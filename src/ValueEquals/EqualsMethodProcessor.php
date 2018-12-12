<?php
namespace AutoValue\ValueEquals;

use function AutoValue\getClass;
use function AutoValue\isClass;
use AutoValue\MethodDefinition;
use AutoValue\MethodDefinitionCollection;
use AutoValue\Property;
use AutoValue\PropertyCollection;
use AutoValue\ReflectionMethodCollection;
use AutoValue\ValueClass\MethodProcessor;
use Roave\BetterReflection\Reflection\ReflectionClass;
use Roave\BetterReflection\Reflection\ReflectionMethod;

/**
 * @author Josh Di Fabio <joshdifabio@gmail.com>
 */
class EqualsMethodProcessor extends MethodProcessor
{
    public function matchMethods(ReflectionMethodCollection $methods): array
    {
        return $methods
            ->filterAbstract()
            ->filter(\Closure::fromCallable([$this, 'matchMethod']))
            ->methodNames();
    }

    public function generateMethods(ReflectionMethodCollection $matchedMethods, PropertyCollection $properties): MethodDefinitionCollection
    {
        return $matchedMethods->reduce(MethodDefinitionCollection::create(), function (MethodDefinitionCollection $methodDefinitions, ReflectionMethod $method) use ($properties) {
            $valueParam = $method->getParameters()[0]->getName();
            $methodBody = $this->generateMethodBody($method->getDeclaringClass(), $valueParam, $properties);
            $methodDefinition = MethodDefinition::of($method, $methodBody);
            return $methodDefinitions->withAdditionalMethodDefinition($methodDefinition);
        });
    }

    private function matchMethod(ReflectionMethod $reflectionMethod): bool
    {
        return $reflectionMethod->getNumberOfParameters() === 1
            && $reflectionMethod->getNumberOfRequiredParameters() === 1
            && !$reflectionMethod->getParameters()[0]->hasType()
            && $reflectionMethod->hasReturnType()
            && ($returnType = $reflectionMethod->getReturnType())
            && (string)$returnType === 'bool';
    }

    private function generateMethodBody(
        ReflectionClass $templateClass,
        string $valueParam,
        PropertyCollection $properties
    ): string {
        $typedProperties = $properties->filter(function (Property $property) { return $property->phpType() !== null; });
        $arrayProperties = $typedProperties->filter(function (Property $property) { return (string)$property->phpType() === 'array'; });
        $classProperties = $typedProperties->filter(function (Property $property) { return isClass($property->phpType()); });
        $valueObjectProperties = $classProperties->filter(function (Property $property) use ($templateClass) {
            return $this->isValueObject($templateClass, $property);
        });
        $mixedProperties = $properties->filter(function (Property $property) {
            return $property->phpType() === null
                || (string)$property->phpType() === 'object'
                || (string)$property->phpType() === 'iterable'
                || (string)$property->phpType() === 'callable';
        });

        $testsForTypedProperties = \array_merge(
            ["\${$valueParam} instanceof self"],

            $typedProperties
                ->filter(function (Property $property) { return !isClass($property->phpType()); })
                ->filter(function (Property $property) { return (string)$property->phpType() !== 'array'; })
                ->mapPropertyNames(function (string $propertyName) use ($valueParam) {
                    return "\$this->$propertyName === \${$valueParam}->$propertyName";
                }),

            $valueObjectProperties
                ->filter(function (Property $property) { return $property->isRequired(); })
                ->mapPropertyNames(function (string $propertyName) use ($valueParam) {
                    return "\$this->{$propertyName}->equals(\${$valueParam}->$propertyName)";
                }),

            $valueObjectProperties
                ->filter(function (Property $property) { return !$property->isRequired(); })
                ->mapPropertyNames(function (string $propertyName) use ($valueParam) {
                    return "(\$this->{$propertyName} === null ? \${$valueParam}->$propertyName === null : \$this->{$propertyName}->equals(\${$valueParam}->$propertyName))";
                }),

            $classProperties
                ->filter(function (Property $property) use ($templateClass) { return !$this->isValueObject($templateClass, $property); })
                ->mapPropertyNames(function (string $propertyName) use ($valueParam) {
                    return "\$this->$propertyName == \${$valueParam}->$propertyName";
                })
        );

        $testsForMixedProperties = \array_merge(
            $mixedProperties
                ->mapPropertyNames(function (string $propertyName) use ($valueParam) {
                    return "\$compareValues(\$this->{$propertyName}, \${$valueParam}->$propertyName) === 0";
                }),

            $arrayProperties
                ->filter(function (Property $property) { return $property->isRequired(); })
                ->mapPropertyNames(function (string $propertyName) use ($valueParam) {
                    return "!\array_udiff_assoc(\$this->{$propertyName}, \${$valueParam}->$propertyName, \$compareValues)";
                }),

            $arrayProperties
                ->filter(function (Property $property) { return !$property->isRequired(); })
                ->mapPropertyNames(function (string $propertyName) use ($valueParam) {
                    return "\$this->{$propertyName} === null ? \${$valueParam}->$propertyName === null : !\array_udiff_assoc(\$this->{$propertyName}, \${$valueParam}->$propertyName, \$compareValues)";
                })
        );

        if ($testsForMixedProperties) {
            return <<<THEPHP
        \$typedPropertiesAreEqual = {$this->getTestsCode($testsForTypedProperties)};
        if (!\$typedPropertiesAreEqual) {
            return false;
        }
        \$compareValues = static function (\$value1, \$value2) use (&\$compareValues) {
            if (\is_array(\$value1)) {
                \$equal = \is_array(\$value2) && !\array_udiff_assoc(\$value1, \$value2, \$compareValues);
            } else {
                \$equal = \$value1 === \$value2
                    || (\method_exists(\$value1, 'equals') ? \$value1->equals(\$value2) : \is_object(\$value1) && \$value1 == \$value2);
            }
            return \$equal ? 0 : 1;
        };
        return {$this->getTestsCode($testsForMixedProperties)};
THEPHP;
        } else {
            return <<<THEPHP
        return {$this->getTestsCode($testsForTypedProperties)};
THEPHP;
        }
    }

    private function isValueObject(ReflectionClass $templateClass, Property $property): bool
    {
        $reflectionClass = getClass($templateClass, $property->phpType());
        return $reflectionClass->hasMethod('equals')
            && $this->matchMethod($reflectionClass->getMethod('equals'));
    }

    private function getTestsCode(array $tests): string
    {
        return \implode("\n            && ", $tests);
    }
}