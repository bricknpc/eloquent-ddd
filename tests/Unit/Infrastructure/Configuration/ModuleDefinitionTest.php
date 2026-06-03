<?php

declare(strict_types=1);

namespace BrickNPC\EloquentDDD\Tests\Unit\Infrastructure\Configuration;

use Psr\Log\LoggerInterface;
use Illuminate\Routing\Router;
use Illuminate\Console\Command;
use Orchestra\Testbench\TestCase;
use Illuminate\Console\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Translation\FileLoader;
use Illuminate\Translation\Translator;
use PHPUnit\Framework\Attributes\Test;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Events\Dispatcher;
use PHPUnit\Framework\Attributes\UsesClass;
use Illuminate\Database\Migrations\Migrator;
use Illuminate\View\Compilers\BladeCompiler;
use Illuminate\Foundation\Exceptions\Handler;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesFunction;
use Illuminate\Contracts\Http\Kernel as HttpKernel;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Contracts\View\Factory as ViewFactory;
use BrickNPC\EloquentDDD\Infrastructure\Modules\ModulePaths;
use Illuminate\Foundation\Application as LaravelApplication;
use BrickNPC\EloquentDDD\Infrastructure\Modules\ModuleContext;
use BrickNPC\EloquentDDD\Infrastructure\Registrars\ConfigRegistrar;
use BrickNPC\EloquentDDD\Infrastructure\Registrars\RoutingRegistrar;
use BrickNPC\EloquentDDD\Infrastructure\Configuration\ModuleDefinition;

/**
 * @internal
 */
#[CoversClass(ModuleDefinition::class)]
#[UsesClass(ModuleContext::class)]
#[UsesClass(ModulePaths::class)]
#[UsesClass(RoutingRegistrar::class)]
#[UsesClass(ConfigRegistrar::class)]
#[UsesFunction('BrickNPC\EloquentDDD\Domain\path')]
class ModuleDefinitionTest extends TestCase
{
    private ModuleContext $context;
    private LoggerInterface $logger;

    private LaravelApplication $application;

    protected function setUp(): void
    {
        parent::setUp();

        $this->logger = new class implements LoggerInterface {
            public function emergency($message, array $context = []): void {}

            public function alert($message, array $context = []): void {}

            public function critical($message, array $context = []): void {}

            public function error($message, array $context = []): void {}

            public function warning($message, array $context = []): void {}

            public function notice($message, array $context = []): void {}

            public function info($message, array $context = []): void {}

            public function debug($message, array $context = []): void {}

            public function log($level, $message, array $context = []): void {}
        };

        Application::forgetBootstrappers();

        $this->application = $this->app;

        $this->context = new ModuleContext(
            name: 'Users',
            baseNamespace: 'App\Users',
            basePath: __DIR__ . '/../../../Fixtures/App/Users',
        );
    }

    protected function tearDown(): void
    {
        Application::forgetBootstrappers();

        parent::tearDown();
    }

    #[Test]
    public function it_registers_routes(): void
    {
        $router = $this->app->make(Router::class);

        $definition = new ModuleDefinition($this->application, $this->logger, $this->context);

        $definition->withRouting(web: 'web.php');

        $this->assertTrue(
            collect($router->getRoutes()->getRoutes())
                ->contains(fn ($route) => $route->uri() === 'test-web-route'),
        );
    }

    #[Test]
    public function it_does_not_register_routes_when_routes_are_cached(): void
    {
        $this->app->instance('routes.cached', true);

        $definition = new ModuleDefinition($this->application, $this->logger, $this->context);

        $definition->withRouting(web: 'web.php');

        $this->assertFalse(
            collect($this->app->make(Router::class)->getRoutes()->getRoutes())
                ->contains(fn ($route) => $route->uri() === 'test-web-route'),
        );
    }

    #[Test]
    public function it_registers_migrations(): void
    {
        $definition = new ModuleDefinition($this->application, $this->logger, $this->context);

        $definition->withMigrations();

        $migrator = $this->app->make(Migrator::class);

        $this->assertNotEmpty($migrator->paths());
        $this->assertStringContainsString('Infrastructure', $migrator->paths()[0]);
        $this->assertStringContainsString('Database', $migrator->paths()[0]);
    }

    #[Test]
    public function it_registers_middleware(): void
    {
        $called     = false;
        $definition = new ModuleDefinition($this->application, $this->logger, $this->context);

        $definition->withMiddleware(function (Middleware $middleware) use (&$called) {
            $called = true;
        });

        $this->app->make(HttpKernel::class);

        $this->assertTrue($called);
    }

    #[Test]
    public function it_registers_commands(): void
    {
        $definition = new ModuleDefinition($this->application, $this->logger, $this->context);

        $definition->withCommands([
            ModuleDefinitionCommand::class,
        ]);

        $artisan = $this->makeConsoleApplication();

        $this->assertTrue($artisan->has('module-definition:test'));
    }

    #[Test]
    public function it_registers_schedule_callbacks(): void
    {
        $called     = false;
        $definition = new ModuleDefinition($this->application, $this->logger, $this->context);

        $definition->withSchedule(function (Schedule $schedule) use (&$called) {
            $called = true;

            $schedule->call(fn () => null);
        });

        $this->makeConsoleApplication();

        $this->assertTrue($called);
        $this->assertNotEmpty($this->app->make(Schedule::class)->events());
    }

    #[Test]
    public function it_registers_exception_callbacks(): void
    {
        $called     = false;
        $definition = new ModuleDefinition($this->application, $this->logger, $this->context);

        $definition->withExceptions(function (Exceptions $exceptions) use (&$called) {
            $called = true;

            $this->assertInstanceOf(Exceptions::class, $exceptions);
        });

        $this->app->make(Handler::class);

        $this->assertTrue($called);
    }

    #[Test]
    public function it_registers_event_listeners(): void
    {
        $dispatcher = $this->app->make(Dispatcher::class);

        $definition = new ModuleDefinition($this->application, $this->logger, $this->context);

        $definition->withEvents(
            \stdClass::class,
            fn ($event) => null,
        );

        $this->assertTrue($dispatcher->hasListeners(\stdClass::class));
    }

    #[Test]
    public function it_registers_view_namespaces(): void
    {
        $definition = new ModuleDefinition(
            $this->application,
            $this->logger,
            $this->context,
        );

        $definition->withViews();

        /** @var Factory $view */
        $view = $this->app->make(Factory::class);

        // force execution of afterResolving callbacks
        $this->app->make('view');

        $this->assertArrayHasKey(
            'users',
            $view->getFinder()->getHints(),
        );
    }

    #[Test]
    public function it_registers_view_composers(): void
    {
        $view = new FakeViewFactory();

        $this->app->forgetInstance('view');
        $this->app->bind('view', fn () => $view);

        $definition = new ModuleDefinition($this->application, $this->logger, $this->context);

        $composer = fn () => null;

        $definition->withViewComposer('test', $composer);

        $this->app->make('view');

        $this->assertSame([
            ['test', $composer],
        ], $view->composers);
    }

    #[Test]
    public function it_registers_view_creators(): void
    {
        $view = new FakeViewFactory();

        $this->app->forgetInstance('view');
        $this->app->bind('view', fn () => $view);

        $definition = new ModuleDefinition($this->application, $this->logger, $this->context);

        $creator = fn () => null;

        $definition->withViewCreator('test', $creator);

        $this->app->make('view');

        $this->assertSame([
            ['test', $creator],
        ], $view->creators);
    }

    #[Test]
    public function it_registers_view_components(): void
    {
        $this->app->forgetInstance(BladeCompiler::class);

        $definition = new ModuleDefinition($this->application, $this->logger, $this->context);

        $definition->withViewComponents();

        $blade = $this->app->make(BladeCompiler::class);

        $this->assertSame(
            'users',
            $blade->getClassComponentNamespaces()[ModulePaths::components($this->context)],
        );
    }

    #[Test]
    public function it_registers_translations(): void
    {
        $definition = new ModuleDefinition($this->application, $this->logger, $this->context);

        $definition->withTranslations();

        /** @var Translator $translator */
        $translator = $this->app->make('translator');

        /** @var FileLoader $loader */
        $loader = $translator->getLoader();

        $this->assertContains(ModulePaths::translations($this->context), $loader->jsonPaths());
    }

    #[Test]
    public function it_registers_config(): void
    {
        $definition = new ModuleDefinition($this->application, $this->logger, $this->context);

        $definition->withConfig('users.php');

        $this->assertSame(
            [
                'enabled' => true,
            ],
            $this->app->make(Repository::class)->get('users'),
        );
    }

    #[Test]
    public function it_does_not_register_config_when_configuration_is_cached(): void
    {
        $this->app->instance('config_loaded_from_cache', true);

        $definition = new ModuleDefinition($this->application, $this->logger, $this->context);

        $this->assertSame($definition, $definition->withConfig('missing.php'));
    }

    private function makeConsoleApplication(): Application
    {
        return new Application(
            $this->app,
            $this->app->make(Dispatcher::class),
            '1.0.0',
        );
    }
}

final class ModuleDefinitionCommand extends Command
{
    protected $signature = 'module-definition:test';

    public function handle(): int
    {
        return self::SUCCESS;
    }
}

final class FakeViewFactory implements ViewFactory
{
    /** @var array<int, array{array<int, string>|string, \Closure|string}> */
    public array $composers = [];

    /** @var array<int, array{array<int, string>|string, \Closure|string}> */
    public array $creators = [];

    /** @var array<string, array<int, string>|string> */
    public array $namespaces = [];

    public function exists($view)
    {
        return false;
    }

    public function file($path, $data = [], $mergeData = [])
    {
        throw new \BadMethodCallException('Not implemented.');
    }

    public function make($view, $data = [], $mergeData = [])
    {
        throw new \BadMethodCallException('Not implemented.');
    }

    public function share($key, $value = null)
    {
        return null;
    }

    public function composer($views, $callback)
    {
        $this->composers[] = [$views, $callback];

        return [];
    }

    public function creator($views, $callback)
    {
        $this->creators[] = [$views, $callback];

        return [];
    }

    public function addNamespace($namespace, $hints)
    {
        $this->namespaces[$namespace] = $hints;

        return $this;
    }

    public function replaceNamespace($namespace, $hints)
    {
        $this->namespaces[$namespace] = $hints;

        return $this;
    }
}
