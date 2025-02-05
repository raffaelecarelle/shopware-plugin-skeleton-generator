<?php

declare(strict_types=1);

namespace ShopwarePluginSkeletonGenerator\Tests\Command;

use App\Example;
use Composer\Autoload\ClassLoader;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Framework\Plugin\KernelPluginLoader\StaticKernelPluginLoader;
use ShopwarePluginSkeletonGenerator\Command\PluginSkeletonGenerateCommand;
use ShopwarePluginSkeletonGenerator\Generator\Generator;
use ShopwarePluginSkeletonGenerator\Linter\JsonLinter;
use ShopwarePluginSkeletonGenerator\Linter\PhpLinter;
use ShopwarePluginSkeletonGenerator\Linter\XmlLinter;
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
        $this->commandTester->execute(['fullyQualifiedPluginName' => Example::class], ['capture_stderr_separately' => true]);
        $this->commandTester->assertCommandIsSuccessful();

        $expected = <<<'EOF'
            <?php

            declare(strict_types=1);

            use Shopware\Core\TestBootstrapper;

            $loader = (new TestBootstrapper())
                ->addCallingPlugin()
                ->addActivePlugins('Example')
                ->setForceInstallPlugins(true)
                ->bootstrap()
                ->getClassLoader();

            $loader->addPsr4('App\\Tests\\', __DIR__);

            EOF;
        self::assertFileExists(__DIR__ . '/../Fixtures/custom/plugins/Example/tests/TestBootstrap.php');
        self::assertSame($expected, file_get_contents(__DIR__ . '/../Fixtures/custom/plugins/Example/tests/TestBootstrap.php'));

        $expected = <<<'EOF'
            <?php

            declare(strict_types=1);

            namespace App;

            use Shopware\Core\Framework\Plugin;

            class Example extends Plugin {}

            EOF;
        self::assertFileExists(__DIR__ . '/../Fixtures/custom/plugins/Example/src/Example.php');
        self::assertSame($expected, file_get_contents(__DIR__ . '/../Fixtures/custom/plugins/Example/src/Example.php'));

        $expected = <<<'EOF'
            <?xml version="1.0"?>
            <container xmlns="http://symfony.com/schema/dic/services" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd">
              <services>
                <defaults autowire="true" autoconfigure="true" public="false"/>
                <prototype namespace="App\" resource="../src" exclude="../src/{DependencyInjection,Entity,Example.php}"/>
              </services>
            </container>

            EOF;
        self::assertFileExists(__DIR__ . '/../Fixtures/custom/plugins/Example/src/Resources/config/services.xml');
        self::assertSame($expected, file_get_contents(__DIR__ . '/../Fixtures/custom/plugins/Example/src/Resources/config/services.xml'));

        $expected = <<<'EOF'
            <?xml version="1.0" encoding="UTF-8"?>
            <routes xmlns="http://symfony.com/schema/routing" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://symfony.com/schema/routing         https://symfony.com/schema/routing/routing-1.0.xsd">
              <import resource="../../Controller/**/*Controller.php" type="attribute"/>
              <import resource="../../Route/**/*Route.php" type="attribute"/>
            </routes>

            EOF;
        self::assertFileExists(__DIR__ . '/../Fixtures/custom/plugins/Example/src/Resources/config/routes.xml');
        self::assertSame($expected, file_get_contents(__DIR__ . '/../Fixtures/custom/plugins/Example/src/Resources/config/routes.xml'));

        $expected = <<<'EOF'
            {
                "name": "example/example",
                "description": "",
                "type": "shopware-platform-plugin",
                "version": "1.0.0",
                "license": "MIT",
                "require": {
                    "shopware/core": "~6.6.9.0",
                    "shopware/administration": "~6.6.9.0",
                    "shopware/storefront": "~6.6.9.0"
                },
                "require-dev": {
                    "friendsofphp/php-cs-fixer": "^3.64",
                    "frosh/shopware-rector": "^0.5",
                    "phpstan/extension-installer": "^1.4",
                    "phpstan/phpstan-deprecation-rules": "^2.0.1",
                    "phpstan/phpstan-phpunit": "^2.0.4",
                    "phpstan/phpstan-strict-rules": "^2.0.3",
                    "phpunit/phpunit": "^11.4.2",
                    "shopwarelabs/phpstan-shopware": "^0.1.3"
                },
                "autoload": {
                    "psr-4": {
                        "App\\": "src/"
                    }
                },
                "autoload-dev": {
                    "psr-4": {
                        "App\\Tests\\": "tests/"
                    }
                },
                "config": {
                    "sort-packages": true,
                    "allow-plugins": {
                        "phpstan/extension-installer": true,
                        "symfony/runtime": true
                    }
                },
                "extra": {
                    "shopware-plugin-class": "App\\Example",
                    "plugin-icon": "src/Resources/config/plugin-icon.png",
                    "copyright": "(c) by YourCompany",
                    "label": {
                        "de-DE": "de label",
                        "en-GB": "en label"
                    },
                    "description": {
                        "de-DE": "de description",
                        "en-GB": "en description"
                    },
                    "manufacturerLink": {
                        "de-DE": "https://www.yoursite.it/",
                        "en-GB": "https://www.yoursite.it/"
                    },
                    "supportLink": {
                        "de-DE": "https://www.yoursite.it/support/",
                        "en-GB": "https://www.yoursite.it/support/",
                        "es-ES": "https://www.yoursite.it/support/",
                        "it-IT": "https://www.yoursite.it/support/"
                    }
                }
            }
            EOF;
        self::assertFileExists(__DIR__ . '/../Fixtures/custom/plugins/Example/composer.json');
        self::assertSame($expected, file_get_contents(__DIR__ . '/../Fixtures/custom/plugins/Example/composer.json'));

        $expected = <<<'EOF'
            <?php

            declare(strict_types=1);

            use PhpCsFixer\Config;
            use PhpCsFixer\Finder;
            use PhpCsFixer\Runner\Parallel\ParallelConfigFactory;

            $finder = Finder::create()
                ->in(__DIR__ . '/src')
                ->in(__DIR__ . '/tests')
                ->append([__DIR__ . '/.php-cs-fixer.php']);

            return (new Config())
                ->setCacheFile('.php_cs.cache')
                ->setRiskyAllowed(true)
                ->setParallelConfig(ParallelConfigFactory::detect())
                ->setRules(
                    [
                        '@PSR12' => true,
                        '@PSR12:risky' => true,
                        '@Symfony' => true,
                        '@Symfony:risky' => true
                    ],
                )
                ->setFinder($finder);

            EOF;
        self::assertFileExists(__DIR__ . '/../Fixtures/custom/plugins/Example/.php-cs-fixer.dist.php');
        self::assertSame($expected, file_get_contents(__DIR__ . '/../Fixtures/custom/plugins/Example/.php-cs-fixer.dist.php'));

        $expected = <<<'EOF'
            <?php

            declare(strict_types=1);

            use Frosh\Rector\Set\ShopwareSetList;
            use Rector\Config\RectorConfig;
            use Rector\Php82\Rector\Class_\ReadOnlyClassRector;
            use Rector\PHPUnit\Set\PHPUnitSetList;

            return RectorConfig::configure()
                ->withImportNames(
                    importShortClasses: false,
                )
                ->withParallel()
                ->withPaths([
                    __FILE__,
                    __DIR__ . '/src',
                    __DIR__ . '/tests',
                ])
                ->withPhpSets()
                ->withPreparedSets(
                    deadCode: true,
                    codeQuality: true,
                    typeDeclarations: true,
                )
                ->withSets([
                    PHPUnitSetList::PHPUNIT_110,
                    ShopwareSetList::SHOPWARE_6_6_0,
                ])
                ->withRules([
                    ReadOnlyClassRector::class,
                ]);

            EOF;
        self::assertFileExists(__DIR__ . '/../Fixtures/custom/plugins/Example/rector.php');
        self::assertSame($expected, file_get_contents(__DIR__ . '/../Fixtures/custom/plugins/Example/rector.php'));

        $expected = <<<'EOF'
            includes:
                - phpstan-baseline.neon

            parameters:
                level: 7
                paths:
                    - %currentWorkingDirectory%/src
                    - %currentWorkingDirectory%/tests

            EOF;
        self::assertFileExists(__DIR__ . '/../Fixtures/custom/plugins/Example/phpstan.neon');
        self::assertSame($expected, file_get_contents(__DIR__ . '/../Fixtures/custom/plugins/Example/phpstan.neon'));

        $expected = <<<'EOF'
            <?xml version="1.0" encoding="UTF-8"?>
            <phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                     xsi:noNamespaceSchemaLocation="https://schema.phpunit.de/10.4/phpunit.xsd"
                     bootstrap="tests/TestBootstrap.php"
                     executionOrder="random"
                     colors="true"
            >
                <coverage includeUncoveredFiles="true"/>
                <source>
                    <include>
                        <directory>./src/</directory>
                    </include>
                </source>
                <php>
                    <ini name="error_reporting" value="-1" />
                    <server name="KERNEL_CLASS" value="Shopware\Core\Kernel"/>
                    <env name="APP_ENV" value="test" />
                    <env name="APP_DEBUG" value="1" />
                    <env name="APP_SECRET" value="s$cretf0rt3st" />
                    <env name="SHOPWARE_HTTP_CACHE_ENABLED" value="0" />

                    <env name="SHELL_VERBOSITY" value="-1" />
                    <server name="MAILER_URL" value="smtp://localhost:1025"/>
                    <server name="HTTPS" value="off"/>
                    <!--To see the full stackTrace of a Deprecation set the value to a regex matching the deprecation warning-->
                    <!--https://symfony.com/doc/current/components/phpunit_bridge.html#display-the-full-stack-trace-->
                    <!--        <env name="SYMFONY_DEPRECATIONS_HELPER" value="ignoreFile=./deprecation.ignore" />-->
                    <env name="SYMFONY_DEPRECATIONS_HELPER" value="disabled" />
                </php>
                <testsuites>
                    <testsuite name="tests">
                        <directory>tests</directory>
                    </testsuite>
                </testsuites>
                <extensions>
                    <bootstrap class="Ergebnis\PHPUnit\SlowTestDetector\Extension"/>
                    <!-- Enable to see the db side effects of the tests. -->
                    <!--        <bootstrap class="Shopware\Core\Test\PHPUnit\Extension\DatabaseDiff\DatabaseDiffExtension"/>-->
                </extensions>
            </phpunit>

            EOF;

        self::assertFileExists(__DIR__ . '/../Fixtures/custom/plugins/Example/phpunit.xml.dist');
        self::assertSame($expected, file_get_contents(__DIR__ . '/../Fixtures/custom/plugins/Example/phpunit.xml.dist'));

        self::assertFileExists(__DIR__ . '/../Fixtures/custom/plugins/Example/phpstan-baseline.neon');
        self::assertFileExists(__DIR__ . '/../Fixtures/custom/plugins/Example/src/Route/.gitkeep');
        self::assertFileExists(__DIR__ . '/../Fixtures/custom/plugins/Example/src/Controller/.gitkeep');
    }

    public function testExecuteWithHeadlessFlag(): void
    {
        $this->commandTester->execute([
            'fullyQualifiedPluginName' => Example::class,
            '--headless' => true,
        ], ['capture_stderr_separately' => true]);

        $this->commandTester->assertCommandIsSuccessful();

        self::assertFileExists(__DIR__ . '/../Fixtures/custom/plugins/Example/tests/TestBootstrap.php');
        self::assertFileExists(__DIR__ . '/../Fixtures/custom/plugins/Example/src/Example.php');
        self::assertFileExists(__DIR__ . '/../Fixtures/custom/plugins/Example/src/Resources/config/services.xml');
        self::assertFileExists(__DIR__ . '/../Fixtures/custom/plugins/Example/src/Resources/config/routes.xml');
        self::assertFileExists(__DIR__ . '/../Fixtures/custom/plugins/Example/src/Route/.gitkeep');
        self::assertFileExists(__DIR__ . '/../Fixtures/custom/plugins/Example/src/Controller/.gitkeep');
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
            'fullyQualifiedPluginName' => Example::class,
            '--additionalBundle' => ['Core', 'Administration'],
        ], ['capture_stderr_separately' => true]);

        $this->commandTester->assertCommandIsSuccessful();

        self::assertFileExists(__DIR__ . '/../Fixtures/custom/plugins/Example/src/Example.php');
        self::assertFileExists(__DIR__ . '/../Fixtures/custom/plugins/Example/src/Core/Resources/config/services.xml');
        self::assertFileExists(__DIR__ . '/../Fixtures/custom/plugins/Example/src/Core/Resources/config/routes.xml');
        self::assertFileExists(__DIR__ . '/../Fixtures/custom/plugins/Example/src/Administration/Resources/config/services.xml');
        self::assertFileExists(__DIR__ . '/../Fixtures/custom/plugins/Example/src/Administration/Resources/config/routes.xml');
        self::assertFileExists(__DIR__ . '/../Fixtures/custom/plugins/Example/src/Administration/Route/.gitkeep');
        self::assertFileExists(__DIR__ . '/../Fixtures/custom/plugins/Example/src/Administration/Controller/.gitkeep');
        self::assertFileExists(__DIR__ . '/../Fixtures/custom/plugins/Example/src/Core/Route/.gitkeep');
        self::assertFileExists(__DIR__ . '/../Fixtures/custom/plugins/Example/src/Core/Controller/.gitkeep');
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
            'fullyQualifiedPluginName' => Example::class,
        ], ['capture_stderr_separately' => true]);

        self::assertSame('Plugin "Example" already exists.' . "\n", $this->commandTester->getDisplay());
    }

    public function testExecuteAppendOptionWithoutAdditionBundlesShouldGiveError(): void
    {
        $fs = new Filesystem();

        $fs->mkdir(__DIR__ . '/../Fixtures/custom/plugins/Example');

        $this->commandTester->execute([
            'fullyQualifiedPluginName' => Example::class,
            '--append' => true,
        ], ['capture_stderr_separately' => true]);

        self::assertSame('The additionalBundle option is mandatory with --appen option' . "\n", $this->commandTester->getDisplay());
    }

    public function testExecuteAppendOptionNonExistentPluginShouldGiveError(): void
    {
        $this->commandTester->execute([
            'fullyQualifiedPluginName' => Example::class,
            '--append' => true,
            '--additionalBundle' => ['Elasticsearch'],
        ], ['capture_stderr_separately' => true]);

        self::assertSame('Plugin "Example" does not exist. Cannot append bundles!' . "\n", $this->commandTester->getDisplay());
    }

    public function testExecuteAppendOption(): void
    {
        $fs = new Filesystem();

        $fs->mkdir(__DIR__ . '/../Fixtures/custom/plugins/Example');

        $this->commandTester->execute([
            'fullyQualifiedPluginName' => Example::class,
            '--append' => true,
            '--additionalBundle' => ['Elasticsearch'],
        ], ['capture_stderr_separately' => true]);

        $this->commandTester->assertCommandIsSuccessful();
        self::assertFileExists(__DIR__ . '/../Fixtures/custom/plugins/Example/src/Elasticsearch/Resources/config/services.xml');
        self::assertFileExists(__DIR__ . '/../Fixtures/custom/plugins/Example/src/Elasticsearch/Resources/config/routes.xml');
        self::assertFileExists(__DIR__ . '/../Fixtures/custom/plugins/Example/src/Elasticsearch/Route/.gitkeep');
        self::assertFileExists(__DIR__ . '/../Fixtures/custom/plugins/Example/src/Elasticsearch/Controller/.gitkeep');
    }

    protected function setUp(): void
    {
        parent::setUp();

        $filesystem = new Filesystem();
        $filesystem->copy(__DIR__ . '/../Fixtures/App/Example.php', __DIR__ . '/../Fixtures/App/Example.php.bk', true);

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
            new XmlLinter(),
            new JsonLinter(),
        ));
    }

    protected function tearDown(): void
    {
        $filesystem = new Filesystem();
        $filesystem->remove(__DIR__ . '/../Fixtures/custom');
        $filesystem->rename(__DIR__ . '/../Fixtures/App/Example.php.bk', __DIR__ . '/../Fixtures/App/Example.php', true);
    }
}
