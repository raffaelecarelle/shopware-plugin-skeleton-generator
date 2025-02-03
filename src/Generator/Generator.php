<?php

declare(strict_types=1);

namespace ShopwarePluginSkeletonGenerator\Generator;

use Exception;
use Shopware\Core\Framework\Plugin\KernelPluginLoader\KernelPluginLoader;
use ShopwarePluginSkeletonGenerator\Render\TemplateRenderInterface;
use ShopwarePluginSkeletonGenerator\Util\Autoload;
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
    ): string {
        $pluginDir = $this->kernelPluginLoader->getPluginDir($this->projectDir);

        if ($static) {
            $pluginDir = \dirname($pluginDir) . '/static-plugins';
        }

        if ( ! $append && $this->filesystem->exists($pluginDir . '/' . $pluginName)) {
            throw new Exception(\sprintf('Plugin "%s" already exists.', $pluginName));
        }

        if ( ! $append) {
            $pluginClassContent = $this->templateRender->render(__DIR__ . '/../Resources/skeletons/PluginClass.tpl.php', [
                'namespace' => $namespace,
                'pluginName' => $pluginName,
                'additionalBundleName' => $additionalBundles,
            ]);

            $this->dump($pluginDir . '/' . $pluginName . '/src/' . $pluginName . '.php', $pluginClassContent);

            $composerJsonContent = $this->templateRender->render(__DIR__ . '/../Resources/skeletons/config/composer.json.php', [
                'namespace' => $namespace,
                'withStorefront' => ! $headless,
                'pluginNameWithDash' => Str::camelCaseToDash($pluginName),
                'pluginName' => $pluginName,
                'shopwareVersion' => Autoload::getShopwareInstalledVersion(),
            ]);

            $this->dump($pluginDir . '/' . $pluginName . '/composer.json', $composerJsonContent);

            $this->copy(__DIR__ . '/../Resources/skeletons/config/.php-cs-fixer.dist.php', $pluginDir . '/' . $pluginName . '/.php-cs-fixer.dist.php');
            $this->copy(__DIR__ . '/../Resources/skeletons/config/rector.php', $pluginDir . '/' . $pluginName . '/rector.php');
            $this->copy(__DIR__ . '/../Resources/skeletons/config/phpstan.neon', $pluginDir . '/' . $pluginName . '/phpstan.neon');
            $this->copy(__DIR__ . '/../Resources/skeletons/config/phpstan-baseline.neon', $pluginDir . '/' . $pluginName . '/phpstan-baseline.neon');
            $this->copy(__DIR__ . '/../Resources/skeletons/config/phpunit.xml.dist', $pluginDir . '/' . $pluginName . '/phpunit.xml.dist');

            $testBootstrapContent = $this->templateRender->render(__DIR__ . '/../Resources/skeletons/TestBootstrap.tpl.php', [
                'namespace' => $namespace,
                'pluginName' => $pluginName
            ]);

            $this->dump($pluginDir . '/' . $pluginName . '/tests/TestBootstrap.php', $testBootstrapContent);

            if ([] === $additionalBundles) {
                $routesContent = $this->templateRender->render(__DIR__ . '/../Resources/skeletons/config/routes.xml.php', [
                    'withStorefront' => ! $headless,
                    'withAdmin' => true,
                ]);

                $this->dump("$pluginDir/$pluginName/src/Resources/config/routes.xml", $routesContent);

                $servicesContent = $this->templateRender->render(__DIR__ . '/../Resources/skeletons/config/services.xml.php', [
                    'namespace' => $namespace,
                    'pluginName' => $pluginName,
                    'additionalBundleName' => [],
                ]);

                $this->dump("$pluginDir/$pluginName/src/Resources/config/services.xml", $servicesContent);
            }
        }

        $this->appendAdditionalBundles($additionalBundles, $namespace, $pluginDir, $pluginName, $headless);

        return $pluginDir . '/' . $pluginName;
    }

    /**
     * @param string[] $additionalBundles
     */
    private function appendAdditionalBundles(array $additionalBundles, string $namespace, string $pluginDir, string $pluginName, bool $headless): void
    {
        foreach ($additionalBundles as $additionalBundle) {
            $sectionBundleClassContent = $this->templateRender->render(__DIR__ . '/../Resources/skeletons/AdditionalBundle.tpl.php', [
                'namespace' => $namespace,
                'additionalBundleName' => $additionalBundle,
            ]);

            $this->dump("$pluginDir/$pluginName/src/$additionalBundle/$pluginName$additionalBundle.php", $sectionBundleClassContent);

            $routesContent = $this->templateRender->render(__DIR__ . '/../Resources/skeletons/config/routes.xml.php', [
                'withStorefront' => ! $headless,
                'withAdmin' => 'Administration' === $additionalBundle,
            ]);

            $this->dump("$pluginDir/$pluginName/src/$additionalBundle/Resources/config/routes.xml", $routesContent);

            $servicesContent = $this->templateRender->render(__DIR__ . '/../Resources/skeletons/config/services.xml.php', [
                'namespace' => $namespace . '\\' . $pluginName . $additionalBundle,
                'pluginName' => $pluginName . $additionalBundle,
            ]);

            $this->dump("$pluginDir/$pluginName/src/$additionalBundle/Resources/config/services.xml", $servicesContent);
        }
    }

    private function dump(string $path, string $content): void
    {
        $this->filesystem->dumpFile($path, $content);
    }

    private function copy(string $origFile, string $destFile): void
    {
        $this->filesystem->copy($origFile, $destFile);
    }
}
