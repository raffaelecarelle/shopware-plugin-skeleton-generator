<?php

declare(strict_types=1);

namespace App;

use Override;
use Shopware\Core\Framework\Bundle;
use Shopware\Core\Framework\Parameter\AdditionalBundleParameters;
use Shopware\Core\Framework\Plugin;

class Example extends Plugin
{
    #[Override]
    public function getAdditionalBundles(AdditionalBundleParameters $parameters): array
    {
        return [
            new TestBundle(),
        ];
    }
}

class TestBundle extends Bundle {}
