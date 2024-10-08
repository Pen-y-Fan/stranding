<?php

declare(strict_types=1);
use PHP_CodeSniffer\Standards\Generic\Sniffs\CodeAnalysis\AssignmentInConditionSniff;
use PhpCsFixer\Fixer\ArrayNotation\ArraySyntaxFixer;
use PhpCsFixer\Fixer\Import\NoUnusedImportsFixer;
use PhpCsFixer\Fixer\Operator\BinaryOperatorSpacesFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocNoEmptyReturnFixer;
use PhpCsFixer\Fixer\PhpUnit\PhpUnitMethodCasingFixer;
use PhpCsFixer\Fixer\Strict\DeclareStrictTypesFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;
use Symplify\EasyCodingStandard\ValueObject\Set\SetList;

// composer require --dev symplify/easy-coding-standard
// vendor/bin/ecs init

// Add to composer scripts:
//      "check-cs": "ecs check --ansi",
//      "fix-cs": "ecs check --fix --ansi",
return static function (ECSConfig $ecsConfig): void {
    // alternative to CLI arguments, easier to maintain and extend

    // Laravel app setup
    $ecsConfig->paths([
        __DIR__ . '/app',
        //  __DIR__ . '/src',
        __DIR__ . '/bootstrap',
        __DIR__ . '/config',
        __DIR__ . '/database',
        __DIR__ . '/public',
        __DIR__ . '/resources',
        __DIR__ . '/routes',
        __DIR__ . '/tests',
        __DIR__ . '/ecs.php',  // check this file too!
    ]);

    $ecsConfig->skip([

        // Ignore: Variable assignment found within a condition in index.php, it's not a conditional!
        AssignmentInConditionSniff::class => [
            __DIR__ . '/public/index.php',
        ],

        // I don't want to remove unused imports, at the beginning of the app, then the app is close to
        // production these unused imports can be removed and files cleaned up.
        NoUnusedImportsFixer::class,

        // I can remove return types from doc blocks later, keep in for now.
        PhpdocNoEmptyReturnFixer::class,

        // Don't camel case tests (e.g. keep snake_case or a mixture!) - existing Laravel test as snake_case
        PhpUnitMethodCasingFixer::class,

        // Skip cached Laravel data
        __DIR__ . '/bootstrap/cache/*',

        // skip paths with legacy code
        // __DIR__ . '/packages/*/src/Legacy',

        //        ArraySyntaxFixer::class => [
        //            // path to file (you can copy this from error report)
        //            __DIR__ . '/packages/EasyCodingStandard/packages/SniffRunner/src/File/File.php',
        //
        //            // or multiple files by path to match against "fnmatch()"
        //            __DIR__ . '/packages/*/src/Command',
        //        ],

        // skip rule completely
        //        ArraySyntaxFixer::class,

        // just single one part of the rule?
        //        ArraySyntaxFixer::class . '.SomeSingleOption',

        // ignore specific error message
        //        'Cognitive complexity for method "addAction" is 13 but has to be less than or equal to 8.',
    ]);

    // run and fix, one by one
    $ecsConfig->sets([
        SetList::SPACES,
        SetList::ARRAY,
        SetList::DOCBLOCK,
        SetList::NAMESPACES,
        SetList::COMMENTS,
        SetList::CONTROL_STRUCTURES,
        SetList::CLEAN_CODE,
        SetList::STRICT,
        SetList::PSR_12,
        SetList::PHPUNIT,
    ]);

    // add declare(strict_types=1); to all php files:
    $ecsConfig->rule(DeclareStrictTypesFixer::class);

    // change $array = array(); to $array = [];
    $ecsConfig->ruleWithConfiguration(ArraySyntaxFixer::class, [
        'syntax' => 'short',
    ]);

    // This is HEIW opinionated rule to automatically align key value pairs, I think they are easier to read.
    // $array [
    //    'name'    => 'Fred',
    //    'address' => '123 high street',
    // ];
    $ecsConfig->ruleWithConfiguration(BinaryOperatorSpacesFixer::class, [
        // My preference is to line up arrays (mostly), it's easier to read 😉
        // if other operators are affected, change 'default' => 'single'
        'default'   => 'align_single_space_minimal',
        'operators' => [
            '|'  => 'no_space',
            '=>' => 'align_single_space_minimal',
        ],
    ]);

    // indent and tabs/spaces
    // [default: spaces]
    //    $ecsConfig->indentation('spaces');

    // [default: PHP_EOL]; other options: "\n". Force "\n" on windows and mac
    $ecsConfig->lineEnding("\n");
};
