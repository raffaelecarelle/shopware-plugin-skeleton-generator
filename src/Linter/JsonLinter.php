<?php

declare(strict_types=1);

namespace ShopwarePluginSkeletonGenerator\Linter;

use Symfony\Component\Finder\Finder;

final class JsonLinter implements LinterInterface
{
    public function lint(array | string $templateFilePath): void
    {
        $finder = new Finder();
        $finder->files()->in($templateFilePath);
        $finder->name('*.json');

        foreach ($finder->getIterator() as $file) {
            $content = file_get_contents($file->getPathname());
            $content = json_encode(json_decode((string) $content), \JSON_PRETTY_PRINT | \JSON_UNESCAPED_SLASHES);
            file_put_contents($file->getPathname(), $content);
        }
    }
}
