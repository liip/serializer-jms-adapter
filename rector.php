<?php

declare(strict_types=1);

use Rector\Caching\ValueObject\Storage\FileCacheStorage;
use Rector\CodeQuality\Rector\Class_\InlineConstructorDefaultToPropertyRector;
use Rector\Config\RectorConfig;
use Rector\PHPUnit\Set\PHPUnitSetList;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;
use Rector\TypeDeclaration\Rector\Class_\PropertyTypeFromStrictSetterGetterRector;
use Rector\TypeDeclaration\Rector\ClassMethod\AddMethodCallBasedStrictParamTypeRector;
use Rector\TypeDeclaration\Rector\ClassMethod\AddParamTypeFromPropertyTypeRector;
use Rector\TypeDeclaration\Rector\ClassMethod\BoolReturnTypeFromBooleanStrictReturnsRector;
use Rector\TypeDeclaration\Rector\ClassMethod\ParamTypeByMethodCallTypeRector;
use Rector\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictConstantReturnRector;
use Rector\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictNativeCallRector;
use Rector\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictNewArrayRector;
use Rector\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictParamRector;
use Rector\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictTypedCallRector;
use Rector\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictTypedPropertyRector;
use Rector\TypeDeclaration\Rector\ClassMethod\StringReturnTypeFromStrictScalarReturnsRector;
use Rector\TypeDeclaration\Rector\Property\TypedPropertyFromStrictConstructorRector;
use Rector\TypeDeclaration\Rector\Property\TypedPropertyFromStrictSetUpRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([
        __DIR__.'/src',
        __DIR__.'/tests',
    ]);

    $rectorConfig->rules([
        InlineConstructorDefaultToPropertyRector::class,
        BoolReturnTypeFromBooleanStrictReturnsRector::class,
        ReturnTypeFromStrictConstantReturnRector::class,
        ReturnTypeFromStrictNativeCallRector::class,
        ReturnTypeFromStrictNewArrayRector::class,
        ReturnTypeFromStrictParamRector::class,
        StringReturnTypeFromStrictScalarReturnsRector::class,
        ReturnTypeFromStrictTypedCallRector::class,
        ReturnTypeFromStrictTypedPropertyRector::class,
        PropertyTypeFromStrictSetterGetterRector::class,
        TypedPropertyFromStrictConstructorRector::class,
        TypedPropertyFromStrictSetUpRector::class,
        AddMethodCallBasedStrictParamTypeRector::class,
        ParamTypeByMethodCallTypeRector::class,
        AddParamTypeFromPropertyTypeRector::class,
    ]);

    $rectorConfig->sets([
        LevelSetList::UP_TO_PHP_80,
        SetList::TYPE_DECLARATION,
        PHPUnitSetList::PHPUNIT_90,
    ]);

    // use imports instead of FQN: https://github.com/rectorphp/rector/blob/main/docs/auto_import_names.md#auto-import-names
    $rectorConfig->importNames();
    // Do not import Single short classes:
    $rectorConfig->importShortClasses(false);

    // Caching configuration
    $rectorConfig->cacheClass(FileCacheStorage::class);
    $rectorConfig->cacheDirectory('.cache/rector');
};
