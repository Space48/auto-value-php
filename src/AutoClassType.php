<?php
namespace AutoValue;

use Roave\BetterReflection\Reflector\ClassReflector;

/**
 * @author Josh Di Fabio <joshdifabio@gmail.com>
 */
interface AutoClassType
{
    public function annotation(): string;

    public function generateAutoClass(ClassReflector $reflector, string $templateClassName): string;
}
