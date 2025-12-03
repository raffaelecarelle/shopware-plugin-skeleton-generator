# Shopware Plugin Skeleton Generator

The Shopware Plugin Skeleton Generator is a powerful tool designed to streamline the development process for Shopware plugins. It quickly generates a clean, standardized skeleton structure for your plugin, providing essential files, directories, and configurations. This helps developers save time, maintain best practices, and focus on building custom functionality for Shopware-based eCommerce solutions. Whether you're a beginner or an experienced Shopware developer, this generator is an excellent starting point for your plugin projects.

## Installation

This tool is intended to be used as a development dependency in your Shopware project.

1.  **Require with Composer:**
    ```bash
    composer require --dev raffaelecarelle/shopware-plugin-skeleton-generator
    ```

2.  **Refresh, Install and Activate the plugin:**
    ```bash
    bin/console plugin:refresh
    bin/console plugin:install ShopwarePluginSkeletonGenerator --activate
    ```

## Usage

### Generate a Plugin

The main command is `plugin:skeleton:generate`. It takes the fully qualified plugin name as an argument and has several options to customize the generated skeleton.

**Arguments:**

*   `fullyQualifiedPluginName`: (Required) The fully qualified class name for your plugin (e.g., `MyVendor\MyPlugin`).

**Options:**

*   `--static`: Generate a static plugin.
*   `--headless`: Generate a plugin compatible with headless projects (omits the Storefront module).
*   `--additionalBundle=<name>`: Create an additional bundle for specific sections like `Storefront`, `Administration`, `Core`, etc. Can be used multiple times.
*   `--append`: Update an existing plugin by adding one or more bundles specified with `--additionalBundle`.
*   `--config`: Generate a `config.xml` file for the plugin.

#### Examples

**Generate a new basic plugin:**
```bash
bin/console plugin:skeleton:generate MyVendor\\MyAwesomePlugin
```

**Generate a new static plugin:**
```bash
bin/console plugin:skeleton:generate MyVendor\\MyStaticPlugin --static
```

**Generate a new headless plugin:**
```bash
bin/console plugin:skeleton:generate MyVendor\\MyHeadlessPlugin --headless
```

**Generate a plugin with additional bundles:**
```bash
bin/console plugin:skeleton:generate MyVendor\\MyMultiBundlePlugin --additionalBundle=Core --additionalBundle=Administration
```

**Add new bundles to an existing plugin:**
```bash
bin/console plugin:skeleton:generate MyVendor\\MyMultiBundlePlugin --append --additionalBundle=Elasticsearch
```

**Generate a plugin with a configuration file:**
```bash
bin/console plugin:skeleton:generate MyVendor\\MyConfigurablePlugin --config
```

## Development Commands

This project uses Docker and a `Makefile` to simplify the development workflow.

*   **`make setup`**: Builds the Docker containers and installs Composer dependencies. Run this first.
*   **`make start`**: Starts the Docker containers in the background.
*   **`make sh`**: Opens a shell (`zsh`) inside the PHP container.
*   **`make composer-update`**: Runs `composer update` inside the container.
*   **`make pre-commit`**: Runs all quality checks: Rector, CS-Fixer, PHPStan, and PHPUnit.
*   **`make rector`**: Runs Rector to apply automated code refactoring.
*   **`make cs-fix`**: Runs PHP-CS-Fixer to fix coding standards.
*   **`make phpstan`**: Runs PHPStan for static analysis.
*   **`make tests`**: Runs the PHPUnit test suite.
