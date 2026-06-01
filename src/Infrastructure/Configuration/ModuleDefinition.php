<?php

declare(strict_types=1);

namespace BrickNPC\EloquentDDD\Infrastructure\Configuration;

use Illuminate\Routing\Router;
use Illuminate\Events\Dispatcher;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Foundation\CachesRoutes;
use BrickNPC\EloquentDDD\Infrastructure\Dto\ModuleContext;
use BrickNPC\EloquentDDD\Infrastructure\Registrars\EventRegistrar;
use BrickNPC\EloquentDDD\Infrastructure\Registrars\RoutingRegistrar;

final readonly class ModuleDefinition
{
    public function __construct(
        private Application $application,
        private ModuleContext $context,
    ) {}

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

        return $this;
    }

    public function withMigrations(): self
    {
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

        return $this;
    }

    public function withViews(string $viewNamespace): self
    {
        return $this;
    }
}
