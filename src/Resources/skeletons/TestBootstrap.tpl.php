<?php echo '<?php'; ?>

declare(strict_types=1);

use Shopware\Core\TestBootstrapper;

$loader = (new TestBootstrapper())
    ->addCallingPlugin()
    ->addActivePlugins('<?php echo $pluginName; ?>')
    ->setForceInstallPlugins(true)
    ->bootstrap()
    ->getClassLoader();

$loader->addPsr4('<?php echo $namespace; ?>\\Tests\\', __DIR__);
