<?php
namespace AutoValue\ValueClass;

use AutoValue\AutoClassType;
use AutoValue\Memoize\MemoizeMethodProcessor;
use AutoValue\PropertyCollection;
use AutoValue\PropertyInferrer;
use AutoValue\ReflectionMethodCollection;
use AutoValue\ValueEquals\EqualsMethodProcessor;
use AutoValue\ValueToArray\ToArrayMethodProcessor;
use AutoValue\ValueToBuilder\ToBuilderMethodProcessor;
use AutoValue\ValueWither\WitherMethodProcessor;
use Roave\BetterReflection\Reflector\ClassReflector;

/**
 * @author Josh Di Fabio <joshdifabio@gmail.com>
 */
class ValueClassType implements AutoClassType, PropertyInferrer
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
            new MemoizeMethodProcessor(),
        ]);
        $classGenerator = new ValueClassGenerator();
        return new self($methodProcessors, $classGenerator);
    }

    public function annotation(): string
    {
        return 'AutoValue';
    }

    public function inferProperties(ClassReflector $reflector, string $templateValueClasName): PropertyCollection
    {
        $templateValueClass = $reflector->reflect($templateValueClasName);
        $methods = ReflectionMethodCollection::of($templateValueClass->getMethods());
        [$properties] = $this->methodProcessors->processMethods($methods);
        return $properties;
    }

    public function generateAutoClass(ClassReflector $reflector, string $templateValueClasName): string
    {
        $templateValueClass = $reflector->reflect($templateValueClasName);
        $methods = ReflectionMethodCollection::of($templateValueClass->getMethods());
        [$properties, $methodDefinitions] = $this->methodProcessors->processMethods($methods);
        return $this->classGenerator->generateClass($templateValueClass, $properties, $methodDefinitions);
    }
}