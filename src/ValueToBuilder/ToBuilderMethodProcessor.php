<?php
namespace AutoValue\ValueToBuilder;

use AutoValue\BuilderClass\BuilderClassType;
use AutoValue\MethodDefinition;
use AutoValue\MethodDefinitionCollection;
use AutoValue\PropertyCollection;
use AutoValue\ReflectionMethodCollection;
use AutoValue\ValueClass\MethodProcessor;
use Roave\BetterReflection\Reflection\ReflectionMethod;

/**
 * @author Josh Di Fabio <joshdifabio@gmail.com>
 */
class ToBuilderMethodProcessor extends MethodProcessor
{
    public function matchMethods(ReflectionMethodCollection $methods): array
    {
        return $methods
            ->filterAbstract()
            ->filter(function (ReflectionMethod $reflectionMethod) {
                if (!(
                    $reflectionMethod->getName() === 'toBuilder'
                    && $reflectionMethod->getNumberOfParameters() === 0
                    && $reflectionMethod->hasReturnType()
                )) {
                    return false;
                }
                $returnType = $reflectionMethod->getReturnType();
                if ($returnType->isBuiltin()) {
                    return false;
                }
                $valueClassName = $reflectionMethod->getDeclaringClass()->getName();
                $builderClassName = BuilderClassType::getBuilderClass($valueClassName);
                return $returnType->targetReflectionClass()->getName() === $builderClassName;
            })
            ->methodNames();
    }

    public function generateMethods(ReflectionMethodCollection $matchedMethods, PropertyCollection $properties): MethodDefinitionCollection
    {
        return $matchedMethods->reduce(MethodDefinitionCollection::create(), function (MethodDefinitionCollection $methodDefinitions, ReflectionMethod $method) use ($properties) {
            $builderTemplateClass = $method->getReturnType()->targetReflectionClass()->getShortName();
            $builderAutoClass = "AutoValue_$builderTemplateClass";
            $propertyReads = \implode($properties->mapPropertyNames(function (string $propertyName) {
                return "\n            '$propertyName' => \$this->$propertyName,";
            }));
            $methodBody = "        return $builderAutoClass::___withTrustedValues([$propertyReads
        ]);";
            return $methodDefinitions->withAdditionalMethodDefinition(MethodDefinition::of($method, $methodBody));
        });
    }
}