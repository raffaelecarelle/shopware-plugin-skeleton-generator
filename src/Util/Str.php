<?php

declare(strict_types=1);

namespace ShopwarePluginSkeletonGenerator\Util;

/**
 * @internal
 */
final class Str
{
    public static function isPascalCase(string $string): bool
    {
        return 1 === preg_match('/^[A-Z][a-z0-9]*(?:[A-Z][a-z0-9]*)*$/', $string);
    }

    public static function camelCaseToDash(string $string): string
    {
        // Replace uppercase letters with a dash followed by the lowercase version
        $output = preg_replace('/([a-z])([A-Z])/', '$1-$2', $string);

        // Convert the entire string to lowercase
        return strtolower((string) $output);
    }
}
