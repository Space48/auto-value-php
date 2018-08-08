<?php
namespace AutoValue;

use Roave\BetterReflection\Reflection\ReflectionType;

/**
 * @author Josh Di Fabio <joshdifabio@gmail.com>
 */
class Property
{
    public static function named(string $name): self
    {
        $property = new self;
        $property->name = $name;
        return $property;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function type(): ?ReflectionType
    {
        return $this->type;
    }

    public function withType(?ReflectionType $type): self
    {
        $result = clone $this;
        $result->type = $type;
        return $result;
    }

    public function isRequired(): bool
    {
        return $this->type && !$this->type->allowsNull();
    }

    private $name;
    /** @var ReflectionType|null */
    private $type;

    private function __construct()
    {
    }
}