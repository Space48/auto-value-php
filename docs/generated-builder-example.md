# Generated builder example


For the code shown in the [builder documentation](builders.md), the following is
typical code AutoValue might generate:

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

/**
 * @internal
 */
final class AutoValue_AnimalBuilder extends AnimalBuilder
{   
    private $propertyValues = [];
    
    public function name(string $value): \AutoValue\Demo\AnimalBuilder
    {
        $this->propertyValues['name'] = $value;
        return $this;
    }

    public function numberOfLegs(int $value): \AutoValue\Demo\AnimalBuilder
    {
        $this->propertyValues['numberOfLegs'] = $value;
        return $this;
    }

    public function build(): \AutoValue\Demo\Animal
    {
        foreach (['name', 'numberOfLegs'] as $property) {
            if (!isset($this->propertyValues[$property])) {
                throw new \Exception("Required property $property not initialized.");
            }
        }
        return AutoValue_Animal::___withTrustedValues($this->propertyValues);
    }

    /**
     * @internal
     */
    public static function ___withTrustedValues(array $propertyValues): self
    {
        $builder = new self;
        $builder->propertyValues = $propertyValues;
        return $builder;
    }
}
```