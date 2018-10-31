<?php
namespace AutoValue\Memoize;

use AutoValue\MethodDefinition;
use AutoValue\MethodDefinitionCollection;
use AutoValue\PropertyCollection;
use AutoValue\ReflectionMethodCollection;
use AutoValue\ValueClass\MethodProcessor;
use Roave\BetterReflection\Reflection\ReflectionMethod;

/**
 * @author Josh Di Fabio <joshdifabio@gmail.com>
 */
class MemoizeMethodProcessor extends MethodProcessor
{
    public function matchMethods(ReflectionMethodCollection $methods): array
    {
        return $methods
            ->filterConcrete()
            ->filter(function (ReflectionMethod $reflectionMethod) {
                return preg_match('{\*\s*\@Memoize\s*$}m', $reflectionMethod->getDocComment()) > 0;
            })
            ->filter(function (ReflectionMethod $reflectionMethod) {
                return $reflectionMethod->getNumberOfParameters() === 0;
            })
            ->methodNames();
    }

    public function generateMethods(ReflectionMethodCollection $matchedMethods, PropertyCollection $properties): MethodDefinitionCollection
    {
        return $matchedMethods->reduce(
            MethodDefinitionCollection::create(),
            function (MethodDefinitionCollection $methodDefinitions, ReflectionMethod $method) {
                $allowsNull = !$method->hasReturnType() || $method->getReturnType()->allowsNull();
                $methodName = $method->getShortName();
                if ($allowsNull) {
                    $methodBody = <<<THEPHP
        if (!isset(\$this->__memoized) || !array_key_exists('$methodName', \$this->__memoized)) {
            \$this->__memoized['$methodName'] = parent::$methodName();
        }
        return \$this->__memoized['$methodName'];
THEPHP;
                } else {
                    $methodBody = <<<THEPHP
        if (!isset(\$this->__memoized['$methodName'])) {
            \$this->__memoized['$methodName'] = parent::$methodName();
        }
        return \$this->__memoized['$methodName'];
THEPHP;
                }
                return $methodDefinitions->withAdditionalMethodDefinition(MethodDefinition::of($method, $methodBody));
            }
        );
    }
}