<?php
namespace AutoValue;

use Roave\BetterReflection\Reflection\ReflectionMethod;

/**
 * @author Josh Di Fabio <joshdifabio@gmail.com>
 */
class MethodDefinition
{
    public static function of(ReflectionMethod $reflection, string $body): self
    {
        $methodDefinition = new self;
        $methodDefinition->reflection = $reflection;
        $methodDefinition->body = $body;
        return $methodDefinition;
    }


    public function reflection(): ReflectionMethod
    {
        return $this->reflection;
    }

    public function name(): string
    {
        return $this->reflection->getShortName();
    }

    public function body(): string
    {
        return $this->body;
    }

    /** @var ReflectionMethod */
    private $reflection;
    private $body;

    private function __construct()
    {
    }
}