<?php
namespace AutoValue\Demo;

/**
 * @internal
 */
final class AutoValue_Address extends Address
{
    /** @var array */
    private $lines;
    /** @var ?string */
    private $city;
    /** @var string */
    private $country;
    /** @var \AutoValue\Demo\PostCode */
    private $postCode;
    /** @var mixed */
    private $metadata;
    /** @var mixed */
    private $foo;
    
    protected function __construct(array $propertyValues = [])
    {
        foreach ($propertyValues as $property => $value) {
            $this->$property = $value;
        }
    }
    
    public function equals($subject): bool
    {
        $typedPropertiesAreEqual = $subject instanceof self
            && $this->city === $subject->city
            && $this->country === $subject->country
            && $this->postCode->equals($subject->postCode);
        if (!$typedPropertiesAreEqual) {
            return false;
        }
        $compareValues = static function ($value1, $value2) use (&$compareValues) {
            if (\is_array($value1)) {
                $equal = \is_array($value2) && !\array_udiff_assoc($value1, $value2, $compareValues);
            } else {
                $equal = $value1 === $value2
                    || (\method_exists($value1, 'equals') ? $value1->equals($value2) : \is_object($value1) && $value1 == $value2);
            }
            return $equal ? 0 : 1;
        };
        return $compareValues($this->metadata, $subject->metadata) === 0
            && $compareValues($this->foo, $subject->foo) === 0
            && !\array_udiff_assoc($this->lines, $subject->lines, $compareValues);
    }

    public function toBuilder(): \AutoValue\Demo\AddressBuilder
    {
        return AutoValue_AddressBuilder::___withTrustedValues([
            'lines' => $this->lines,
            'city' => $this->city,
            'country' => $this->country,
            'postCode' => $this->postCode,
            'metadata' => $this->metadata,
            'foo' => $this->foo,
        ]);
    }

    public function withCountry(string $country): \AutoValue\Demo\Address
    {
        $result = clone $this;
        $result->country = $country;
        return $result;
    }

    public function lines(): array
    {
        return $this->lines;
    }

    public function city(): ?string
    {
        return $this->city;
    }

    public function country(): string
    {
        return $this->country;
    }

    public function postCode(): \AutoValue\Demo\PostCode
    {
        return $this->postCode;
    }

    public function metadata()
    {
        return $this->metadata;
    }

    public function foo()
    {
        return $this->foo;
    }

    /**
     * @internal
     */
    public static function ___withTrustedValues(array $propertyValues): self
    {
        return new self($propertyValues);
    }
}