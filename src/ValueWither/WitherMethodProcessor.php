<?php
namespace AutoValue\ValueWither;

use function AutoValue\getClass;
use function AutoValue\getPropertyName;
use function AutoValue\isClass;
use AutoValue\MethodDefinition;
use AutoValue\MethodDefinitionCollection;
use AutoValue\PropertyCollection;
use AutoValue\ReflectionMethodCollection;
use AutoValue\ValueClass\MethodProcessor;
use Roave\BetterReflection\Reflection\ReflectionMethod;

/**
 * @author Josh Di Fabio <joshdifabio@gmail.com>
 */
class WitherMethodProcessor extends MethodProcessor
{
    public function matchMethods(ReflectionMethodCollection $methods): array
    {
        return $methods
            ->filterAbstract()
            ->filter(function (ReflectionMethod $reflectionMethod) {
                return getPropertyName($reflectionMethod->getShortName(), 'with') !== null
                    && $reflectionMethod->getNumberOfParameters() === 1
                    && ($returnType = $reflectionMethod->getReturnType())
                    && isClass($returnType)
                    && getClass($reflectionMethod->getDeclaringClass(), $returnType)->getName() === $reflectionMethod->getDeclaringClass()->getName();
            })
            ->methodNames();
    }

    public function generateMethods(ReflectionMethodCollection $matchedMethods, PropertyCollection $properties): MethodDefinitionCollection
    {
        return $matchedMethods->reduce(MethodDefinitionCollection::create(), function (MethodDefinitionCollection $methodDefinitions, ReflectionMethod $method) {
            $propertyName = getPropertyName($method->getShortName(), 'with');
            $parameterName = $method->getParameters()[0]->getName();
            $methodBody = <<<THEPHP
        \$result = clone \$this;
        \$result->$propertyName = \${$parameterName};
        return \$result;
THEPHP;
            return $methodDefinitions->withAdditionalMethodDefinition(MethodDefinition::of($method, $methodBody));
        });
    }
}