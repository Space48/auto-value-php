<?php
namespace AutoValue\ValueToArray;

use AutoValue\MethodDefinition;
use AutoValue\MethodDefinitionCollection;
use AutoValue\PropertyCollection;
use AutoValue\ReflectionMethodCollection;
use AutoValue\ValueClass\MethodProcessor;
use Roave\BetterReflection\Reflection\ReflectionMethod;

/**
 * @author Josh Di Fabio <joshdifabio@gmail.com>
 */
class ToArrayMethodProcessor extends MethodProcessor
{
    public function matchMethods(ReflectionMethodCollection $reflectionMethods): array
    {
        return $reflectionMethods
            ->filter(function (ReflectionMethod $reflectionMethod) {
                return $reflectionMethod->getName() === 'toArray'
                    && $reflectionMethod->getNumberOfParameters() === 0
                    && ($returnType = $reflectionMethod->getReturnType())
                    && (string)$returnType === 'array';
            })
            ->methodNames();
    }

    public function generateMethods(ReflectionMethodCollection $matchedMethods, PropertyCollection $properties): MethodDefinitionCollection
    {
        return $matchedMethods->reduce(MethodDefinitionCollection::create(), function (MethodDefinitionCollection $methodDefinitions, ReflectionMethod $method) use ($properties) {
            $propertyReads = \implode($properties->mapPropertyNames(function (string $propertyName) {
                return "\n            '$propertyName' => \$this->$propertyName,";
            }));
            $methodBody = <<<THEPHP
        return \array_filter([$propertyReads
        ], function (\$value) { return \$value !== null; });
THEPHP;
            return $methodDefinitions->withAdditionalMethodDefinition(MethodDefinition::of($method, $methodBody));
        });
    }
}