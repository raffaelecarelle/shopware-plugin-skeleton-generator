<?php

declare(strict_types=1);

namespace ShopwarePluginSkeletonGenerator\Tests\Linter;

use League\Flysystem\Filesystem;
use League\Flysystem\Local\LocalFilesystemAdapter;
use PHPUnit\Framework\TestCase;
use ShopwarePluginSkeletonGenerator\Linter\PhpLinter;

class PhpLinterTest extends TestCase
{
    private string $tmpFilePath;
    private Filesystem $filesystem;
    private string $tmpDir;

    protected function setUp(): void
    {
        $this->tmpDir = sys_get_temp_dir() . '/linter-test';
        $adapter = new LocalFilesystemAdapter($this->tmpDir);
        $this->filesystem = new Filesystem($adapter);
        $this->tmpFilePath = 'example.php';
        $this->filesystem->write($this->tmpFilePath, "<?php echo 'Hello, World!';");
    }

    protected function tearDown(): void
    {
        $this->filesystem->deleteDirectory('/');
    }

    public function testLinterForSingleFile(): void
    {
        $linter = new PhpLinter();
        $linter->lint($this->tmpDir . '/' . $this->tmpFilePath);

        self::assertFileExists($this->tmpDir . '/' . $this->tmpFilePath);

        self::assertStringEqualsFile($this->tmpDir . '/' . $this->tmpFilePath, <<<EOF
            <?php

            declare(strict_types=1);
            echo 'Hello, World!';

            EOF);
    }

    public function testLinterForArrayOfFiles(): void
    {
        $linter = new PhpLinter();
        $linter->lint([$this->tmpDir . '/' . $this->tmpFilePath]);

        self::assertFileExists($this->tmpDir . '/' . $this->tmpFilePath);

        self::assertStringEqualsFile($this->tmpDir . '/' . $this->tmpFilePath, <<<EOF
            <?php

            declare(strict_types=1);
            echo 'Hello, World!';

            EOF);
    }

    public function testLinterWithCustomBinaryPath(): void
    {
        $linter = new PhpLinter(__DIR__ . '/../../src/Resources/bin/php-cs-fixer.phar');
        $linter->lint($this->tmpDir . '/' . $this->tmpFilePath);

        self::assertFileExists($this->tmpDir . '/' . $this->tmpFilePath);

        self::assertStringEqualsFile($this->tmpDir . '/' . $this->tmpFilePath, <<<EOF
            <?php

            declare(strict_types=1);
            echo 'Hello, World!';

            EOF);
    }

    public function testLinterWithCustomConfigPath(): void
    {
        $linter = new PhpLinter(null, __DIR__ . '/../../.php-cs-fixer.dist.php');
        $linter->lint($this->tmpDir . '/' . $this->tmpFilePath);

        self::assertFileExists($this->tmpDir . '/' . $this->tmpFilePath);

        self::assertStringEqualsFile($this->tmpDir . '/' . $this->tmpFilePath, <<<EOF
            <?php

            declare(strict_types=1);
            echo 'Hello, World!';

            EOF);
    }
}
