<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\FuncCall\IntvalToTypeCastRector;
use Rector\Config\RectorConfig;
use Rector\Php55\Rector\String_\StringClassNameToClassConstantRector;
use Rector\Php73\Rector\FuncCall\JsonThrowOnErrorRector;
use Rector\Php74\Rector\Closure\ClosureToArrowFunctionRector;
use Rector\Php74\Rector\LNumber\AddLiteralSeparatorToNumberRector;
use Rector\Php80\Rector\FunctionLike\UnionTypesRector;
use Rector\PostRector\Rector\NameImportingPostRector;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;

return static function (RectorConfig $rector_config): void {
    // Path to phpstan with extensions, that PHPSTan in Rector uses to determine types
    $rector_config->phpstanConfig(__DIR__ . '/phpstan.neon');

    // Define paths to process
    $rector_config->paths([
        'src/',
        'tests/'
    ]);

    // import all kind of names
    $rector_config->importNames();

    // Define what rule sets will be applied
    $rector_config->import(SetList::DEAD_CODE);
    $rector_config->skip([
        StringClassNameToClassConstantRector::class,
        UnionTypesRector::class,
        JsonThrowOnErrorRector::class,
    ]);

    // define sets of rules
    $rector_config->sets([
        LevelSetList::UP_TO_PHP_81,
    ]);

    $services = $rector_config->services();

    $services->set(IntvalToTypeCastRector::class);
    $services->set(AddLiteralSeparatorToNumberRector::class);
    $services->set(ClosureToArrowFunctionRector::class);
    $services->set(NameImportingPostRector::class);
};
