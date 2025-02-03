<?php

declare(strict_types=1);

namespace ShopwarePluginSkeletonGenerator\Tests\Command;

use App\Example;
use Composer\Autoload\ClassLoader;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Framework\Plugin\KernelPluginLoader\StaticKernelPluginLoader;
use ShopwarePluginSkeletonGenerator\Command\PluginSkeletonGenerateCommand;
use ShopwarePluginSkeletonGenerator\Generator\Generator;
use ShopwarePluginSkeletonGenerator\Linter\PhpLinter;
use ShopwarePluginSkeletonGenerator\Render\SimplePhpTemplateRender;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Filesystem\Filesystem;

class SkeletonGeneratorCommandTest extends TestCase
{
    private CommandTester $commandTester;

    public function testExecuteWithNoArguments(): void
    {
        $this->commandTester->execute([], ['capture_stderr_separately' => true]);

        self::assertStringContainsString('The plugin name (FQN) must be in a valid namespace', $this->commandTester->getDisplay());
    }

    public function testExecuteWithPluginName(): void
    {
        // @phpstan-ignore-next-line
        $this->commandTester->execute(['fullyQualifiedPluginName' => Example::class], ['capture_stderr_separately' => true]);
        $this->commandTester->assertCommandIsSuccessful();

        self::assertFileExists(__DIR__ . '/../Fixtures/custom/plugins/Example/tests/TestBootstrap.php');
        self::assertFileExists(__DIR__ . '/../Fixtures/custom/plugins/Example/src/Example.php');
        self::assertFileExists(__DIR__ . '/../Fixtures/custom/plugins/Example/src/Resources/config/services.xml');
        self::assertFileExists(__DIR__ . '/../Fixtures/custom/plugins/Example/src/Resources/config/routes.xml');
        self::assertFileExists(__DIR__ . '/../Fixtures/custom/plugins/Example/composer.json');
        self::assertFileExists(__DIR__ . '/../Fixtures/custom/plugins/Example/.php-cs-fixer.dist.php');
        self::assertFileExists(__DIR__ . '/../Fixtures/custom/plugins/Example/rector.php');
        self::assertFileExists(__DIR__ . '/../Fixtures/custom/plugins/Example/phpstan.neon');
        self::assertFileExists(__DIR__ . '/../Fixtures/custom/plugins/Example/phpstan-baseline.neon');
        self::assertFileExists(__DIR__ . '/../Fixtures/custom/plugins/Example/phpunit.xml.dist');
    }

    public function testExecuteWithAdditionalBundles(): void
    {
        $this->commandTester->execute([
            // @phpstan-ignore-next-line
            'fullyQualifiedPluginName' => Example::class,
            '--additionalBundle' => ['Core', 'Administration'],
        ], ['capture_stderr_separately' => true]);

        $this->commandTester->assertCommandIsSuccessful();

        self::assertFileExists(__DIR__ . '/../Fixtures/custom/plugins/Example/src/Example.php');
        self::assertFileExists(__DIR__ . '/../Fixtures/custom/plugins/Example/src/Core/Resources/config/services.xml');
        self::assertFileExists(__DIR__ . '/../Fixtures/custom/plugins/Example/src/Core/Resources/config/routes.xml');
        self::assertFileExists(__DIR__ . '/../Fixtures/custom/plugins/Example/src/Administration/Resources/config/services.xml');
        self::assertFileExists(__DIR__ . '/../Fixtures/custom/plugins/Example/src/Administration/Resources/config/routes.xml');
        self::assertFileExists(__DIR__ . '/../Fixtures/custom/plugins/Example/composer.json');
        self::assertFileExists(__DIR__ . '/../Fixtures/custom/plugins/Example/.php-cs-fixer.dist.php');
        self::assertFileExists(__DIR__ . '/../Fixtures/custom/plugins/Example/rector.php');
        self::assertFileExists(__DIR__ . '/../Fixtures/custom/plugins/Example/phpstan.neon');
        self::assertFileExists(__DIR__ . '/../Fixtures/custom/plugins/Example/phpstan-baseline.neon');
        self::assertFileExists(__DIR__ . '/../Fixtures/custom/plugins/Example/phpunit.xml.dist');
    }

    public function testExecuteWithStaticFlag(): void
    {
        $this->commandTester->execute([
            // @phpstan-ignore-next-line
            'fullyQualifiedPluginName' => Example::class,
            '--static' => true,
        ], ['capture_stderr_separately' => true]);

        $this->commandTester->assertCommandIsSuccessful();

        self::assertFileExists(__DIR__ . '/../Fixtures/custom/static-plugins/Example/src/Example.php');
        self::assertFileExists(__DIR__ . '/../Fixtures/custom/static-plugins/Example/src/Resources/config/services.xml');
        self::assertFileExists(__DIR__ . '/../Fixtures/custom/static-plugins/Example/src/Resources/config/routes.xml');
        self::assertFileExists(__DIR__ . '/../Fixtures/custom/static-plugins/Example/composer.json');
        self::assertFileExists(__DIR__ . '/../Fixtures/custom/static-plugins/Example/.php-cs-fixer.dist.php');
        self::assertFileExists(__DIR__ . '/../Fixtures/custom/static-plugins/Example/rector.php');
        self::assertFileExists(__DIR__ . '/../Fixtures/custom/static-plugins/Example/phpstan.neon');
        self::assertFileExists(__DIR__ . '/../Fixtures/custom/static-plugins/Example/phpstan-baseline.neon');
        self::assertFileExists(__DIR__ . '/../Fixtures/custom/static-plugins/Example/phpunit.xml.dist');
    }

    public function testExecutePluginAlreadyExistsShouldDoNothing(): void
    {
        $fs = new Filesystem();

        $fs->mkdir(__DIR__ . '/../Fixtures/custom/plugins/Example');

        $this->commandTester->execute([
            // @phpstan-ignore-next-line
            'fullyQualifiedPluginName' => Example::class,
        ], ['capture_stderr_separately' => true]);

        self::assertSame('Plugin "Example" already exists.' . "\n", $this->commandTester->getDisplay());
    }

    public function testExecuteAppendOptionWithoutAdditionBundlesShouldGiveError(): void
    {
        $fs = new Filesystem();

        $fs->mkdir(__DIR__ . '/../Fixtures/custom/plugins/Example');

        $this->commandTester->execute([
            // @phpstan-ignore-next-line
            'fullyQualifiedPluginName' => Example::class,
            '--append' => true,
        ], ['capture_stderr_separately' => true]);

        self::assertSame('The additionalBundle option is mandatory with --appen option' . "\n", $this->commandTester->getDisplay());
    }

    public function testExecuteAppendOption(): void
    {
        $fs = new Filesystem();

        $fs->mkdir(__DIR__ . '/../Fixtures/custom/plugins/Example');

        $this->commandTester->execute([
            // @phpstan-ignore-next-line
            'fullyQualifiedPluginName' => Example::class,
            '--append' => true,
            '--additionalBundle' => ['Elasticsearch'],
        ], ['capture_stderr_separately' => true]);

        $this->commandTester->assertCommandIsSuccessful();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->commandTester = new CommandTester(new PluginSkeletonGenerateCommand(
            new Generator(
                new StaticKernelPluginLoader(
                    new ClassLoader(__DIR__ . '/../../vendor'),
                ),
                new SimplePhpTemplateRender(),
                new Filesystem(),
                __DIR__ . '/../Fixtures',
            ),
            new PhpLinter(),
        ));
    }

    protected function tearDown(): void
    {
        $filesystem = new Filesystem();
        $filesystem->remove(__DIR__ . '/../Fixtures/custom');
    }
}
