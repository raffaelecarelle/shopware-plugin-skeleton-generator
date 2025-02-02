<?php

declare(strict_types=1);

use Rector\Php82\Rector\Class_\ReadOnlyClassRector;
use Rector\Config\RectorConfig;
use Rector\PHPUnit\Set\PHPUnitSetList;
use Rector\TypeDeclaration\Rector\ClassMethod\ReturnNeverTypeRector;
use Frosh\Rector\Set\ShopwareSetList;

return RectorConfig::configure()
    ->withImportNames(
        importShortClasses: false
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
        ShopwareSetList::SHOPWARE_6_6_0
    ])
    ->withRules(
        [
            ReadOnlyClassRector::class
        ]
    );
