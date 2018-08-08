<?php
namespace AutoValue;

use Roave\BetterReflection\Reflector\ClassReflector;

/**
 * @author Josh Di Fabio <joshdifabio@gmail.com>
 */
interface PropertyDeducer
{
    public function deduceProperties(ClassReflector $reflector, string $templateValueClassName): PropertyCollection;
}