<?php
namespace AutoValue;

use Roave\BetterReflection\Reflector\ClassReflector;
use Roave\BetterReflection\SourceLocator\Ast\Locator as AstLocator;
use Roave\BetterReflection\SourceLocator\Type\SingleFileSourceLocator;

/**
 * @author Josh Di Fabio <joshdifabio@gmail.com>
 */
class TemplateClassLocator
{
    private const ANNOTATION_PATTERN = '{^\s*(/\*)?\*\s+@(?P<annotations>AutoValue(\\\\[^\s\*]*)?)[\s\*]*(\*/)?$}m';

    private $astLocator;

    public function __construct(AstLocator $astLocator)
    {
        $this->astLocator = $astLocator;
    }

    public function locateTemplateClasses(string $targetDir): \Iterator
    {
        $directoryIterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($targetDir));
        foreach ($directoryIterator as $path) {
            if (\pathinfo($path, \PATHINFO_EXTENSION) === 'php') {
                $relativeFilePath = $this->relativizeFilePath($targetDir, $path);
                foreach ($this->getTemplateClasses($path) as [$templateClassName, $annotations]) {
                    yield TemplateClass::of($templateClassName, $relativeFilePath, $annotations);
                }
            }
        }
    }

    private function getTemplateClasses(string $filePath): \Iterator
    {
        $fileContents = \file_get_contents($filePath);
        if (\preg_match(self::ANNOTATION_PATTERN, $fileContents) !== 1) {
            return;
        }
        $reflector = new ClassReflector(new SingleFileSourceLocator($filePath, $this->astLocator));
        foreach ($reflector->getAllClasses() as $reflectionClass) {
            if (\preg_match_all(self::ANNOTATION_PATTERN, $reflectionClass->getDocComment(), $matches)) {
                yield [$reflectionClass->getName(), $matches['annotations']];
            }
        }
    }

    private function relativizeFilePath(string $baseDir, string $filePath): string
    {
        $realDirPath = \realpath($baseDir);
        $realFilePath = \realpath($filePath);
        $relativePathOffset = \strpos($realFilePath, $realDirPath);
        if ($relativePathOffset !== 0) {
            return $realFilePath;
        }
        return \ltrim(\substr($realFilePath, \strlen($baseDir)), \DIRECTORY_SEPARATOR);
    }
}