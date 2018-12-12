<?php
namespace AutoValue\Test\Console\Build;

use MyTemplates\Address;
use MyTemplates\Command;
use MyTemplates\PostCode;
use PHPUnit\Framework\TestCase;

class BuildTest extends TestCase
{
    public function testAutoClassesAreGenerated(): void
    {
        $targetDir = $this->createTempDir();
        $expectedAutoClassFilePaths = $this->getExpectedAutoClassFilePaths($targetDir);

        $autoBinPath = dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR . 'auto';
        $sourceDir = __DIR__ . DIRECTORY_SEPARATOR . 'templates';
        $shellCommand = sprintf(
            '%s %s build %s %s',
            \PHP_BINARY,
            escapeshellarg($autoBinPath),
            escapeshellarg($sourceDir),
            escapeshellarg($targetDir)
        );
        exec($shellCommand, $stdoutLines, $exitCode);

        self::assertSame(0, $exitCode);
        self::assertArrayValuesSame($expectedAutoClassFilePaths, $stdoutLines);

        include $sourceDir . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
        $this->requireFiles($expectedAutoClassFilePaths);
    }

    public function testAddressAutoClass(): void
    {
        $address = null;
        try {
            $address = Address::builder()
                ->setLines('line1')
                ->setCountry('UK')
                ->build();
        } catch (\Exception $e) {
            self::assertSame('Required property postCode not initialized.', $e->getMessage());
        }
        self::assertNull($address);

        $addressBuilder = Address::builder()
            ->setLines('line1')
            ->setCountry('UK')
            ->setPostCode($postCode = new PostCode());
        $address1 = $addressBuilder->build();
        self::assertTrue($address1->equals($address1));
        self::assertTrue($address1->equals($addressBuilder->build()));
        self::assertSame(['line1'], $address1->lines());
        self::assertSame('UK', $address1->country());
        self::assertSame($postCode, $address1->postCode());

        // test @Memoize
        self::assertSame(0, $address1->n());
        self::assertSame(0, $address1->n());
        self::assertTrue($address1->equals($addressBuilder->build()));

        $address2 = Address::builder()
            ->setLines('line2')
            ->setCountry('UK')
            ->setPostCode($postCode = new PostCode())
            ->build();
        self::assertFalse($address2->equals($address1));
    }

    public function testCommandAutoClass(): void
    {
        $command1 = Command::of(
            'SaveAddress',
            [
                'address' => Address::builder()
                    ->setLines('line1')
                    ->setCountry('UK')
                    ->setPostCode($postCode = new PostCode())
                    ->build(),
            ]
        );

        $command2 = $command1->withPayload([
            'address' => Address::builder()
                ->setLines('line2')
                ->setCountry('UK')
                ->setPostCode($postCode = new PostCode())
                ->build(),
        ]);

        self::assertFalse($command1->equals($command2));

        self::assertTrue($command1->equals($command2->withPayload($command1->payload())));
    }

    public function testMemoizedValuesResetOnClone(): void
    {
        $address = Address::builder()
            ->setLines('foo')
            ->setCountry('UK')
            ->setPostCode(new PostCode())
            ->build();
        self::assertSame('foo', $address->firstLine());
        $address2 = $address->withLines('bar');
        self::assertSame('bar', $address2->firstLine());
    }

    private function createTempDir(): string
    {
        $path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'auto-value-php-tests-' . \random_int(1, 1000000);
        mkdir($path);
        return $path;
    }

    private function getExpectedAutoClassFilePaths(string $targetDir): array
    {
        return array_map(function (string $templateClassName) use ($targetDir) {
            return $this->getExpectedAutoClassFilePath($targetDir, $templateClassName);
        }, ['Address', 'AddressBuilder', 'Command']);
    }

    private function getExpectedAutoClassFilePath(string $targetDir, string $templateClassName): string
    {
        return $targetDir . DIRECTORY_SEPARATOR . "AutoValue_{$templateClassName}.php";
    }

    private function requireFiles(array $files): void
    {
        foreach ($files as $file) {
            require $file;
        }
    }

    private static function assertArrayValuesSame($expected, $actual): void
    {
        $expectedValues = array_values($expected);
        $actualValues = array_values($actual);
        sort($expectedValues);
        sort($actualValues);
        self::assertSame($expectedValues, $actualValues);
    }
}