<?php

declare(strict_types=1);

namespace ShopwarePluginSkeletonGenerator\Command;

use Exception;
use ShopwarePluginSkeletonGenerator\Generator\Generator;
use ShopwarePluginSkeletonGenerator\Linter\LinterInterface;
use ShopwarePluginSkeletonGenerator\Util\Autoload;
use ShopwarePluginSkeletonGenerator\Util\Str;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'plugin:skeleton:generate', description: 'Generate a new plugin skeleton')]
class PluginSkeletonGenerateCommand extends Command
{
    public function __construct(
        private readonly Generator $generator,
        private readonly LinterInterface $linter,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('fullyQualifiedPluginName', InputOption::VALUE_REQUIRED, 'The FQN plugin name (ex. Valid\\Namespace\\PluginName)')
            ->addOption('static', 's', InputOption::VALUE_OPTIONAL, 'Check if the plugin is static', false)
            ->addOption('headless', 'H', InputOption::VALUE_OPTIONAL, 'Check if the plugin is compatible for headless project (without Storefront module)', false)
            ->addOption('additionalBundle', 'ab', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Create an additional bundle for section like Storefront, Administration, Core, Elasticsearch ecc.', [])
            ->addOption('append', null, InputOption::VALUE_OPTIONAL, 'Update an existing bundle appending one or more additional bundle ("additionalBundle" option is mandatory in this case)', false)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $fqpn = $input->getArgument('fullyQualifiedPluginName');
        $static = (bool) $input->getOption('static');
        $headless = (bool) $input->getOption('headless');
        $additionalBundle = $input->getOption('additionalBundle') ?? [];
        $append = (bool) $input->getOption('append');

        if (null === $fqpn || ! Autoload::isValidNamespace($fqpn)) {
            $output->writeln('<error>The plugin name (FQN) must be in a valid namespace (ex. Valid\\\Namespace\\\PluginName)</error>');

            return self::FAILURE;
        }

        if ($append && [] === $additionalBundle) {
            $output->writeln('<error>The additionalBundle option is mandatory with --appen option</error>');

            return self::FAILURE;
        }

        foreach ($additionalBundle as $bundle) {
            if (false === Str::isPascalCase($bundle)) {
                $output->writeln('<error>The additional bundle names must be in PascalCase</error>');

                return self::FAILURE;
            }
        }

        $pluginName = Autoload::extractClassName($fqpn);
        $namespace = Autoload::extractNamespace($fqpn);

        if ( ! Str::isPascalCase($pluginName)) {
            $output->writeln('<error>The plugin name must be in PascalCase</error>');

            return self::FAILURE;
        }

        try {
            $pluginPath = $this->generator->generate(
                $namespace,
                $pluginName,
                $additionalBundle,
                $headless,
                $static,
                $append,
            );

            $this->linter->lint($pluginPath);
        } catch (Exception $e) {
            $output->writeln("<error>{$e->getMessage()}</error>");

            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
