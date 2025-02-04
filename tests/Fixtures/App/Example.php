<?php

namespace App;

use Shopware\Core\Framework\Bundle;
use Shopware\Core\Framework\Parameter\AdditionalBundleParameters;
use Shopware\Core\Framework\Plugin;

class Example extends Plugin
{
    public function getAdditionalBundles(AdditionalBundleParameters $parameters): array
    {
        return [
            new TestBundle(),
        ];
    }
}

class TestBundle extends Bundle
{

}
