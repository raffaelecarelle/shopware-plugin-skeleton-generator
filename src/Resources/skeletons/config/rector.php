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
