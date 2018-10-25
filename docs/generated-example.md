# Generated example


For the code shown in the [introduction](index.md), the following is typical
code AutoValue might generate:

```php

/**
 * @internal
 */
final class AutoValue_Animal extends Animal
{
    /** @var string */
    private $name;
    /** @var int */
    private $numberOfLegs;
    
    protected function __construct(array $propertyValues = [])
    {
        foreach ($propertyValues as $property => $value) {
            $this->$property = $value;
        }
    }
    
    public function equals($value): bool
    {
        return $value instanceof self
            && $this->name === $value->name
            && $this->numberOfLegs === $value->numberOfLegs;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function numberOfLegs(): int
    {
        return $this->numberOfLegs;
    }

    /**
     * @internal
     */
    public static function ___withTrustedValues(array $propertyValues): self
    {
        return new self($propertyValues);
    }
}

```