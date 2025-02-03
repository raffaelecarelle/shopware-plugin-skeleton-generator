<?php

declare(strict_types=1);

namespace ShopwarePluginSkeletonGenerator\Tests\Command;

use App\Example;
use Composer\Autoload\ClassLoader;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Framework\Plugin\KernelPluginLoader\StaticKernelPluginLoader;
use ShopwarePluginSkeletonGenerator\Command\PluginSkeletonGenerateCommand;
use ShopwarePluginSkeletonGenerator\Generator\Generator;
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
        ));
    }

    protected function tearDown(): void
    {
        $filesystem = new Filesystem();
        $filesystem->remove(__DIR__ . '/../Fixtures/custom');
    }
}
