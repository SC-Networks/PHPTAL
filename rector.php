<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Php55\Rector\String_\StringClassNameToClassConstantRector;
use Rector\Php83\Rector\ClassMethod\AddOverrideAttributeToOverriddenMethodsRector;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/src',
        __DIR__ . '/tests'
    ])
    ->withImportNames()
    ->withSkip([
        StringClassNameToClassConstantRector::class,
        AddOverrideAttributeToOverriddenMethodsRector::class,
    ])
    ->withPhpSets(php83: true)
    ->withPreparedSets(deadCode: true)
;
