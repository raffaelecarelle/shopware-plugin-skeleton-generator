# Shopware Plugin Skeleton Generator

The Shopware Plugin Skeleton Generator is a powerful tool designed to streamline the development process for Shopware plugins. It quickly generates a clean, standardized skeleton structure for your plugin, providing essential files, directories, and configurations. This helps developers save time, maintain best practices, and focus on building custom functionality for Shopware-based eCommerce solutions. Whether you're a beginner or an experienced Shopware developer, this generator is an excellent starting point for your plugin projects.

## Install and Activate

```console
$ composer require --dev raffaelecarelle/shopware-plugin-skeleton-generator
$ bin/console plugin:refresh
$ bin/console plugin:install SkeletonGenerator --activate
```

## Run Command

### Generate new Shopware plugin

```console
$ bin/console plugin:skeleton:generate Full\\\Qualified\\\Name\\\Plugin
```

### Generate new Static Shopware plugin

```console
$ bin/console plugin:skeleton:generate Full\\\Qualified\\\Name\\\Plugin --static
```

### Generate new Headless Shopware plugin (without Storefront module)

```console
$ bin/console plugin:skeleton:generate Full\\\Qualified\\\Name\\\Plugin --headless
```

### Add some additional bundle

```console
$ bin/console plugin:skeleton:generate Full\\\Qualified\\\Name\\\Plugin --additionalBundle=Core --additionalBundle=Administration
```

### Update existing plugin with new additional bundle

```console
$ bin/console plugin:skeleton:generate Full\\\Qualified\\\Name\\\Plugin --append --additionalBundle=Core --additionalBundle=Administration
```


