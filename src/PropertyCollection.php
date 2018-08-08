<?php
namespace AutoValue;

/**
 * @author Josh Di Fabio <joshdifabio@gmail.com>
 */
class PropertyCollection
{
    public static function create(): self
    {
        return new self;
    }

    public function propertyNames(): array
    {
        return \array_keys($this->properties);
    }

    public function getProperty(string $name): ?Property
    {
        return $this->properties[$name];
    }

    public function withProperty(Property $property): self
    {
        $result = clone $this;
        $result->properties[$property->name()] = $property;
        return $result;
    }

    public function plus(self $collection): self
    {
        $result = clone $this;
        foreach ($collection->properties as $name => $property) {
            $result->properties[$name] = isset($result->properties[$name])
                ? $result->properties[$name]->withAdditionalConstraints($property->constraints())
                : $property;
        }
        return $result;
    }

    public function filter(callable $predicate): self
    {
        $result = new self;
        $result->properties = \array_filter($this->properties, $predicate);
        return $result;
    }

    public function map(callable $fn): array
    {
        return \array_map($fn, \array_values($this->properties));
    }

    public function mapPropertyNames(callable $fn): array
    {
        return \array_map($fn, \array_keys($this->properties));
    }

    private $properties = [];

    private function __construct()
    {
    }
}