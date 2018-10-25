<?php
namespace AutoValue\BuilderClass;

use AutoValue\AutoClassType;
use AutoValue\MethodGeneratorList;
use AutoValue\PropertyInferrer;
use AutoValue\ReflectionMethodCollection;
use Roave\BetterReflection\Reflector\ClassReflector;

/**
 * @author Josh Di Fabio <joshdifabio@gmail.com>
 */
class BuilderClassType implements AutoClassType
{
    private $propertyInferrer;
    private $methodGenerators;
    private $classGenerator;

    public function __construct(
        PropertyInferrer $propertyInferrer,
        MethodGeneratorList $methodGenerators,
        BuilderClassGenerator $classGenerator
    ) {
        $this->propertyInferrer = $propertyInferrer;
        $this->methodGenerators = $methodGenerators;
        $this->classGenerator = $classGenerator;
    }

    public static function withDefaultConfiguration(PropertyInferrer $propertyInferrer): self
    {
        $methodGenerators = new MethodGeneratorList([
            new SetterMethodGenerator(),
            new BuildMethodGenerator(),
        ]);
        return new self($propertyInferrer, $methodGenerators, new BuilderClassGenerator());
    }

    public function annotation(): string
    {
        return 'AutoValue\\Builder';
    }

    public function generateAutoClass(ClassReflector $reflector, string $templateBuilderClassName): string
    {
        $templateBuilderClass = $reflector->reflect($templateBuilderClassName);
        $abstractMethods = ReflectionMethodCollection::of($templateBuilderClass->getMethods())->filterAbstract();
        $templateValueClassName = self::getValueClass($templateBuilderClassName);
        $properties = $this->propertyInferrer->inferProperties($reflector, $templateValueClassName);
        $methodDefinitions = $this->methodGenerators->generateMethods($abstractMethods, $properties);
        return $this->classGenerator->generateClass($templateBuilderClass, $methodDefinitions);
    }

    public static function getBuilderClass(string $valueClass): string
    {
        return "{$valueClass}Builder";
    }

    public static function getValueClass(string $builderClass): string
    {
        if (\substr($builderClass, -7) !== 'Builder') {
            throw new \Exception("Builder class names must end with the word 'Builder'.");
        }
        return \substr($builderClass, 0, -7);
    }
}