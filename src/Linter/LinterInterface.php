<?php

declare(strict_types=1);

namespace ShopwarePluginSkeletonGenerator\Linter;

interface LinterInterface
{
    /**
     * @param string[]|string $templateFilePath
     */
    public function lint(array | string $templateFilePath): void;
}
