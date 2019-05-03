<?php
namespace AutoValue\BuilderClass;

use AutoValue\MethodGenerator;
use AutoValue\MethodDefinition;
use AutoValue\MethodDefinitionCollection;
use AutoValue\Property;
use AutoValue\PropertyCollection;
use AutoValue\ReflectionMethodCollection;
use Roave\BetterReflection\Reflection\ReflectionMethod;

/**
 * @author Josh Di Fabio <joshdifabio@gmail.com>
 */
class BuildMethodGenerator implements MethodGenerator
{
    public function generateMethods(ReflectionMethodCollection $matchedMethods, PropertyCollection $properties): MethodDefinitionCollection
    {
        return $matchedMethods
            ->filterAbstract()
            ->filter(self::abstractMethodMatcher())
            ->reduce(MethodDefinitionCollection::create(), function (MethodDefinitionCollection $methodDefinitions, ReflectionMethod $method) use ($properties) {
                $valueTemplateClass = $method->getReturnType()->targetReflectionClass()->getShortName();
                $valueAutoClass = "AutoValue_$valueTemplateClass";
                $methodBody = <<<THEPHP
        return $valueAutoClass::___withTrustedValues(\$this->propertyValues);
THEPHP;
                return $methodDefinitions->withAdditionalMethodDefinition(MethodDefinition::of($method, $methodBody));
            });
    }

    private static function abstractMethodMatcher(): callable
    {
        return function (ReflectionMethod $reflectionMethod) {
            if ($reflectionMethod->getNumberOfParameters() !== 0) {
                return false;
            }
            if (!$reflectionMethod->hasReturnType()) {
                return false;
            }
            $returnType = $reflectionMethod->getReturnType();
            if ($returnType->isBuiltin()) {
                return false;
            }
            $valueClassName = BuilderClassType::getValueClass($reflectionMethod->getDeclaringClass()->getName());
            return $returnType->targetReflectionClass()->getName() === $valueClassName;
        };
    }
}