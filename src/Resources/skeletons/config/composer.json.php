{
    "name": "<?php echo $pluginNameWithDash; ?>/<?php echo $pluginNameWithDash; ?>",
    "description": "",
    "type": "shopware-platform-plugin",
    "version": "1.0.0",
    "license": "MIT",
    "require": {
        "shopware/core": "~<?php echo $shopwareVersion; ?>",
        "shopware/administration": "~<?php echo $shopwareVersion; ?>",
        <?php if ($withStorefront) { ?>
        "shopware/storefront": "~<?php echo $shopwareVersion; ?>"
        <?php } ?>
    },
    "require-dev": {
        "friendsofphp/php-cs-fixer": "^3.64",
        "frosh/shopware-rector": "^0.5",
        "phpstan/extension-installer": "^1.4",
        "phpstan/phpstan-deprecation-rules": "^2.0.1",
        "phpstan/phpstan-phpunit": "^2.0.4",
        "phpstan/phpstan-strict-rules": "^2.0.3",
        "phpunit/phpunit": "^11.4.2",
        "shopwarelabs/phpstan-shopware": "^0.1.3"
    },
    "autoload": {
        "psr-4": {
            "<?php echo $namespace; ?>\\": "src/"
        }
    },
    "autoload-dev": {
        "psr-4": {
            "<?php echo $namespace; ?>\\Tests\\": "tests/"
        }
    },
    "config": {
        "sort-packages": true,
        "allow-plugins": {
            "phpstan/extension-installer": true,
            "symfony/runtime": true
        }
    },
    "extra": {
        "shopware-plugin-class": "<?php echo $namespace; ?>\\<?php echo $pluginName; ?>",
        "plugin-icon": "src/Resources/config/plugin-icon.png",
        "copyright": "(c) by YourCompany",
        "label": {
            "de-DE": "de label",
            "en-GB": "en label"
        },
        "description": {
            "de-DE": "de description",
            "en-GB": "en description"
        },
        "manufacturerLink": {
            "de-DE": "https://www.yoursite.it/",
            "en-GB": "https://www.yoursite.it/"
        },
        "supportLink": {
            "de-DE": "https://www.yoursite.it/support/",
            "en-GB": "https://www.yoursite.it/support/",
            "es-ES": "https://www.yoursite.it/support/",
            "it-IT": "https://www.yoursite.it/support/"
        }
    }
}
