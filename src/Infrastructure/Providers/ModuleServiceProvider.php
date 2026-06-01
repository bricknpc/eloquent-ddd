<?php

declare(strict_types=1);

namespace BrickNPC\EloquentDDD\Infrastructure\Providers;

use Psr\Log\LoggerInterface;
use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Console\Application as ConsoleApplication;
use BrickNPC\EloquentDDD\Infrastructure\Modules\ModuleResolver;
use BrickNPC\EloquentDDD\Infrastructure\Configuration\ModuleDefinition;

abstract class ModuleServiceProvider extends ServiceProvider
{
    protected ?string $module    = null;
    protected ?string $namespace = null;
    protected ?string $path      = null;

    final protected function module(?string $name = null, ?string $namespace = null, ?string $path = null): ModuleDefinition
    {
        $moduleContext = ModuleResolver::fromServiceProvider(
            static::class,
            $name      ?? $this->module,
            $namespace ?? $this->namespace,
            $path      ?? $this->path,
        );

        /** @var LoggerInterface $logger */
        $logger = $this->app->make(LoggerInterface::class);

        /** @var Application&ConsoleApplication $app */
        $app = $this->app;

        return new ModuleDefinition($app, $logger, $moduleContext);
    }
}
