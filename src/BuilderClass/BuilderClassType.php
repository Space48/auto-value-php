<?php
namespace AutoValue\BuilderClass;

use AutoValue\AutoClassType;
use AutoValue\MethodGeneratorList;
use AutoValue\PropertyDeducer;
use AutoValue\ReflectionMethodCollection;
use Roave\BetterReflection\Reflector\ClassReflector;

/**
 * @author Josh Di Fabio <joshdifabio@gmail.com>
 */
class BuilderClassType implements AutoClassType
{
    private $propertyDeducer;
    private $methodGenerators;
    private $classGenerator;

    public function __construct(
        PropertyDeducer $propertyDeducer,
        MethodGeneratorList $methodGenerators,
        BuilderClassGenerator $classGenerator
    ) {
        $this->propertyDeducer = $propertyDeducer;
        $this->methodGenerators = $methodGenerators;
        $this->classGenerator = $classGenerator;
    }

    public static function withDefaultConfiguration(PropertyDeducer $propertyDeducer): self
    {
        $methodGenerators = new MethodGeneratorList([
            new SetterMethodGenerator(),
            new BuildMethodGenerator(),
        ]);
        return new self($propertyDeducer, $methodGenerators, new BuilderClassGenerator());
    }

    public function annotation(): string
    {
        return 'AutoValue\\Builder';
    }

    public function generateAutoClass(ClassReflector $reflector, string $templateBuilderClassName): string
    {
        $templateBuilderClass = $reflector->reflect($templateBuilderClassName);
        $abstractMethods = ReflectionMethodCollection::of($templateBuilderClass->getMethods(\ReflectionMethod::IS_ABSTRACT));
        $templateValueClassName = self::getValueClass($templateBuilderClassName);
        $properties = $this->propertyDeducer->deduceProperties($reflector, $templateValueClassName);
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