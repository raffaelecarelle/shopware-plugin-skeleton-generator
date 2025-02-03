<?php

declare(strict_types=1);

namespace ShopwarePluginSkeletonGenerator\Render;

interface TemplateRenderInterface
{
    /**
     * @param array<string, mixed> $parameters
     */
    public function render(string $templatePath, array $parameters): string;
}
