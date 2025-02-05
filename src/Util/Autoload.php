<?php

declare(strict_types=1);

namespace ShopwarePluginSkeletonGenerator\Util;

use Composer\InstalledVersions;

/**
 * @internal
 */
final class Autoload
{
    public static function extractClassName(string $fullyQualifiedName): string
    {
        // Find the last position of the backslash
        $lastSlashPos = strrpos($fullyQualifiedName, '\\');

        if (false === $lastSlashPos) {
            // If no backslash exists, it's just the class name (no namespace)
            return $fullyQualifiedName;
        }

        // Extract class name
        return substr($fullyQualifiedName, $lastSlashPos + 1);
    }

    public static function extractNamespace(string $fullyQualifiedName): string
    {
        // Find the last position of the backslash
        $lastSlashPos = strrpos($fullyQualifiedName, '\\');

        if (false === $lastSlashPos) {
            // If no backslash exists, it's just the class name (no namespace)
            return '';
        }

        // Extract namespace
        return substr($fullyQualifiedName, 0, $lastSlashPos);
    }

    public static function isValidNamespace(string $namespace): bool
    {
        // Check if namespace ends with a backslash
        if (str_ends_with($namespace, '\\')) {
            return false;
        }

        // Split the namespace by backslashes and validate each part
        $parts = explode('\\', $namespace);
        foreach ($parts as $part) {
            // Validate each segment with a regex
            if (1 !== preg_match('/^[A-Z_\x80-\xff][a-zA-Z0-9_\x80-\xff]*$/', $part)) {
                return false;
            }
        }

        // All checks passed; it's a valid namespace
        return true;
    }

    public static function getShopwareInstalledVersion(): string
    {
        return InstalledVersions::getVersion('shopware/core');
    }
}
