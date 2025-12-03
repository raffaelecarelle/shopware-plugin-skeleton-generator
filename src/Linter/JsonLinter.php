<?php

declare(strict_types=1);

namespace ShopwarePluginSkeletonGenerator\Linter;

use League\Flysystem\FilesystemOperator;
use Symfony\Component\Finder\Finder;

final readonly class JsonLinter implements LinterInterface
{
    public function __construct(
        private FilesystemOperator $filesystem,
        private string $projectDir,
    ) {}

    public function lint(array | string $templateFilePath): void
    {
        $finder = new Finder();
        $finder->files()->in($templateFilePath);
        $finder->name('*.json');

        foreach ($finder->getIterator() as $file) {
            $path = $this->getRelativePath($file->getPathname());
            $content = $this->filesystem->read($path);
            $content = json_encode(json_decode($content), \JSON_PRETTY_PRINT | \JSON_UNESCAPED_SLASHES);
            $this->filesystem->write($path, (string) $content);
        }
    }

    private function getRelativePath(string $path): string
    {
        return str_replace($this->projectDir . '/', '', $path);
    }
}
