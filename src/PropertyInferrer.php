<?php
namespace AutoValue;

use Roave\BetterReflection\Reflector\ClassReflector;

/**
 * @author Josh Di Fabio <joshdifabio@gmail.com>
 */
interface PropertyInferrer
{
    public function inferProperties(ClassReflector $reflector, string $templateValueClassName): PropertyCollection;
}