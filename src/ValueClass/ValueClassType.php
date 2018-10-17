<?php
namespace AutoValue\ValueClass;

use AutoValue\AutoClassType;
use AutoValue\PropertyCollection;
use AutoValue\PropertyDeducer;
use AutoValue\ReflectionMethodCollection;
use AutoValue\ValueEquals\EqualsMethodProcessor;
use AutoValue\ValueToArray\ToArrayMethodProcessor;
use AutoValue\ValueToBuilder\ToBuilderMethodProcessor;
use AutoValue\ValueWither\WitherMethodProcessor;
use Roave\BetterReflection\Reflector\ClassReflector;

/**
 * @author Josh Di Fabio <joshdifabio@gmail.com>
 */
class ValueClassType implements AutoClassType, PropertyDeducer
{
    private $methodProcessors;
    private $classGenerator;

    public function __construct(MethodProcessorList $methodProcessors, ValueClassGenerator $classGenerator)
    {
        $this->methodProcessors = $methodProcessors;
        $this->classGenerator = $classGenerator;
    }

    public static function withDefaultConfiguration(): self
    {
        $methodProcessors = new MethodProcessorList([
            new EqualsMethodProcessor(),
            new ToBuilderMethodProcessor(),
            new ToArrayMethodProcessor(),
            new WitherMethodProcessor(),
            new AccessorMethodProcessor(),
        ]);
        $classGenerator = new ValueClassGenerator();
        return new self($methodProcessors, $classGenerator);
    }

    public function annotation(): string
    {
        return 'AutoValue';
    }

    public function deduceProperties(ClassReflector $reflector, string $templateValueClasName): PropertyCollection
    {
        $templateValueClass = $reflector->reflect($templateValueClasName);
        $abstractMethods = ReflectionMethodCollection::of($templateValueClass->getMethods())->filterAbstract();
        [$properties] = $this->methodProcessors->processMethods($abstractMethods);
        return $properties;
    }

    public function generateAutoClass(ClassReflector $reflector, string $templateValueClasName): string
    {
        $templateValueClass = $reflector->reflect($templateValueClasName);
        $abstractMethods = ReflectionMethodCollection::of($templateValueClass->getMethods())->filterAbstract();
        [$properties, $methodDefinitions] = $this->methodProcessors->processMethods($abstractMethods);
        return $this->classGenerator->generateClass($templateValueClass, $properties, $methodDefinitions);
    }
}