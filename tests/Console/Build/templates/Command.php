<?php
namespace MyTemplates;

/**
 * @AutoValue
 */
abstract class Command
{
    public static function of(string $name, $payload): self
    {
        return new AutoValue_Command([
            'name' => $name,
            'payload' => $payload,
            'timestamp' => new \DateTimeImmutable(),
        ]);
    }

    public abstract function name(): string;

    public abstract function payload();

    public abstract function withPayload($payload): self;

    public abstract function timestamp(): \DateTimeImmutable;

    public abstract function equals($subject): bool;
}