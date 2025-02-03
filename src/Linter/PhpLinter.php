<?php

declare(strict_types=1);

namespace ShopwarePluginSkeletonGenerator\Linter;

use Override;
use RuntimeException;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;

final class PhpLinter implements LinterInterface
{
    private bool $needsPhpCmdPrefix = true;

    public function __construct(
        private ?string $phpCsFixerBinaryPath = null,
        private ?string $phpCsFixerConfigPath = null,
    ) {
        $this->setBinary();
        $this->setConfig();
    }

    #[Override]
    public function lint(array | string $templateFilePath): void
    {
        if (\is_string($templateFilePath)) {
            $templateFilePath = [$templateFilePath];
        }

        $isWindows = \defined('PHP_WINDOWS_VERSION_MAJOR');
        $ignoreEnv = $isWindows ? 'set PHP_CS_FIXER_IGNORE_ENV=1& ' : 'PHP_CS_FIXER_IGNORE_ENV=1 ';

        $cmdPrefix = $this->needsPhpCmdPrefix ? 'php ' : '';

        foreach ($templateFilePath as $filePath) {
            Process::fromShellCommandline(\sprintf(
                '%s%s%s --config=%s --using-cache=no fix %s',
                $ignoreEnv,
                $cmdPrefix,
                $this->phpCsFixerBinaryPath,
                $this->phpCsFixerConfigPath,
                $filePath,
            ))
                ->run()
            ;
        }
    }

    private function setBinary(): void
    {
        // Use Bundled PHP-CS-Fixer
        if (null === $this->phpCsFixerBinaryPath) {
            $this->phpCsFixerBinaryPath = \sprintf('%s/Resources/bin/php-cs-fixer.phar', __DIR__);

            return;
        }

        // Path to PHP-CS-Fixer provided
        if (is_file($this->phpCsFixerBinaryPath)) {
            return;
        }

        // PHP-CS-Fixer in the system path?
        if (null !== $path = (new ExecutableFinder())->find($this->phpCsFixerBinaryPath)) {
            $this->phpCsFixerBinaryPath = $path;

            $this->needsPhpCmdPrefix = false;

            return;
        }

        // PHP-CS-Fixer provided is not a file and is not in the system path.
        throw new RuntimeException(\sprintf('The MAKER_PHP_CS_FIXER_BINARY_PATH provided: %s does not exist.', $this->phpCsFixerBinaryPath));
    }

    private function setConfig(): void
    {
        // No config provided, but there is a dist config file in the project dir
        if (null === $this->phpCsFixerConfigPath && file_exists($defaultConfigPath = '.php-cs-fixer.dist.php')) {
            $this->phpCsFixerConfigPath = $defaultConfigPath;

            return;
        }

        // No config provided and no project dist config - use our config
        if (null === $this->phpCsFixerConfigPath) {
            $this->phpCsFixerConfigPath = \sprintf('%s/config/php-cs-fixer.config.php', \dirname(__DIR__, 2));

            return;
        }

        // The config path provided doesn't exist...
        if ( ! file_exists($this->phpCsFixerConfigPath)) {
            throw new RuntimeException(\sprintf('The PHP_CS_FIXER_CONFIG_PATH provided: %s does not exist.', $this->phpCsFixerConfigPath));
        }
    }
}
