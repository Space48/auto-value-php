<?php
namespace AutoValue\ValueClass;

use function AutoValue\generateConcreteMethod;
use function AutoValue\generateTypeHint;
use AutoValue\MethodDefinition;
use AutoValue\MethodDefinitionCollection;
use AutoValue\Property;
use AutoValue\PropertyCollection;
use Roave\BetterReflection\Reflection\ReflectionClass;

/**
 * @author Josh Di Fabio <joshdifabio@gmail.com>
 */
class ValueClassGenerator
{
    public function generateClass(ReflectionClass $baseClass, PropertyCollection $properties, MethodDefinitionCollection $methodDefinitions): string
    {
        // todo include type in properties constant
        $propertyDeclarations = \implode("\n", $properties->map(function (Property $property) use ($baseClass) {
            return <<<THEPHP
    /** @var {$property->docBlockType()} */
    private \${$property->name()};
THEPHP;
        }));

        $methodDeclarations = \implode("\n\n", $methodDefinitions->map(function (MethodDefinition $methodDefinition) {
            return generateConcreteMethod($methodDefinition->reflection(), $methodDefinition->body());
        }));

        $requiredProperties = $properties
            ->filter(function (Property $property) { return $property->isRequired(); })
            ->propertyNames();

        $requiredPropertiesExported = \implode(', ', \array_map(function ($property) { return "'$property'"; }, $requiredProperties));

        return <<<THEPHP
namespace {$baseClass->getNamespaceName()};

/**
 * @internal
 */
final class AutoValue_{$baseClass->getShortName()} extends {$baseClass->getShortName()}
{
$propertyDeclarations
    
    protected function __construct(array \$propertyValues = [])
    {
        self::___checkRequiredPropertiesExist(\$propertyValues);
        
        foreach (\$propertyValues as \$property => \$value) {
            \$this->\$property = \$value;
        }
    }
    
$methodDeclarations
    
    /**
     * @internal
     */
    public static function ___checkRequiredPropertiesExist(array \$propertyValues): void
    {
        foreach ([$requiredPropertiesExported] as \$property) {
            if (!isset(\$propertyValues[\$property])) {
                throw new \Exception("Required property \$property not initialized.");
            }
        }
    }

    /**
     * @internal
     */
    public static function ___withTrustedValues(array \$propertyValues): self
    {
        return new self(\$propertyValues);
    }
}
THEPHP;
    }
}