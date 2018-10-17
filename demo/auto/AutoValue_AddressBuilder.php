<?php
namespace AutoValue\Demo;

/**
 * @internal
 */
final class AutoValue_AddressBuilder extends AddressBuilder
{   
    private $propertyValues = [];
    
    public function setLines(string ...$lines): \AutoValue\Demo\AddressBuilder
    {
        $this->propertyValues['lines'] = $lines;
        return $this;
    }

    public function setCity(string $city): \AutoValue\Demo\AddressBuilder
    {
        $this->propertyValues['city'] = $city;
        return $this;
    }

    public function setCountry(string $country): \AutoValue\Demo\AddressBuilder
    {
        $this->propertyValues['country'] = $country;
        return $this;
    }

    public function setPostCode(\AutoValue\Demo\PostCode $postCode): \AutoValue\Demo\AddressBuilder
    {
        $this->propertyValues['postCode'] = $postCode;
        return $this;
    }

    public function setMetadata($metadata): \AutoValue\Demo\AddressBuilder
    {
        $this->propertyValues['metadata'] = $metadata;
        return $this;
    }

    public function build(): \AutoValue\Demo\Address
    {
        foreach (['lines', 'country', 'postCode'] as $property) {
            if (!isset($this->propertyValues[$property])) {
                throw new \Exception("Required property $property not initialized.");
            }
        }
        return AutoValue_Address::___withTrustedValues($this->propertyValues);
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
