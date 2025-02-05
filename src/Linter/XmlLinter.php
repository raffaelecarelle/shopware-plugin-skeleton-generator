<?php

declare(strict_types=1);

namespace ShopwarePluginSkeletonGenerator\Linter;

use DOMDocument;
use Override;
use Symfony\Component\Finder\Finder;

final class XmlLinter implements LinterInterface
{
    #[Override]
    public function lint(array | string $templateFilePath): void
    {
        $finder = new Finder();
        $finder->files()->in($templateFilePath);
        $finder->name('*.xml');

        foreach ($finder->getIterator() as $file) {
            $dom = new DOMDocument();
            $dom->preserveWhiteSpace = false;
            $dom->loadXML((string) file_get_contents($file->getPathname()));
            $dom->formatOutput = true;
            file_put_contents($file->getPathname(), $dom->saveXML());
        }
    }
}
