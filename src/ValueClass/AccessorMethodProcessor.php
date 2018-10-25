<?php
namespace AutoValue\ValueClass;

use function AutoValue\getPropertyName;
use AutoValue\MethodDefinition;
use AutoValue\MethodDefinitionCollection;
use AutoValue\Property;
use AutoValue\PropertyCollection;
use AutoValue\ReflectionMethodCollection;
use Roave\BetterReflection\Reflection\ReflectionMethod;

/**
 * @author Josh Di Fabio <joshdifabio@gmail.com>
 */
class AccessorMethodProcessor extends MethodProcessor
{
    public function matchMethods(ReflectionMethodCollection $reflectionMethods): array
    {
        return $reflectionMethods
            ->filter(function (ReflectionMethod $reflectionMethod) {
                return $reflectionMethod->getNumberOfParameters() === 0;
            })
            ->methodNames();
    }

    public function inferProperties(ReflectionMethodCollection $matchedMethods): PropertyCollection
    {
        $templateUsesPrefixes = self::templateUsesPrefixes($matchedMethods);
        return $matchedMethods->reduce(PropertyCollection::create(), function (PropertyCollection $properties, ReflectionMethod $method) use ($templateUsesPrefixes) {
            $propertyName = self::getPropertyName($templateUsesPrefixes, $method);
            $property = Property::named($propertyName)->withType($method->getReturnType());
            return $properties->withProperty($property);
        });
    }

    public function generateMethods(ReflectionMethodCollection $matchedMethods, PropertyCollection $properties): MethodDefinitionCollection
    {
        $templateUsesPrefixes = self::templateUsesPrefixes($matchedMethods);
        return $matchedMethods->reduce(MethodDefinitionCollection::create(), function (MethodDefinitionCollection $methodDefinitions, ReflectionMethod $method) use ($templateUsesPrefixes, $properties) {
            $propertyName = self::getPropertyName($templateUsesPrefixes, $method);
            $methodBody = "        return \$this->$propertyName;";
            return $methodDefinitions->withAdditionalMethodDefinition(MethodDefinition::of($method, $methodBody));
        });
    }

    private static function getPropertyName(bool $templateUsesPrefixes, ReflectionMethod $matchedMethod): ?string
    {
        $methodName = $matchedMethod->getShortName();
        if (!$templateUsesPrefixes) {
            return $methodName;
        }
        if (($type = $matchedMethod->getReturnType()) && (string)$type === 'bool') {
            return (getPropertyName($methodName, 'get') ?? getPropertyName($methodName, 'is'));
        }
        return getPropertyName($methodName, 'get');
    }

    private static function templateUsesPrefixes(ReflectionMethodCollection $matchedMethods): bool
    {
        /** @var ReflectionMethod $matchedMethod */
        foreach ($matchedMethods as $matchedMethod) {
            $propertyName = self::getPropertyName(true, $matchedMethod);
            if ($propertyName === null) {
                return false;
            }
        }
        return true;
    }
}