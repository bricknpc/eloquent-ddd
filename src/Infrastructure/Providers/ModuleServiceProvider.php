<?php

declare(strict_types=1);

namespace BrickNPC\EloquentDDD\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;
use BrickNPC\EloquentDDD\Infrastructure\Discovery\DiscoverModuleName;
use BrickNPC\EloquentDDD\Infrastructure\Discovery\DiscoverModulePath;
use BrickNPC\EloquentDDD\Infrastructure\Configuration\ModuleDefinition;
use BrickNPC\EloquentDDD\Infrastructure\Discovery\DiscoverModuleNamespace;

abstract class ModuleServiceProvider extends ServiceProvider
{
    protected ?string $module    = null;
    protected ?string $namespace = null;
    protected ?string $path      = null;

    final protected function module(?string $name = null, ?string $namespace = null, ?string $path = null): ModuleDefinition
    {
        return new ModuleDefinition(
            $this->app,
            $this->module    = $name           ?? $this->module ?? DiscoverModuleName::fromServiceProvider(static::class),
            $this->namespace = $namespace      ?? $this->namespace ?? DiscoverModuleNamespace::fromServiceProvider(static::class),
            $this->path      = $path           ?? $this->path ?? DiscoverModulePath::fromServiceProvider(static::class),
        );
    }
}
