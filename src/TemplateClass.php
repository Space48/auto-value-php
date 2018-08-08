<?php
namespace AutoValue;

/**
 * @author Josh Di Fabio <joshdifabio@gmail.com>
 */
class TemplateClass
{
    public static function of(string $className, string $relativeFilePath, array $annotations): self
    {
        $templateClass = new self;
        $templateClass->className = $className;
        $templateClass->relativeFilePath = $relativeFilePath;
        $templateClass->annotations = $annotations;
        return $templateClass;
    }

    public function className(): string
    {
        return $this->className;
    }

    public function relativeFilePath(): string
    {
        return $this->relativeFilePath;
    }

    public function annotations(): array
    {
        return $this->annotations;
    }

    private $className;
    private $relativeFilePath;
    private $annotations;

    private function __construct()
    {

    }
}