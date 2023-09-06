<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\Class_\InlineConstructorDefaultToPropertyRector;
use Rector\CodeQuality\Rector\Empty_\SimplifyEmptyCheckOnEmptyArrayRector;
use Rector\CodingStyle\Rector\ClassMethod\UnSpreadOperatorRector;
use Rector\Config\RectorConfig;
use Rector\Naming\Rector\Assign\RenameVariableToMatchMethodCallReturnTypeRector;
use Rector\Naming\Rector\Class_\RenamePropertyToMatchTypeRector;
use Rector\Naming\Rector\ClassMethod\RenameParamToMatchTypeRector;
use Rector\Naming\Rector\ClassMethod\RenameVariableToMatchNewTypeRector;
use Rector\PHPUnit\Set\PHPUnitLevelSetList;
use Rector\PHPUnit\Set\PHPUnitSetList;
use Rector\Privatization\Rector\Class_\FinalizeClassesWithoutChildrenRector;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;
use RectorLaravel\Set\LaravelSetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([
        __DIR__ . '/app',
        __DIR__ . '/bootstrap',
        __DIR__ . '/config',
        __DIR__ . '/public',
        __DIR__ . '/resources',
        __DIR__ . '/routes',
        __DIR__ . '/tests',
    ]);

    // register a single rule
    $rectorConfig->rule(InlineConstructorDefaultToPropertyRector::class);

    // define sets of rules
    $rectorConfig->sets([
        LevelSetList::UP_TO_PHP_82,
        LaravelSetList::LARAVEL_LEGACY_FACTORIES_TO_CLASSES,
        LaravelSetList::LARAVEL_100,
        PHPUnitLevelSetList::UP_TO_PHPUNIT_100,
        PHPUnitSetList::PHPUNIT_CODE_QUALITY,
        SetList::CODE_QUALITY,
        SetList::CODING_STYLE,
        SetList::DEAD_CODE,
        SetList::EARLY_RETURN,
        SetList::NAMING,
        SetList::PRIVATIZATION,
        SetList::TYPE_DECLARATION,
    ]);

    // Import FQN to use statements:
    $rectorConfig->importNames();

    $rectorConfig->skip([
        // don't finalise classes, yet, maybe when the app has been completed
        FinalizeClassesWithoutChildrenRector::class,
        SimplifyEmptyCheckOnEmptyArrayRector::class,

        UnSpreadOperatorRector::class,

        // renames are not always correct, don't change on existing project
        // RenameVariableToMatchNewTypeRector::class,
        // RenamePropertyToMatchTypeRector::class,
        // RenameParamToMatchTypeRector::class,
         RenameVariableToMatchMethodCallReturnTypeRector::class,

        // ignore the cache directory as this is auto generated by Laravel
        __DIR__ . '/bootstrap/cache/*',

        // leave the Laravel providers as they are
        __DIR__ . '/app/Providers/*',

        // Leave the Laravel and vendor configs as they are
        __DIR__ . '/config/*',

        // Leave the Laravel Kernel
        __DIR__ . '/app/Http/Kernel.php',

        // Leave the Laravel Exception Handler
        __DIR__ . '/app/Exceptions/Handler.php',

        // Leave the Laravel Middleware
        __DIR__ . '/app/Http/Middleware/*',
    ]);
};
