<?php

declare(strict_types=1);

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__)
    ->notPath('config.php')
    ->append([
        __FILE__,
        __DIR__.'/bin/auth',
        __DIR__.'/bin/cleanup',
    ]);

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setRules([
        '@PSR12' => true,
        '@PSR12:risky' => true,
        'array_syntax' => ['syntax' => 'short'],
        'list_syntax' => ['syntax' => 'short'],
        'declare_strict_types' => true,
        'strict_comparison' => true,
        'strict_param' => true,
        'php_unit_strict' => true,
        'global_namespace_import' => [
            'import_classes' => true,
            'import_constants' => true,
            'import_functions' => true,
        ],
    ])
    ->setCacheFile(__DIR__.'/.build/.php_cs.cache')
    ->setFinder($finder);
