<?php
namespace AutoValue;

use Roave\BetterReflection\Reflection\ReflectionMethod;

/**
 * @author Josh Di Fabio <joshdifabio@gmail.com>
 */
class ReflectionMethodCollection implements \IteratorAggregate
{
    /**
     * @param ReflectionMethod[] $methods
     */
    public static function of(array $methods): self
    {
        $collection = new self;
        foreach ($methods as $method) {
            $methodName = $method->getShortName();
            if (isset($collection->items[$methodName])) {
                throw new \Exception();
            }
            $collection->items[$methodName] = $method;
        }
        return $collection;
    }

    public function methodNames(): array
    {
        return \array_keys($this->items);
    }

    public function getMethod(string $methodName): ?ReflectionMethod
    {
        return $this->items[$methodName] ?? null;
    }

    public function withoutMethods(array $methodNames): self
    {
        return $this->filter(function (ReflectionMethod $method) use ($methodNames) {
            return !\in_array($method->getShortName(), $methodNames, true);
        });
    }

    public function filter(callable $predicate): self
    {
        $result = new self;
        $result->items = \array_filter($this->items, $predicate);
        return $result;
    }

    public function filterAbstract(): self
    {
        return $this->filter(function (ReflectionMethod $method): bool {
            return \ReflectionMethod::IS_ABSTRACT & $method->getModifiers()
                || $method->getDeclaringClass()->isInterface();
        });
    }

    public function filterConcrete(): self
    {
        return $this->filter(function (ReflectionMethod $method): bool {
            return !(\ReflectionMethod::IS_ABSTRACT & $method->getModifiers())
                && !$method->getDeclaringClass()->isInterface();
        });
    }

    public function reduce($value, callable $fn)
    {
        return \array_reduce($this->items, $fn, $value);
    }

    public function isEmpty(): bool
    {
        return empty($this->items);
    }

    public function getIterator(): \Iterator
    {
        return new \ArrayIterator($this->items);
    }

    private $items = [];

    private function __construct()
    {
    }
}