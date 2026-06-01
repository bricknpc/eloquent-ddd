<?php

declare(strict_types=1);

namespace BrickNPC\EloquentDDD\Infrastructure\Configuration;

use Psr\Log\LoggerInterface;
use Illuminate\Routing\Router;
use Illuminate\Events\Dispatcher;
use Illuminate\Contracts\View\View;
use Illuminate\Contracts\View\Factory;
use Illuminate\Translation\Translator;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Database\Migrations\Migrator;
use Illuminate\View\Compilers\BladeCompiler;
use Illuminate\Foundation\Exceptions\Handler;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Foundation\CachesRoutes;
use Illuminate\Contracts\Http\Kernel as HttpKernel;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Contracts\Foundation\CachesConfiguration;
use Illuminate\Console\Application as ConsoleApplication;
use BrickNPC\EloquentDDD\Infrastructure\Modules\ModuleContext;
use BrickNPC\EloquentDDD\Infrastructure\Registrars\ConfigRegistrar;
use BrickNPC\EloquentDDD\Infrastructure\Registrars\RoutingRegistrar;

use function BrickNPC\EloquentDDD\Domain\path;

final readonly class ModuleDefinition
{
    public function __construct(
        private Application&ConsoleApplication $application,
        private LoggerInterface $logger,
        private ModuleContext $context,
    ) {
        $this->logger->debug(sprintf('Initiated module %s', $this->context->name), [
            'module'    => $this->context->name,
            'namespace' => $this->context->baseNamespace,
            'path'      => $this->context->basePath,
        ]);
    }

    /**
     * @param null|array<int, string>|string $web
     * @param null|array<int, string>|string $api
     */
    public function withRouting(
        array|string|null $web = null,
        array|string|null $api = null,
        ?string $apiPrefix = null,
    ): self {
        if ($this->application instanceof CachesRoutes && $this->application->routesAreCached()) {
            return $this;
        }

        /** @var Router $router */
        $router = $this->application->make('router');

        new RoutingRegistrar($router, $this->context->basePath)($web, $api, $apiPrefix);

        $this->logger->debug(sprintf('Registered routes for module %s', $this->context->name), [
            'module'    => $this->context->name,
            'web'       => $web,
            'api'       => $api,
            'apiPrefix' => $apiPrefix,
        ]);

        return $this;
    }

    public function withMigrations(): self
    {
        $migrationsPath = path($this->context->basePath, 'Infrastructure', 'Database', 'Migrations');

        $this->application->afterResolving('migrator', function (Migrator $migrator) use ($migrationsPath) {
            $migrator->path($migrationsPath);
        });

        $this->logger->debug(sprintf('Registered migrations for module %s', $this->context->name), [
            'module' => $this->context->name,
            'path'   => $migrationsPath,
        ]);

        return $this;
    }

    /**
     * @param \Closure(Middleware $middleware): void $callback
     */
    public function withMiddleware(\Closure $callback): self
    {
        /** @var Middleware $middleware */
        $middleware = $this->application->make(Middleware::class);

        $this->application->afterResolving(HttpKernel::class, function (HttpKernel $kernel) use ($callback, $middleware) {
            call_user_func($callback, $middleware);
        });

        $this->logger->debug(sprintf('Registered middleware for module %s', $this->context->name), [
            'module' => $this->context->name,
        ]);

        return $this;
    }

    /**
     * @param array<int, class-string> $commands
     */
    public function withCommands(array $commands): self
    {
        $this->application->starting(function (ConsoleApplication $artisan) use ($commands) {
            $artisan->resolveCommands($commands);
        });

        $this->logger->debug(sprintf('Registered commands for module %s', $this->context->name), [
            'module'   => $this->context->name,
            'commands' => $commands,
        ]);

        return $this;
    }

    /**
     * @param \Closure(Schedule $callback): void $callback
     */
    public function withSchedule(\Closure $callback): self
    {
        $this->application->starting(function (Application $application) use ($callback) {
            /** @var Schedule $schedule */
            $schedule = $application->make(Schedule::class);

            call_user_func($callback, $schedule);
        });

        $this->logger->debug(sprintf('Registered schedule for module %s', $this->context->name), [
            'module' => $this->context->name,
        ]);

        return $this;
    }

    /**
     * @param \Closure(Exceptions $exceptions): void $exceptions
     */
    public function withExceptions(\Closure $exceptions): self
    {
        $this->application->afterResolving(
            Handler::class,
            fn (Handler $handler) => call_user_func($exceptions, new Exceptions($handler)),
        );

        $this->logger->debug(sprintf('Registered exception handlers for module %s', $this->context->name), [
            'module' => $this->context->name,
        ]);

        return $this;
    }

    /**
     * @param class-string                               $event
     * @param class-string|\Closure(object $event): void $listener
     */
    public function withEvents(string $event, \Closure|string $listener): self
    {
        /** @var Dispatcher $dispatcher */
        $dispatcher = $this->application->make('events');

        $dispatcher->listen($event, $listener);

        $this->logger->debug(sprintf('Registered event listener for module %s', $this->context->name), [
            'module'   => $this->context->name,
            'event'    => $event,
            'listener' => $listener instanceof \Closure ? 'Closure' : $listener,
        ]);

        return $this;
    }

    public function withViews(?string $viewPath = null, ?string $viewNamespace = null): self
    {
        $viewNamespace ??= $this->context->viewNamespace;
        $viewPath      ??= path($this->context->basePath, 'Application', 'Resources', 'Views');

        $this->application->afterResolving('view', function (Factory $view) use ($viewNamespace, $viewPath) {
            $view->addNamespace($viewNamespace, $viewPath);
        });

        $this->logger->debug(sprintf('Registered view paths for module %s', $this->context->name), [
            'module'         => $this->context->name,
            'view-namespace' => $viewNamespace,
        ]);

        return $this;
    }

    /**
     * @param array<int, string>|string         $views
     * @param \Closure(View $view): void|string $composer
     */
    public function withViewComposer(array|string $views, \Closure|string $composer): self
    {
        $this->application->afterResolving('view', function (Factory $view) use ($views, $composer) {
            $view->composer($views, $composer);
        });

        $this->logger->debug(sprintf('Registered view composer for module %s', $this->context->name), [
            'module' => $this->context->name,
        ]);

        return $this;
    }

    /**
     * @param array<int, string>|string         $views
     * @param \Closure(View $view): void|string $creator
     */
    public function withViewCreator(array|string $views, \Closure|string $creator): self
    {
        $this->application->afterResolving('view', function (Factory $view) use ($views, $creator) {
            $view->creator($views, $creator);
        });

        $this->logger->debug(sprintf('Registered view creator for module %s', $this->context->name), [
            'module' => $this->context->name,
        ]);

        return $this;
    }

    public function withViewComponents(?string $componentFolder = null, ?string $prefix = null): self
    {
        $componentFolder ??= path($this->context->basePath, 'Application', 'Resources', 'Components');
        $prefix          ??= $this->context->viewNamespace;

        $this->application->afterResolving(BladeCompiler::class, function (BladeCompiler $blade) use ($prefix, $componentFolder) {
            $blade->componentNamespace($prefix, $componentFolder);
        });

        $this->logger->debug(sprintf('Registered view components for module %s', $this->context->name), [
            'module' => $this->context->name,
            'prefix' => $prefix,
            'folder' => $componentFolder,
        ]);

        return $this;
    }

    public function withTranslations(?string $path = null): self
    {
        $path ??= path($this->context->basePath, 'Application', 'Resources', 'Lang');

        $this->application->afterResolving('translator', function (Translator $translator) use ($path) {
            $translator->addJsonPath($path);
        });

        $this->logger->debug(sprintf('Registered translations for module %s', $this->context->name), [
            'module' => $this->context->name,
            'path'   => $path,
        ]);

        return $this;
    }

    public function withConfig(string ...$files): self
    {
        if ($this->application instanceof CachesConfiguration && $this->application->configurationIsCached()) {
            return $this;
        }

        /** @var Repository $repository */
        $repository = $this->application->make(Repository::class);
        $configPath = path($this->context->basePath, 'Infrastructure', 'Config');

        new ConfigRegistrar($repository, $configPath, $this->context->name)(...$files);

        $this->logger->debug(sprintf('Registered config for module %s', $this->context->name), [
            'module' => $this->context->name,
            'files'  => $files,
        ]);

        return $this;
    }
}
