<?php
namespace AutoValue\ValueClass;

use AutoValue\MethodGenerator;
use AutoValue\PropertyCollection;
use AutoValue\ReflectionMethodCollection;

/**
 * @author Josh Di Fabio <joshdifabio@gmail.com>
 */
abstract class MethodProcessor implements MethodGenerator
{
    /**
     * @return string[] Names of matched methods
     */
    public abstract function matchMethods(ReflectionMethodCollection $reflectionMethods): array;

    public function inferProperties(ReflectionMethodCollection $matchedMethods): PropertyCollection
    {
        return PropertyCollection::create();
    }
}