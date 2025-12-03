<?php

declare(strict_types=1);

namespace ShopwarePluginSkeletonGenerator\Linter;

use DOMDocument;
use League\Flysystem\FilesystemOperator;
use Override;
use Symfony\Component\Finder\Finder;

final readonly class XmlLinter implements LinterInterface
{
    public function __construct(
        private FilesystemOperator $filesystem,
        private string $projectDir,
    ) {}

    #[Override]
    public function lint(array | string $templateFilePath): void
    {
        $finder = new Finder();
        $finder->files()->in($templateFilePath);
        $finder->name('*.xml');

        foreach ($finder->getIterator() as $file) {
            $path = $this->getRelativePath($file->getPathname());
            $dom = new DOMDocument();
            $dom->preserveWhiteSpace = false;
            $dom->loadXML($this->filesystem->read($path));
            $dom->formatOutput = true;
            $this->filesystem->write($path, (string) $dom->saveXML());
        }
    }

    private function getRelativePath(string $path): string
    {
        return str_replace($this->projectDir . '/', '', $path);
    }
}
