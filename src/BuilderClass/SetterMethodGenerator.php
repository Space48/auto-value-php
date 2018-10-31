<?php
namespace AutoValue\BuilderClass;

use function AutoValue\getPropertyName;
use AutoValue\MethodDefinition;
use AutoValue\MethodDefinitionCollection;
use AutoValue\PropertyCollection;
use AutoValue\ReflectionMethodCollection;
use AutoValue\MethodGenerator;
use Roave\BetterReflection\Reflection\ReflectionMethod;

/**
 * @author Josh Di Fabio <joshdifabio@gmail.com>
 */
class SetterMethodGenerator implements MethodGenerator
{
    public function generateMethods(ReflectionMethodCollection $matchedMethods, PropertyCollection $properties): MethodDefinitionCollection
    {
        return $matchedMethods
            ->filterAbstract()
            ->filter(self::abstractMethodMatcher())
            ->reduce(MethodDefinitionCollection::create(), function (MethodDefinitionCollection $methodDefinitions, ReflectionMethod $method) {
                $propertyName = self::getPropertyName($method->getShortName());
                $parameterName = $method->getParameters()[0]->getName();
                $methodBody = <<<THEPHP
        \$this->propertyValues['$propertyName'] = \${$parameterName};
        return \$this;
THEPHP;
                return $methodDefinitions->withAdditionalMethodDefinition(MethodDefinition::of($method, $methodBody));
            });
    }

    private static function getPropertyName(string $accessorMethodName): string
    {
        return getPropertyName($accessorMethodName, 'set') ?? $accessorMethodName;
    }

    private static function abstractMethodMatcher(): callable
    {
        return function (ReflectionMethod $reflectionMethod) {
            if ($reflectionMethod->getNumberOfParameters() !== 1) {
                return false;
            }
            $returnType = $reflectionMethod->getReturnType();
            if (!$returnType) {
                return true;
            }
            if ($returnType->allowsNull()) {
                return false;
            }
            if ($returnType->isBuiltin()) {
                return (string)$returnType === 'self';
            }
            return $reflectionMethod->getDeclaringClass()->isSubclassOf((string)$returnType);
        };
    }
}