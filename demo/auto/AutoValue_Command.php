<?php
namespace AutoValue\Demo;

/**
 * @internal
 */
final class AutoValue_Command extends Command
{
    /** @var string */
    private $name;
    /** @var mixed */
    private $payload;
    /** @var \DateTimeImmutable */
    private $timestamp;
    
    protected function __construct(array $propertyValues = [])
    {
        foreach ($propertyValues as $property => $value) {
            $this->$property = $value;
        }
    }
    
    public function equals($subject): bool
    {
        $typedPropertiesAreEqual = $subject instanceof self
            && $this->name === $subject->name
            && $this->timestamp == $subject->timestamp;
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
        return $compareValues($this->payload, $subject->payload) === 0;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function payload()
    {
        return $this->payload;
    }

    public function timestamp(): \DateTimeImmutable
    {
        return $this->timestamp;
    }

    /**
     * @internal
     */
    public static function ___withTrustedValues(array $propertyValues): self
    {
        return new self($propertyValues);
    }
}
