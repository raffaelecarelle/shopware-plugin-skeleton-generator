<?php

declare(strict_types=1);

namespace ShopwarePluginSkeletonGenerator\Render;

use Override;

class SimplePhpTemplateRender implements TemplateRenderInterface
{
    #[Override]
    public function render(string $templatePath, array $parameters): string
    {
        ob_start();
        extract($parameters, \EXTR_SKIP);
        include $templatePath;

        return ob_get_clean() ?: '';
    }
}
