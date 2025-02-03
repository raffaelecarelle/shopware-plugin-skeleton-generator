<?php

declare(strict_types=1);

namespace ShopwarePluginSkeletonGenerator\Tests\Util;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use ShopwarePluginSkeletonGenerator\Util\Autoload;

#[CoversClass(Autoload::class)]
class AutoloadTest extends TestCase
{
    #[TestWith([Autoload::class, 'Autoload'])]
    #[TestWith(['Test', 'Test'])]
    #[TestWith(['\\Test', 'Test'])]
    #[TestWith([self::class, 'AutoloadTest'])]
    public function testExtractClassName(string $fullyQualifiedName, string $expectedClassName): void
    {
        $actualClassName = Autoload::extractClassName($fullyQualifiedName);
        self::assertEquals($expectedClassName, $actualClassName);
    }

    #[TestWith([Autoload::class, 'ShopwarePluginSkeletonGenerator\\Util'])]
    #[TestWith(['Test', ''])]
    #[TestWith(['\\Test', ''])]
    #[TestWith([self::class, 'ShopwarePluginSkeletonGenerator\\Tests\\Util'])]
    public function testExtractNamespace(string $fullyQualifiedName, string $expectedNamespace): void
    {
        $actualNamespace = Autoload::extractNamespace($fullyQualifiedName);
        self::assertEquals($expectedNamespace, $actualNamespace);
    }

    #[TestWith(['ShopwarePluginSkeletonGenerator\\Util', true])]
    #[TestWith(['Test', true])]
    #[TestWith(['', false])]
    #[TestWith(['Invalid\\Namespace\\', false])]
    public function testIsValidNamespace(string $namespace, bool $expectedResult): void
    {
        $actualResult = Autoload::isValidNamespace($namespace);
        self::assertEquals($expectedResult, $actualResult);
    }

    public function testGetShopwareInstalledVersion(): void
    {
        $actualVersion = Autoload::getShopwareInstalledVersion();
        self::assertEquals('6.6.9.0', $actualVersion);
    }
}
