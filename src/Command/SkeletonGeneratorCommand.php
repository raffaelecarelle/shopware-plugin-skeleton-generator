<?php

declare(strict_types=1);

namespace ShopwarePluginSkeletonGenerator\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'plugin:skeleton:generate', description: 'Generate a new plugin skeleton')]
class SkeletonGeneratorCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->addArgument('pluginName', InputOption::VALUE_REQUIRED, 'The plugin name')
            ->addOption('static', 'S', InputOption::VALUE_OPTIONAL, 'Check if the plugin is static', false)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        return self::SUCCESS;
    }
}
