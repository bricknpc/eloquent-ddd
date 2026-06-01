<?php

declare(strict_types=1);

namespace BrickNPC\EloquentDDD\Infrastructure\Registrars;

use Illuminate\Contracts\Events\Dispatcher;

final readonly class EventRegistrar
{
    public function __construct(
        private Dispatcher $dispatcher,
    ) {}

    /**
     * @param class-string                               $event
     * @param class-string|\Closure(object $event): void $listener
     */
    public function __invoke(string $event, \Closure|string $listener): void
    {
        $this->dispatcher->listen($event, $listener);
    }
}
