<?php

declare(strict_types=1);

namespace ShopwarePluginSkeletonGenerator\Tests\Util;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use ShopwarePluginSkeletonGenerator\Util\Str;

#[CoversClass(Str::class)]
class StrTest extends TestCase
{
    #[TestWith(['PascalCase', true])]
    #[TestWith(['pascalCase', false])]
    #[TestWith(['Pascalcase', true])]
    #[TestWith(['pascalcase', false])]
    #[TestWith(['Pascal Case', false])]
    #[TestWith(['Pascal', true])]
    #[TestWith(['pascal', false])]
    #[TestWith(['', false])]
    #[TestWith(['Pascal1Case', true])]
    #[TestWith(['PascalCase1', true])]
    #[TestWith(['1PascalCase', false])]
    public function testIsPascalCase(string $string, bool $expected): void
    {
        self::assertEquals($expected, Str::isPascalCase($string));
    }

    #[TestWith(['camelCase', 'camel-case'])]
    #[TestWith(['PascalCase', 'pascal-case'])]
    #[TestWith(['simpleTest', 'simple-test'])]
    #[TestWith(['simple', 'simple'])]
    #[TestWith(['simpleCaseTest', 'simple-case-test'])]
    public function testCamelCaseToDash(string $string, string $expected): void
    {
        self::assertEquals($expected, Str::camelCaseToDash($string));
    }
}
