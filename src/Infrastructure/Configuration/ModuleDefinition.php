<?php

declare(strict_types=1);

namespace BrickNPC\EloquentDDD\Infrastructure\Configuration;

use Psr\Log\LoggerInterface;
use Illuminate\Routing\Router;
use Illuminate\Events\Dispatcher;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Database\Migrations\Migrator;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Foundation\CachesRoutes;
use BrickNPC\EloquentDDD\Infrastructure\Dto\ModuleContext;
use BrickNPC\EloquentDDD\Infrastructure\Registrars\EventRegistrar;
use BrickNPC\EloquentDDD\Infrastructure\Registrars\RoutingRegistrar;
use BrickNPC\EloquentDDD\Infrastructure\Registrars\MigrationRegistrar;

use function BrickNPC\EloquentDDD\Domain\path;

final readonly class ModuleDefinition
{
    public function __construct(
        private Application $application,
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
            new MigrationRegistrar($migrator)($migrationsPath);
        });

        $this->logger->debug(sprintf('Registered migrations for module %s', $this->context->name), [
            'module' => $this->context->name,
            'path'   => $migrationsPath,
        ]);

        return $this;
    }

    /**
     * @param null|\Closure(Middleware $middleware): void $callback
     */
    public function withMiddleware(?\Closure $callback = null): self
    {
        return $this;
    }

    /**
     * @param array<int, class-string> $commands
     */
    public function withCommands(array $commands = []): self
    {
        return $this;
    }

    /**
     * @param \Closure(Schedule $schedule): void $schedule
     */
    public function withSchedule(\Closure $schedule): self
    {
        return $this;
    }

    /**
     * @param \Closure(Exceptions $exceptions): void $exceptions
     */
    public function withExceptions(\Closure $exceptions): self
    {
        return $this;
    }

    /**
     * @param class-string          $abstract
     * @param callable|class-string $concrete
     */
    public function withBinding(string $abstract, callable|string $concrete): self
    {
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

        new EventRegistrar($dispatcher)($event, $listener);

        $this->logger->debug(sprintf('Registered event listener for module %s', $this->context->name), [
            'module'   => $this->context->name,
            'event'    => $event,
            'listener' => $listener instanceof \Closure ? 'Closure' : $listener,
        ]);

        return $this;
    }

    public function withViews(string $viewNamespace): self
    {
        return $this;
    }
}
