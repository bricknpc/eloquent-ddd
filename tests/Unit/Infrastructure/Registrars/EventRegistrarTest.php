<?php

declare(strict_types=1);

namespace BrickNPC\EloquentDDD\Tests\Unit\Infrastructure\Registrars;

use PHPUnit\Framework\Attributes\Test;
use BrickNPC\EloquentDDD\Tests\TestCase;
use Illuminate\Contracts\Events\Dispatcher;
use PHPUnit\Framework\Attributes\CoversClass;
use BrickNPC\EloquentDDD\Infrastructure\Registrars\EventRegistrar;

/**
 * @internal
 */
#[CoversClass(EventRegistrar::class)]
class EventRegistrarTest extends TestCase
{
    #[Test]
    public function it_registers_a_string_listener(): void
    {
        /** @var Dispatcher $dispatcher */
        $dispatcher = $this->app->make(Dispatcher::class);

        $registrar = new EventRegistrar($dispatcher);

        $registrar(
            TestEvent::class,
            TestListener::class,
        );

        $listeners = $dispatcher->getListeners(TestEvent::class);

        $this->assertCount(1, $listeners);
    }

    #[Test]
    public function it_registers_a_closure_listener(): void
    {
        /** @var Dispatcher $dispatcher */
        $dispatcher = $this->app->make(Dispatcher::class);

        $registrar = new EventRegistrar($dispatcher);

        $registrar(
            TestEvent::class,
            function (object $event): void {},
        );

        $listeners = $dispatcher->getListeners(TestEvent::class);

        $this->assertCount(1, $listeners);
    }

    #[Test]
    public function it_dispatches_registered_class_string_listener(): void
    {
        /** @var Dispatcher $dispatcher */
        $dispatcher = $this->app->make(Dispatcher::class);

        $registrar = new EventRegistrar($dispatcher);

        TestListener::$handled = false;

        $registrar(
            TestEvent::class,
            TestListener::class,
        );

        $dispatcher->dispatch(new TestEvent());

        $this->assertTrue(TestListener::$handled);
    }

    #[Test]
    public function it_dispatches_a_closure_listener(): void
    {
        /** @var Dispatcher $dispatcher */
        $dispatcher = $this->app->make(Dispatcher::class);

        $registrar = new EventRegistrar($dispatcher);

        $called = false;

        $registrar(
            TestEvent::class,
            function (object $event) use (&$called): void {
                $called = true;
            },
        );

        $dispatcher->dispatch(new TestEvent());

        $this->assertTrue($called);
    }

    #[Test]
    public function it_registers_multiple_listeners(): void
    {
        /** @var Dispatcher $dispatcher */
        $dispatcher = $this->app->make(Dispatcher::class);

        $registrar = new EventRegistrar($dispatcher);

        $registrar(
            TestEvent::class,
            function (object $event): void {},
        );
        $registrar(TestEvent::class, TestListener::class);

        $dispatcher->dispatch(new TestEvent());

        $this->assertCount(2, $dispatcher->getListeners(TestEvent::class));
    }

    #[Test]
    public function it_dispatches_multiple_listeners(): void
    {
        /** @var Dispatcher $dispatcher */
        $dispatcher = $this->app->make(Dispatcher::class);

        $registrar = new EventRegistrar($dispatcher);

        $called = false;

        $registrar(
            TestEvent::class,
            function (object $event) use (&$called): void {
                $called = true;
            },
        );
        $registrar(TestEvent::class, TestListener::class);

        $dispatcher->dispatch(new TestEvent());

        $this->assertTrue($called);
        $this->assertTrue(TestListener::$handled);
    }
}

final class TestEvent {}

final class TestListener
{
    public static bool $handled = false;

    public function handle(TestEvent $event): void
    {
        self::$handled = true;
    }
}
