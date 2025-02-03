<?php

declare(strict_types=1);

namespace ShopwarePluginSkeletonGenerator\Tests\Linter;

use PHPUnit\Framework\TestCase;
use ShopwarePluginSkeletonGenerator\Linter\PhpLinter;

class PhpLinterTest extends TestCase
{
    private string $tmpFilePath;

    protected function setUp(): void
    {
        $this->tmpFilePath = sys_get_temp_dir() . '/example.php';
        file_put_contents($this->tmpFilePath, "<?php echo 'Hello, World!';");
    }

    protected function tearDown(): void
    {
        if (file_exists($this->tmpFilePath)) {
            unlink($this->tmpFilePath);
        }
    }

    public function testLinterForSingleFile(): void
    {
        $linter = new PhpLinter();
        $linter->lint($this->tmpFilePath);

        self::assertFileExists($this->tmpFilePath);

        self::assertStringEqualsFile($this->tmpFilePath, <<<EOF
            <?php

            declare(strict_types=1);
            echo 'Hello, World!';

            EOF);
    }

    public function testLinterForArrayOfFiles(): void
    {
        $linter = new PhpLinter();
        $linter->lint([$this->tmpFilePath]);

        self::assertFileExists($this->tmpFilePath);

        self::assertStringEqualsFile($this->tmpFilePath, <<<EOF
            <?php

            declare(strict_types=1);
            echo 'Hello, World!';

            EOF);
    }

    public function testLinterWithCustomBinaryPath(): void
    {
        $linter = new PhpLinter(__DIR__ . '/../../src/Resources/bin/php-cs-fixer.phar');
        $linter->lint($this->tmpFilePath);

        self::assertFileExists($this->tmpFilePath);

        self::assertStringEqualsFile($this->tmpFilePath, <<<EOF
            <?php

            declare(strict_types=1);
            echo 'Hello, World!';

            EOF);
    }

    public function testLinterWithCustomConfigPath(): void
    {
        $linter = new PhpLinter(null, __DIR__ . '/../../.php-cs-fixer.dist.php');
        $linter->lint($this->tmpFilePath);

        self::assertFileExists($this->tmpFilePath);

        self::assertStringEqualsFile($this->tmpFilePath, <<<EOF
            <?php

            declare(strict_types=1);
            echo 'Hello, World!';

            EOF);
    }
}
