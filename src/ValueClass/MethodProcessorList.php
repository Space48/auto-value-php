<?php
namespace AutoValue\ValueClass;

use AutoValue\MethodDefinitionCollection;
use AutoValue\PropertyCollection;
use AutoValue\ReflectionMethodCollection;
use Roave\BetterReflection\Reflection\ReflectionMethod;

/**
 * @author Josh Di Fabio <joshdifabio@gmail.com>
 */
class MethodProcessorList
{
    private $methodProcessors;

    /**
     * @param MethodProcessor $methodProcessors
     */
    public function __construct(array $methodProcessors)
    {
        $this->methodProcessors = $methodProcessors;
    }

    public function processMethods(ReflectionMethodCollection $abstractMethods): array
    {
        $remainingAbstractMethods = $abstractMethods;
        $matchedMethodsByProcessor = [];
        /** @var MethodProcessor $methodProcessor */
        foreach ($this->methodProcessors as $methodProcessor) {
            $matchedMethodNames = $methodProcessor->matchMethods($remainingAbstractMethods);
            $matchedMethods = $remainingAbstractMethods->filter(function (ReflectionMethod $reflectionMethod) use ($matchedMethodNames) {
                return \in_array($reflectionMethod->getShortName(), $matchedMethodNames, true);
            });
            $remainingAbstractMethods = $remainingAbstractMethods->withoutMethods($matchedMethodNames);
            $matchedMethodsByProcessor[] = [$methodProcessor, $matchedMethods];
        }
        if (!$remainingAbstractMethods->isEmpty()) {
            throw new \Exception('Some abstract methods could not be processed.');
        }
        $properties = $this->inferProperties($matchedMethodsByProcessor);
        $methodDefinitions = $this->generateMethods($matchedMethodsByProcessor, $properties);
        return [$properties, $methodDefinitions];
    }

    private function inferProperties(array $matchedMethodsByProcessor): PropertyCollection
    {
        $properties = PropertyCollection::create();
        /**
         * @var MethodProcessor $methodProcessor
         * @var ReflectionMethodCollection $matchedMethods
         */
        foreach ($matchedMethodsByProcessor as [$methodProcessor, $matchedMethods]) {
            $properties = $properties->plus($methodProcessor->inferProperties($matchedMethods));
        }
        return $properties;
    }

    private function generateMethods(array $matchedMethodsByProcessor, PropertyCollection $properties): MethodDefinitionCollection
    {
        $methodDefinitions = MethodDefinitionCollection::create();
        /**
         * @var MethodProcessor $methodProcessor
         * @var ReflectionMethodCollection $matchedMethods
         */
        foreach ($matchedMethodsByProcessor as [$methodProcessor, $matchedMethods]) {
            $methodDefinitions = $methodDefinitions->plus($methodProcessor->generateMethods($matchedMethods, $properties));
        }
        return $methodDefinitions;
    }
}