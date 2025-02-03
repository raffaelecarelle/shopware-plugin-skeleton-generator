<?php echo '<?php declare(strict_types=1)'; ?>;

namespace <?php echo $namespace; ?>;

use Shopware\Core\Framework\Plugin;

class <?php echo $pluginName; ?> extends Plugin
{
    <?php if ([] !== $additionalBundleName) { ?>
    #[\Override]
    public function getAdditionalBundles(AdditionalBundleParameters $parameters): array
    {
        <?php echo "return [\n"; ?>
            <?php foreach ($additionalBundleName as $bundleName) {?>
            new<?php echo $bundleName; ?>(),
            <?php } ?>
        <?php echo '];'; ?>
    }
    <?php } ?>
}
