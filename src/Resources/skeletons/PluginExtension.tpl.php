<? echo '<?php declare(strict_types=1)' ?>;

namespace <? echo $namespace ?>\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

class <? echo $pluginName ?>Extension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        <? foreach ($configs as $config) {
            echo "\$loader->load('{$config}.xml');";
        }?>
    }
}
