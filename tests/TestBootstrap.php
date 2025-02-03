<?php

declare(strict_types=1);

use Shopware\Core\TestBootstrapper;

$loader = (new TestBootstrapper())
    ->addCallingPlugin()
    ->addActivePlugins('PluginSkeletonGenerator')
    ->setForceInstallPlugins(false)
    ->bootstrap()
    ->getClassLoader();

$loader->addPsr4('ShopwarePluginSkeletonGenerator\PluginSkeletonGenerator\Tests\\', __DIR__);
