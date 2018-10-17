<?php
namespace AutoValue\Demo;

/**
 * @AutoValue\Builder
 */
abstract class AddressBuilder
{
    public abstract function setLines(string ...$lines): self;

    public abstract function setCity(string $city): self;

    public abstract function setCountry(string $country): self;

    public abstract function setPostCode(PostCode $postCode): self;

    public abstract function setMetadata($metadata): self;

    public abstract function build(): Address;
}