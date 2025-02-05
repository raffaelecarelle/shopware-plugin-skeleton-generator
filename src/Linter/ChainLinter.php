<?php

declare(strict_types=1);

namespace ShopwarePluginSkeletonGenerator\Linter;

/**
 * @internal
 */
final readonly class ChainLinter
{
    /**
     * @param LinterInterface[] $linters
     */
    public function __construct(
        private iterable $linters,
    ) {}

    /**
     * @return LinterInterface[]
     */
    public function getLinters(): iterable
    {
        return $this->linters;
    }
}
