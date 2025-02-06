<?php

declare(strict_types=1);

namespace ShopwarePluginSkeletonGenerator\Generator;

use Exception;
use Roave\BetterReflection\BetterReflection;
use Shopware\Core\Framework\Plugin\KernelPluginLoader\KernelPluginLoader;
use ShopwarePluginSkeletonGenerator\Render\TemplateRenderInterface;
use ShopwarePluginSkeletonGenerator\Util\Autoload;
use ShopwarePluginSkeletonGenerator\Util\CodeManipulator;
use ShopwarePluginSkeletonGenerator\Util\Str;
use Symfony\Component\Filesystem\Filesystem;

class Generator
{
    public function __construct(
        private readonly KernelPluginLoader $kernelPluginLoader,
        private readonly TemplateRenderInterface $templateRender,
        private readonly Filesystem $filesystem,
        private readonly string $projectDir,
    ) {}

    /**
     * @param string[] $additionalBundles
     */
    public function generate(
        string $namespace,
        string $pluginName,
        array $additionalBundles,
        bool $headless = false,
        bool $static = false,
        bool $append = false,
        bool $config = false,
    ): string {
        $pluginDir = $this->kernelPluginLoader->getPluginDir($this->projectDir);

        if ($static) {
            $pluginDir = \dirname($pluginDir) . '/static-plugins';
        }

        if ( ! $append && $this->filesystem->exists($pluginDir . '/' . $pluginName)) {
            throw new Exception(\sprintf('Plugin "%s" already exists.', $pluginName));
        }

        if ($append && ! $this->filesystem->exists($pluginDir . '/' . $pluginName)) {
            throw new Exception(\sprintf('Plugin "%s" does not exist. Cannot append bundles!', $pluginName));
        }

        if ( ! $append) {
            $pluginClassContent = $this->templateRender->render(__DIR__ . '/../Resources/skeletons/PluginClass.tpl.php', [
                'namespace' => $namespace,
                'pluginName' => $pluginName,
                'additionalBundles' => $additionalBundles,
            ]);

            $this->dumpFile($pluginDir . '/' . $pluginName . '/src/' . $pluginName . '.php', $pluginClassContent);

            $composerJsonContent = $this->templateRender->render(__DIR__ . '/../Resources/skeletons/config/composer.json.php', [
                'namespace' => str_replace('\\', '\\\\', $namespace),
                'withStorefront' => ! $headless,
                'pluginNameWithDash' => Str::camelCaseToDash($pluginName),
                'pluginName' => $pluginName,
                'shopwareVersion' => Autoload::getShopwareInstalledVersion(),
            ]);

            $this->dumpFile($pluginDir . '/' . $pluginName . '/composer.json', $composerJsonContent);

            $this->copyFile(__DIR__ . '/../Resources/skeletons/config/.php-cs-fixer.dist.php', $pluginDir . '/' . $pluginName . '/.php-cs-fixer.dist.php');
            $this->copyFile(__DIR__ . '/../Resources/skeletons/config/rector.php', $pluginDir . '/' . $pluginName . '/rector.php');
            $this->copyFile(__DIR__ . '/../Resources/skeletons/config/phpstan.neon', $pluginDir . '/' . $pluginName . '/phpstan.neon');
            $this->copyFile(__DIR__ . '/../Resources/skeletons/config/phpstan-baseline.neon', $pluginDir . '/' . $pluginName . '/phpstan-baseline.neon');
            $this->copyFile(__DIR__ . '/../Resources/skeletons/config/phpunit.xml.dist', $pluginDir . '/' . $pluginName . '/phpunit.xml.dist');

            $testBootstrapContent = $this->templateRender->render(__DIR__ . '/../Resources/skeletons/TestBootstrap.tpl.php', [
                'namespace' => str_replace('\\', '\\\\', $namespace),
                'pluginName' => $pluginName,
            ]);

            $this->dumpFile($pluginDir . '/' . $pluginName . '/tests/TestBootstrap.php', $testBootstrapContent);

            if ($config) {
                $this->copyFile(__DIR__ . '/../Resources/skeletons/config/config.xml', $pluginDir . '/' . $pluginName . '/src/Resources/config/config.xml');
            }

            if ([] === $additionalBundles) {
                $routesContent = $this->templateRender->render(__DIR__ . '/../Resources/skeletons/config/routes.xml.php');

                $this->dumpFile("$pluginDir/$pluginName/src/Resources/config/routes.xml", $routesContent);

                $servicesContent = $this->templateRender->render(__DIR__ . '/../Resources/skeletons/config/services.xml.php', [
                    'namespace' => $namespace,
                    'pluginName' => $pluginName,
                    'additionalBundleName' => false,
                ]);

                $this->dumpFile("$pluginDir/$pluginName/src/Resources/config/services.xml", $servicesContent);
                $this->dumpFile("$pluginDir/$pluginName/src/Controller/.gitkeep", '');
                $this->dumpFile("$pluginDir/$pluginName/src/Route/.gitkeep", '');
            }
        }

        $this->appendAdditionalBundles($additionalBundles, $namespace, $pluginDir, $pluginName);

        return $pluginDir . '/' . $pluginName;
    }

    /**
     * @param string[] $additionalBundles
     */
    private function appendAdditionalBundles(array $additionalBundles, string $namespace, string $pluginDir, string $pluginName): void
    {
        foreach ($additionalBundles as $additionalBundleName) {
            if ($this->filesystem->exists("$pluginDir/$pluginName/src/$additionalBundleName")) {
                continue;
            }

            $sectionBundleClassContent = $this->templateRender->render(__DIR__ . '/../Resources/skeletons/AdditionalBundle.tpl.php', [
                'namespace' => $namespace,
                'pluginName' => $pluginName,
                'additionalBundleName' => $additionalBundleName,
            ]);

            $this->dumpFile("$pluginDir/$pluginName/src/$additionalBundleName/$additionalBundleName.php", $sectionBundleClassContent);

            $routesContent = $this->templateRender->render(__DIR__ . '/../Resources/skeletons/config/routes.xml.php');

            $this->dumpFile("$pluginDir/$pluginName/src/$additionalBundleName/Resources/config/routes.xml", $routesContent);

            $servicesContent = $this->templateRender->render(__DIR__ . '/../Resources/skeletons/config/services.xml.php', [
                'namespace' => $namespace . '\\' . $additionalBundleName,
                'pluginName' => $additionalBundleName,
                'additionalBundleName' => $additionalBundleName,
            ]);

            $this->dumpFile("$pluginDir/$pluginName/src/$additionalBundleName/Resources/config/services.xml", $servicesContent);
            $this->dumpFile("$pluginDir/$pluginName/src/$additionalBundleName/Controller/.gitkeep", '');
            $this->dumpFile("$pluginDir/$pluginName/src/$additionalBundleName/Route/.gitkeep", '');
        }

        if ([] !== $additionalBundles) {
            $classInfo = (new BetterReflection())
                ->reflector()
                ->reflectClass($namespace . '\\' . $pluginName);

            $codeManipulator = new CodeManipulator(
                $this->filesystem->readFile($classInfo->getFileName()),
            );

            foreach ($additionalBundles as $additionalBundleName) {
                $codeManipulator->addAdditionalBundle($namespace . '\\' . $additionalBundleName);
                $this->dumpFile($classInfo->getFileName(), $codeManipulator->getSourceCode());
            }
        }
    }

    private function dumpFile(string $path, string $content): void
    {
        $this->filesystem->dumpFile($path, $content);
    }

    private function copyFile(string $origFile, string $destFile): void
    {
        $this->filesystem->copy($origFile, $destFile);
    }
}
