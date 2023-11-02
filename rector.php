<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\FuncCall\IntvalToTypeCastRector;
use Rector\Config\RectorConfig;
use Rector\Php55\Rector\String_\StringClassNameToClassConstantRector;
use Rector\Php73\Rector\FuncCall\JsonThrowOnErrorRector;
use Rector\Php74\Rector\Closure\ClosureToArrowFunctionRector;
use Rector\Php74\Rector\LNumber\AddLiteralSeparatorToNumberRector;
use Rector\Php81\Rector\Array_\FirstClassCallableRector;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;

return static function (RectorConfig $rectorConfig): void {
    // Define paths to process
    $rectorConfig->paths([
        'src/',
        'tests/'
    ]);

    // import all kind of names
    $rectorConfig->importNames();

    // Define what rule sets will be applied
    $rectorConfig->import(SetList::DEAD_CODE);
    $rectorConfig->skip([
        StringClassNameToClassConstantRector::class,
        JsonThrowOnErrorRector::class,
        FirstClassCallableRector::class,
    ]);

    // define sets of rules
    $rectorConfig->sets([
        LevelSetList::UP_TO_PHP_81,
    ]);

    $rectorConfig->rules([
        IntvalToTypeCastRector::class,
        AddLiteralSeparatorToNumberRector::class,
        ClosureToArrowFunctionRector::class,
    ]);
};
