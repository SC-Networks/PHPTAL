<?php

$finder = \PhpCsFixer\Finder::create()
->in(
    [
        __DIR__ . '/src',
    ]
);

return (new \PhpCsFixer\Config())
->setRules([
    '@PSR2' => true,
    'array_syntax' => [
        'syntax' => 'short'
    ],
    'ordered_imports' => [
        'sort_algorithm' => 'alpha'
    ],
    'no_unused_imports' => true,
    'indentation_type' => true,
])
->setLineEnding("\n")
->setFinder($finder);
