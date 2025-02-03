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
    ): void {
        $pluginDir = $this->kernelPluginLoader->getPluginDir($this->projectDir);

        if ($static) {
            $pluginDir .= '/../static-plugins';
        }

        if ($this->filesystem->exists($pluginDir . '/' . $pluginName)) {
            throw new Exception('Plugin already exists');
        }

        foreach ($additionalBundles as $additionalBundle) {
            $this->filesystem->mkdir($pluginDir . '/' . $pluginName . '/src/' . $additionalBundle);
            $sectionBundleClass = $this->templateRender->render(__DIR__ . '/../Resources/skeletons/AdditionalBundle.tpl.php', [
                'namespace' => $namespace,
                'additionalBundleName' => $additionalBundle,
            ]);

            $this->filesystem->dumpFile("$pluginDir/$pluginName/src/$additionalBundle/$pluginName$additionalBundle.php", $sectionBundleClass);

            $routes = $this->templateRender->render(__DIR__ . '/../Resources/skeletons/config/routes.xml.php', [
                'withStorefront' => ! $headless,
                'withAdmin' => 'Administration' === $additionalBundle,
            ]);

            $this->filesystem->dumpFile("$pluginDir/$pluginName/src/$additionalBundle/Resources/config/routes.xml", $routes);

            $services = $this->templateRender->render(__DIR__ . '/../Resources/skeletons/config/services.xml.php', [
                'namespace' => $namespace,
                'pluginName' => $pluginName,
                'additionalBundleName' => $additionalBundle,
            ]);

            $this->filesystem->dumpFile("$pluginDir/$pluginName/src/$additionalBundle/Resources/config/services.xml", $services);
        }

        $pluginClass = $this->templateRender->render(__DIR__ . '/../Resources/skeletons/PluginClass.tpl.php', [
            'namespace' => $namespace,
            'pluginName' => $pluginName,
            'additionalBundleName' => $additionalBundles,
        ]);

        $this->filesystem->dumpFile($pluginDir . '/' . $pluginName . '/src/' . $pluginName . '.php', $pluginClass);

        $composerJson = $this->templateRender->render(__DIR__ . '/../Resources/skeletons/config/composer.json.php', [
            'namespace' => $namespace,
            'withStorefront' => ! $headless,
            'pluginNameWithDash' => Str::camelCaseToDash($pluginName),
            'pluginName' => $pluginName,
            'shopwareVersion' => Autoload::getShopwareInstalledVersion(),
        ]);

        $this->filesystem->dumpFile($pluginDir . '/' . $pluginName . '/composer.json', $composerJson);

        $this->filesystem->copy(__DIR__ . '/../Resources/skeletons/config/.php-cs-fixer.dist.php', $pluginDir . '/' . $pluginName . '/.php-cs-fixer.dist.php');
        $this->filesystem->copy(__DIR__ . '/../Resources/skeletons/config/rector.php', $pluginDir . '/' . $pluginName . '/rector.php');
        $this->filesystem->copy(__DIR__ . '/../Resources/skeletons/config/phpstan.neon', $pluginDir . '/' . $pluginName . '/phpstan.neon');
        $this->filesystem->copy(__DIR__ . '/../Resources/skeletons/config/phpstan-baseline.neon', $pluginDir . '/' . $pluginName . '/phpstan-baseline.neon');
        $this->filesystem->copy(__DIR__ . '/../Resources/skeletons/config/phpunit.xml.dist', $pluginDir . '/' . $pluginName . '/phpunit.xml.dist');
    }
}
