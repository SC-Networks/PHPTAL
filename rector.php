<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Php55\Rector\String_\StringClassNameToClassConstantRector;
use Rector\Php74\Rector\Closure\ClosureToArrowFunctionRector;
use Rector\Php74\Rector\LNumber\AddLiteralSeparatorToNumberRector;
use Rector\Php80\Rector\FunctionLike\MixedTypeRector;
use Rector\Php81\Rector\Array_\FirstClassCallableRector;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/src',
        __DIR__ . '/tests'
    ])
    ->withImportNames()
    ->withSkip([
        StringClassNameToClassConstantRector::class,
        FirstClassCallableRector::class,
        MixedTypeRector::class,
    ])
    ->withPhpSets(php81: true)
    ->withPreparedSets(deadCode: true)
    ->withRules([
        AddLiteralSeparatorToNumberRector::class,
        ClosureToArrowFunctionRector::class,
    ]);
