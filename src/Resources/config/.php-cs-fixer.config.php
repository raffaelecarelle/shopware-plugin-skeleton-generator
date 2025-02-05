<?php

declare(strict_types=1);

use PhpCsFixer\Config;
use PhpCsFixer\Finder;
use PhpCsFixer\Runner\Parallel\ParallelConfigFactory;

$finder = Finder::create()
    ->in(__DIR__ . '/src')
    ->in(__DIR__ . '/tests')
    ->append([__DIR__ . '/.php-cs-fixer.php']);

return (new Config())
    ->setCacheFile('.php_cs.cache')
    ->setRiskyAllowed(true)
    ->setParallelConfig(ParallelConfigFactory::detect())
    ->setRules(
        [
            '@PSR12' => true,
            '@PSR12:risky' => true,
            '@Symfony' => true,
            '@Symfony:risky' => true
        ],
    )
    ->setFinder($finder);
