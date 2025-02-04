<?php

declare(strict_types=1);

namespace ShopwarePluginSkeletonGenerator\Generator;

use Exception;
use Laminas\Code\Generator\ClassGenerator;
use Laminas\Code\Generator\FileGenerator;
use Laminas\Code\Generator\MethodGenerator;
use Laminas\Code\Reflection\ClassReflection;
use Laminas\Code\Reflection\MethodReflection;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;
use Shopware\Core\Framework\Bundle;
use Shopware\Core\Framework\Parameter\AdditionalBundleParameters;
use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\KernelPluginLoader\KernelPluginLoader;
use ShopwarePluginSkeletonGenerator\Render\TemplateRenderInterface;
use ShopwarePluginSkeletonGenerator\Util\Autoload;
use ShopwarePluginSkeletonGenerator\Util\CodeManipulator;
use ShopwarePluginSkeletonGenerator\Util\Str;
use Symfony\Component\Filesystem\Filesystem;

class Generator
{
    public function __construct(
        private readonly KernelPluginLoader      $kernelPluginLoader,
        private readonly TemplateRenderInterface $templateRender,
        private readonly Filesystem              $filesystem,
        private readonly string                  $projectDir,
    )
    {
    }

    /**
     * @param string[] $additionalBundles
     */
    public function generate(
        string $namespace,
        string $pluginName,
        array  $additionalBundles,
        bool   $headless = false,
        bool   $static = false,
        bool   $append = false,
    ): string
    {
        $pluginDir = $this->kernelPluginLoader->getPluginDir($this->projectDir);

        if ($static) {
            $pluginDir = \dirname($pluginDir) . '/static-plugins';
        }

        if (!$append && $this->filesystem->exists($pluginDir . '/' . $pluginName)) {
            throw new Exception(\sprintf('Plugin "%s" already exists.', $pluginName));
        }

        if ($append && !$this->filesystem->exists($pluginDir . '/' . $pluginName)) {
            throw new Exception(\sprintf('Plugin "%s" does not exist. Cannot append bundles!', $pluginName));
        }

        if (!$append) {
            $pluginClassContent = $this->templateRender->render(__DIR__ . '/../Resources/skeletons/PluginClass.tpl.php', [
                'namespace' => $namespace,
                'pluginName' => $pluginName,
                'additionalBundles' => $additionalBundles,
            ]);

            $this->dump($pluginDir . '/' . $pluginName . '/src/' . $pluginName . '.php', $pluginClassContent);

            $composerJsonContent = $this->templateRender->render(__DIR__ . '/../Resources/skeletons/config/composer.json.php', [
                'namespace' => str_replace('\\', '\\\\', $namespace),
                'withStorefront' => !$headless,
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
                'namespace' => str_replace('\\', '\\\\', $namespace),
                'pluginName' => $pluginName,
            ]);

            $this->dump($pluginDir . '/' . $pluginName . '/tests/TestBootstrap.php', $testBootstrapContent);

            if ([] === $additionalBundles) {
                $routesContent = $this->templateRender->render(__DIR__ . '/../Resources/skeletons/config/routes.xml.php');

                $this->dump("$pluginDir/$pluginName/src/Resources/config/routes.xml", $routesContent);

                $servicesContent = $this->templateRender->render(__DIR__ . '/../Resources/skeletons/config/services.xml.php', [
                    'namespace' => $namespace,
                    'pluginName' => $pluginName,
                    'additionalBundleName' => false,
                ]);

                $this->dump("$pluginDir/$pluginName/src/Resources/config/services.xml", $servicesContent);
                $this->dump("$pluginDir/$pluginName/src/Controller/.gitkeep", '');
                $this->dump("$pluginDir/$pluginName/src/Route/.gitkeep", '');
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

            $this->dump("$pluginDir/$pluginName/src/$additionalBundleName/$pluginName$additionalBundleName.php", $sectionBundleClassContent);

            $routesContent = $this->templateRender->render(__DIR__ . '/../Resources/skeletons/config/routes.xml.php');

            $this->dump("$pluginDir/$pluginName/src/$additionalBundleName/Resources/config/routes.xml", $routesContent);

            $servicesContent = $this->templateRender->render(__DIR__ . '/../Resources/skeletons/config/services.xml.php', [
                'namespace' => $namespace . '\\' . $pluginName . $additionalBundleName,
                'pluginName' => $pluginName . $additionalBundleName,
                'additionalBundleName' => $additionalBundleName,
            ]);

            $this->dump("$pluginDir/$pluginName/src/$additionalBundleName/Resources/config/services.xml", $servicesContent);
            $this->dump("$pluginDir/$pluginName/src/$additionalBundleName/Controller/.gitkeep", '');
            $this->dump("$pluginDir/$pluginName/src/$additionalBundleName/Route/.gitkeep", '');
        }

        $bundles = $this->getExistentAdditionalBundles($namespace . '\\' . $pluginName);

        $additionalBundles = array_unique($additionalBundles + $bundles);

        $this->addAdditionalBundlesToPluginClass($namespace . '\\' . $pluginName, $additionalBundles);
    }

    private function dump(string $path, string $content): void
    {
        $this->filesystem->dumpFile($path, $content);
    }

    private function copy(string $origFile, string $destFile): void
    {
        $this->filesystem->copy($origFile, $destFile);
    }

    private function addAdditionalBundlesToPluginClass(string $existentPluginClass, array $bundles): void
    {
        $classRef = new ClassReflection($existentPluginClass);
        $class = ClassGenerator::fromReflection($classRef);
        $class->addUse(Plugin::class);
        $class->addUse(AdditionalBundleParameters::class);

        $method = new MethodGenerator();

        $body = '';
        if ($class->hasMethod('getAdditionalBundles')) {
            $method = $class->getMethod('getAdditionalBundles');
            $body = $method->getBody();
            $class->removeMethod('getAdditionalBundles');
        }
        $additionalBundlesBody = '';
        foreach ($bundles as $additionalBundle) {
            $class->addUse($additionalBundle);
            $additionalBundlesBody .= "\n    new " . Autoload::extractClassName($additionalBundle) . '(),';
        }

        if (!str_contains($body, 'return')) {
            $body .= 'return [';
            $body .= "\n" . $additionalBundlesBody;
            $body .= "\n];";
        } else {
            $body = str_replace("];", <<<EOD
                    ,$additionalBundlesBody \n ];\n
                EOD
                , $body);
            dump($body);
        }

//        $method->setDocBlock("/**\n* Method auto-generated by PluginSkeletonGenerator.\n*/");
        $method->setBody($body);

        $class->addMethodFromGenerator($method);

        $file = new FileGenerator();
        $file->setClass($class);
        $this->dump($classRef->getFileName(), $file->generate());
    }

    private function getExistentAdditionalBundles(string $existentPluginClass)
    {
        $classRef = new ClassReflection($existentPluginClass);
        $addRef = new ClassReflection(AdditionalBundleParameters::class);
        /** @var Plugin $instance */
        $instance = $classRef->newInstanceWithoutConstructor();
        $bundles = $instance->getAdditionalBundles($addRef->newInstanceWithoutConstructor());

        return array_map(fn(Bundle $bundle) => $bundle->getNamespace() . '\\'. $bundle->getName(), $bundles);
    }
}
