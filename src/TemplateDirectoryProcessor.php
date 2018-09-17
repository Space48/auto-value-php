<?php
namespace AutoValue;

use Composer\Autoload\ClassLoader;
use Roave\BetterReflection\Reflector\ClassReflector;
use Roave\BetterReflection\SourceLocator\Type\AggregateSourceLocator;
use Roave\BetterReflection\SourceLocator\Type\ComposerSourceLocator;
use Roave\BetterReflection\SourceLocator\Ast\Locator as AstLocator;
use Roave\BetterReflection\SourceLocator\Type\SourceLocator;

/**
 * @author Josh Di Fabio <joshdifabio@gmail.com>
 */
class TemplateDirectoryProcessor
{
    private $astLocator;
    private $autoClassLocator;
    private $autoClassTypeMap;
    private $sourceLocator;

    /**
     * @param AutoClassType[] $autoClassTypes
     */
    public function __construct(
        AstLocator $astLocator,
        TemplateClassLocator $templateClassLocator,
        array $autoClassTypes,
        SourceLocator $sourceLocator
    ) {
        $this->astLocator = $astLocator;
        $this->autoClassLocator = $templateClassLocator;
        $this->autoClassTypeMap = [];
        foreach ($autoClassTypes as $autoClassType) {
            $this->autoClassTypeMap[$autoClassType->annotation()] = $autoClassType;
        }
        $this->sourceLocator = $sourceLocator;
    }

    public function generateAutoClasses(string $directory): \Iterator
    {
        $composerClassLoader = $this->getComposerClassLoader($directory);
        $composerSourceLocator = new ComposerSourceLocator($composerClassLoader, $this->astLocator);
        $sourceLocator = new AggregateSourceLocator([$this->sourceLocator, $composerSourceLocator]);
        $classReflector = new ClassReflector($sourceLocator);
        $templateClasses = $this->autoClassLocator->locateTemplateClasses($directory);
        /** @var TemplateClass $templateClass */
        foreach ($templateClasses as $templateClass) {
            $autoClassType = $this->getClassType($templateClass);
            $autoFilename = 'AutoValue_' . \basename($templateClass->relativeFilePath());
            $autoFilePath = \strpos($templateClass->relativeFilePath(), \DIRECTORY_SEPARATOR) !== false
                ? \dirname($templateClass->relativeFilePath()) . \DIRECTORY_SEPARATOR . $autoFilename
                : $autoFilename;
            yield [$autoFilePath, $autoClassType->generateAutoClass($classReflector, $templateClass->className())];
        }
    }

    private function getComposerClassLoader(string $dir): ClassLoader
    {
        do {
            $autoloadPath = $dir . \DIRECTORY_SEPARATOR . 'vendor' . \DIRECTORY_SEPARATOR . 'autoload.php';
            if (\file_exists($autoloadPath)) {
                return require $autoloadPath;
            }
            $previousDir = $dir;
            $dir = \dirname($dir);
        } while ($dir !== $previousDir);

        throw new \Exception('Failed to find vendor autoload file.');
    }

    private function getClassType(TemplateClass $templateClass): ?AutoClassType
    {
        $matches = [];

        foreach ($templateClass->annotations() as $annotation) {
            if (isset($this->autoClassTypeMap[$annotation])) {
                $matches[] = $this->autoClassTypeMap[$annotation];
            }
        }

        switch (\count($matches)) {
            case 1:
                return $matches[0];

            case 0:
                throw new \Exception('No recognised AutoValue annotations found on class ' . $templateClass->className());

            default:
                throw new \Exception('Multiple AutoValue annotations found on class ' . $templateClass->className());
        }
    }
}
                                                          