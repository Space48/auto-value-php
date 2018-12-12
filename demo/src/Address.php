<?php
namespace AutoValue\Demo;

/**
 * @AutoValue
 */
abstract class Address implements MyValueType
{
    public static function builder(): AddressBuilder
    {
        return new AutoValue_AddressBuilder();
    }

    public abstract function toBuilder(): AddressBuilder;

    /**
     * @return string[]
     */
    public abstract function lines(): array;

    public abstract function withLines(string ...$lines): self;

    public abstract function city(): ?string;

    public abstract function country(): string;

    public abstract function withCountry(string $country): self;

    public abstract function postCode(): PostCode;

    public abstract function metadata();

    public abstract function foo();

    /**
     * @Memoize
     */
    public function linesString(): string
    {
        return implode(', ', $this->lines());
    }
}