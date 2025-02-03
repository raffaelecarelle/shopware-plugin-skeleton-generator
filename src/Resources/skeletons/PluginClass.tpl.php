<?php echo '<?php declare(strict_types=1)'; ?>;

namespace <?php echo $namespace; ?>;

use Shopware\Core\Framework\Plugin;

class <?php echo $pluginName; ?> extends Plugin
{
    <?php if ([] !== $additionalBundles) { ?>
    #[\Override]
    public function getAdditionalBundles(AdditionalBundleParameters $parameters): array
    {
        <?php echo "return [\n"; ?>
            <?php foreach ($additionalBundles as $bundleName) {?>
            new<?php echo $bundleName; ?>(),
            <?php } ?>
        <?php echo '];'; ?>
    }
    <?php } ?>
}
