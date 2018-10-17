<?php
namespace AutoValue\Console;

use AutoValue\TemplateDirectoryProcessor;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Josh Di Fabio <joshdifabio@gmail.com>
 */
class BuildCommand extends Command
{
    private $templateDirectoryProcessor;

    public function __construct(TemplateDirectoryProcessor $templateDirectoryProcessor)
    {
        $this->templateDirectoryProcessor = $templateDirectoryProcessor;
        parent::__construct('build');
    }

    protected function configure()
    {
        $this->addArgument('source', InputArgument::REQUIRED);
        $this->addArgument('target', InputArgument::OPTIONAL);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $sourceDirectory = \realpath($input->getArgument('source'));
        if (!$sourceDirectory) {
            throw new \Exception('The specified source directory does not exist.');
        }
        $specifiedTargetDirectory = $input->getArgument('target');
        if (!$specifiedTargetDirectory) {
            $targetDirectory = $sourceDirectory;
        } else {
            $targetDirectory = \realpath($specifiedTargetDirectory);
            if (!$targetDirectory) {
                throw new \Exception('The specified target directory does not exist.');
            }
        }

        foreach ($this->templateDirectoryProcessor->generateAutoClasses($sourceDirectory) as [$relativeFilePath, $autoClass]) {
            \fwrite(\STDERR, "$relativeFilePath\n");
            $absoluteFilePath = $targetDirectory . DIRECTORY_SEPARATOR . $relativeFilePath;
            \file_put_contents($absoluteFilePath, "<?php\n$autoClass\n");
        }
    }
}
