<?php
namespace AutoValue;

use phpDocumentor\Reflection\Type;
use Roave\BetterReflection\Reflection\ReflectionMethod;
use Roave\BetterReflection\Reflection\ReflectionProperty;
use Roave\BetterReflection\Reflection\ReflectionType;

/**
 * @author Josh Di Fabio <joshdifabio@gmail.com>
 */
class Property
{
    public static function fromAccessorMethod(string $propertyName, ReflectionMethod $accessorMethod): self
    {
        $property = new self;
        $property->name = $propertyName;
        $property->accessorMethod = $accessorMethod;
        return $property;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function phpType(): ?ReflectionType
    {
        if (!$this->phpType) {
            $this->phpType = $this->accessorMethod->getReturnType();
        }
        return $this->phpType;
    }

    public function docBlockType(): string
    {
        if (!isset($this->docBlockType)) {
            $docBlockTypes = $this->accessorMethod->getDocBlockReturnTypes();
            $this->docBlockType = $docBlockTypes
                ? \implode('|', $docBlockTypes)
                : ($this->phpType()
                    ? generateTypeHint($this->phpType(), $this->accessorMethod->getDeclaringClass())
                    : 'mixed'
                );
        }
        return $this->docBlockType;
    }

    public function isRequired(): bool
    {
        return $this->phpType() && !$this->phpType()->allowsNull();
    }

    private $name;
    /** @var ReflectionMethod */
    private $accessorMethod;
    /** @var ReflectionType|null */
    private $phpType;

    private function __construct()
    {
    }
}