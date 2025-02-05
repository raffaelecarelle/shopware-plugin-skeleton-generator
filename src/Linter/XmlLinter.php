<?php

declare(strict_types=1);

namespace ShopwarePluginSkeletonGenerator\Linter;

use Override;
use RuntimeException;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;

final class XmlLinter implements LinterInterface
{
    private bool $needsPhpCmdPrefix = true;

    #[Override]
    public function lint(array | string $templateFilePath): void
    {
        $finder = new Finder();
        $finder->files()->in($templateFilePath);
        $finder->name('*.xml');

        foreach ($finder->getIterator() as $file) {
            $dom = new \DOMDocument();
            $dom->preserveWhiteSpace = FALSE;
            $dom->loadXML(file_get_contents($file->getPathname()));
            $dom->formatOutput = true;
            file_put_contents($file->getPathname(), $dom->saveXML());
        }
    }
}
