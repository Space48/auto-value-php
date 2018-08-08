<?php
namespace AutoValue\BuilderClass;

use function AutoValue\generateConcreteMethod;
use AutoValue\MethodDefinition;
use AutoValue\MethodDefinitionCollection;
use Roave\BetterReflection\Reflection\ReflectionClass;

/**
 * @author Josh Di Fabio <joshdifabio@gmail.com>
 */
class BuilderClassGenerator
{
    public function generateClass(ReflectionClass $templateClass, MethodDefinitionCollection $methodDefinitions): string
    {
        $methodDeclarations = \implode("\n\n", $methodDefinitions->map(function (MethodDefinition $methodDefinition) {
            return generateConcreteMethod($methodDefinition->reflection(), $methodDefinition->body());
        }));

        return <<<THEPHP
namespace {$templateClass->getNamespaceName()};

/**
 * @internal
 */
final class AutoValue_{$templateClass->getShortName()} extends {$templateClass->getShortName()}
{   
    private \$propertyValues = [];
    
$methodDeclarations

    /**
     * @internal
     */
    public static function ___withTrustedValues(array \$propertyValues): self
    {
        \$builder = new self;
        \$builder->propertyValues = \$propertyValues;
        return \$builder;
    }
}
THEPHP;
    }
}