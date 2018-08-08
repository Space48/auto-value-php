<?php
namespace AutoValue;

/**
 * @author Josh Di Fabio <joshdifabio@gmail.com>
 */
class MethodGeneratorList
{
    private $methodGenerators = [];

    /**
     * @param MethodGenerator[] $methodGenerators
     */
    public function __construct(array $methodGenerators = [])
    {
        $this->methodGenerators = $methodGenerators;
    }

    public function generateMethods(ReflectionMethodCollection $abstractMethods, PropertyCollection $properties): MethodDefinitionCollection
    {
        $methodDefinitions = MethodDefinitionCollection::create();
        foreach ($this->methodGenerators as $methodGenerator) {
            $_methodDefinitions = $methodGenerator->generateMethods($abstractMethods, $properties);
            $methodDefinitions = $methodDefinitions->plus($_methodDefinitions);
            $abstractMethods = $abstractMethods->withoutMethods($_methodDefinitions->methodNames());
        }
        if (!$abstractMethods->isEmpty()) {
            throw new \Exception('Some abstract methods could not be processed.');
        }
        return $methodDefinitions;
    }
}