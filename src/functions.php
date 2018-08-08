<?php
namespace AutoValue;

use Roave\BetterReflection\Reflection\ReflectionClass;
use Roave\BetterReflection\Reflection\ReflectionMethod;
use Roave\BetterReflection\Reflection\ReflectionParameter;
use Roave\BetterReflection\Reflection\ReflectionType;

function generateConcreteMethod(ReflectionMethod $abstractMethod, string $methodBody): string
{
    $methodSignature = generateMethodSignature($abstractMethod);
    return <<<THEPHP
    $methodSignature
    {
$methodBody
    }
THEPHP;
}

function generateMethodSignature(ReflectionMethod $abstractMethod): string
{
    $visibility = $abstractMethod->isProtected() ? 'protected' : 'public';
    $parameters = generateParameters($abstractMethod->getParameters());
    $returnTypeHint = generateReturnTypeHint($abstractMethod->getReturnType(), $abstractMethod->getDeclaringClass());
    return "$visibility function {$abstractMethod->getShortName()}({$parameters}){$returnTypeHint}";
}

function generateParameters(array $reflectionParameters): string
{
    return \implode(', ', \array_map(function (ReflectionParameter $parameter) {
        $typeHintPart = $parameter->getType() ? generateTypeHint($parameter->getType(), $parameter->getDeclaringClass()) . ' ' : '';
        $defaultValuePart = $parameter->isDefaultValueAvailable() ? ' = ' . \var_export($parameter->getDefaultValue(), true) : '';
        return $typeHintPart . '$' . $parameter->getName() . $defaultValuePart;
    }, $reflectionParameters));
}

function generateReturnTypeHint(?ReflectionType $returnType, ?ReflectionClass $declaringClass): string
{
    return $returnType === null ? '' : ': ' . generateTypeHint($returnType, $declaringClass);
}

function generateTypeHint(ReflectionType $type, ?ReflectionClass $declaringClass): string
{
    $prefix = $type->allowsNull() ? '?' : '';
    $normalizedType = (string)$type === 'self'
        ? '\\' . $declaringClass->getName()
        : ($type->isBuiltin() ? (string)$type : '\\' . (string)$type);
    return $prefix . $normalizedType;
}

function getPropertyName(string $methodName, string $prefix): ?string
{
    if (\strpos($methodName, $prefix) !== 0) {
        return null;
    }

    $propertyName = \substr($methodName, \strlen($prefix));

    return \strlen($propertyName) > 0 && \ucfirst($propertyName) === $propertyName
        ? \lcfirst($propertyName)
        : null;
}

function splitFullyQualifiedName(string $fullyQualifiedName): array
{
    $lastSlashOffset = \strrpos($fullyQualifiedName, '\\');
    if ($lastSlashOffset === false) {
        return ['', $fullyQualifiedName];
    }
    return [
        \substr($fullyQualifiedName, 0, $lastSlashOffset),
        \substr($fullyQualifiedName, $lastSlashOffset + 1),
    ];
}

function isClass(ReflectionType $reflectionType): bool
{
    return (string)$reflectionType === 'self' || !$reflectionType->isBuiltin();
}

function getClass(ReflectionClass $declaringClass, ReflectionType $reflectionType): ?ReflectionClass
{
    if ((string)$reflectionType === 'self') {
        return $declaringClass;
    }
    if (!$reflectionType->isBuiltin()) {
        return $reflectionType->targetReflectionClass();
    }
    return null;
}