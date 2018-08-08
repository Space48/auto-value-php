<?php
namespace AutoValue;

/**
 * @author Josh Di Fabio <joshdifabio@gmail.com>
 */
class MethodDefinitionCollection
{
    public static function create(): self
    {
        return new self;
    }

    public function methodNames(): array
    {
        return \array_keys($this->items);
    }

    public function withAdditionalMethodDefinition(MethodDefinition $methodDefinition): self
    {
        if (isset($this->items[$methodDefinition->name()])) {
            throw new \Exception('Multiple definitions provided for method ' . $methodDefinition->name() . '.');
        }

        $result = clone $this;
        $result->items[$methodDefinition->name()] = $methodDefinition;
        return $result;
    }

    public function plus(self $methodDefinitions): self
    {
        $result = $this;

        foreach ($methodDefinitions->items as $item) {
            $result = $result->withAdditionalMethodDefinition($item);
        }

        return $result;
    }

    public function map(callable $fn): array
    {
        return \array_map($fn, $this->items);
    }

    /** @var MethodDefinition[] */
    private $items = [];

    private function __construct()
    {
    }
}