<?php

namespace BrickNPC\EloquentDDD\Infrastructure\Modules;

use function BrickNPC\EloquentDDD\Domain\path;

final readonly class ModulePaths
{
    public static function migrations(ModuleContext $context): string
    {
        return path($context->basePath, 'Infrastructure', 'Database', 'Migrations');
    }

    public static function config(ModuleContext $context): string
    {
        return path($context->basePath, 'Infrastructure', 'Config');
    }

    public static function translations(ModuleContext $context): string
    {
        return path($context->basePath, 'Application', 'Resources', 'Lang');
    }

    public static function routes(ModuleContext $context): string
    {
        return path($context->basePath, 'Application', 'Http', 'Routes');
    }

    public static function views(ModuleContext $context): string
    {
        return path($context->basePath, 'Application', 'Resources', 'Views');
    }

    public static function components(ModuleContext $context): string
    {
        return path($context->basePath, 'Application', 'Resources', 'Components');
    }
}