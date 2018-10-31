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

    public function generateMethods(ReflectionMethodCollection $methods, PropertyCollection $properties): MethodDefinitionCollection
    {
        $unprocessedMethods = $methods;
        $methodDefinitions = MethodDefinitionCollection::create();
        foreach ($this->methodGenerators as $methodGenerator) {
            $_methodDefinitions = $methodGenerator->generateMethods($unprocessedMethods, $properties);
            $methodDefinitions = $methodDefinitions->plus($_methodDefinitions);
            $unprocessedMethods = $unprocessedMethods->withoutMethods($_methodDefinitions->methodNames());
        }
        if (!$unprocessedMethods->filterAbstract()->isEmpty()) {
            throw new \Exception('Some abstract methods could not be processed.');
        }
        return $methodDefinitions;
    }
}