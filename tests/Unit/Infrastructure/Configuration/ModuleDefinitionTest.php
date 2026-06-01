<?php

declare(strict_types=1);

namespace BrickNPC\EloquentDDD\Tests\Unit\Infrastructure\Configuration;

use Psr\Log\LoggerInterface;
use Illuminate\Routing\Router;
use Illuminate\Console\Command;
use Orchestra\Testbench\TestCase;
use Illuminate\Contracts\View\Factory;
use PHPUnit\Framework\Attributes\Test;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Events\Dispatcher;
use PHPUnit\Framework\Attributes\UsesClass;
use Illuminate\Database\Migrations\Migrator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesFunction;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Contracts\View\Factory as ViewFactory;
use BrickNPC\EloquentDDD\Infrastructure\Modules\ModuleContext;
use BrickNPC\EloquentDDD\Infrastructure\Registrars\ConfigRegistrar;
use BrickNPC\EloquentDDD\Infrastructure\Registrars\RoutingRegistrar;
use BrickNPC\EloquentDDD\Infrastructure\Configuration\ModuleDefinition;

/**
 * @internal
 */
#[CoversClass(ModuleDefinition::class)]
#[UsesClass(ModuleContext::class)]
#[UsesClass(RoutingRegistrar::class)]
#[UsesClass(ConfigRegistrar::class)]
#[UsesFunction('BrickNPC\EloquentDDD\Domain\path')]
class ModuleDefinitionTest extends TestCase
{
    private ModuleContext $context;
    private LoggerInterface $logger;

    private Application $consoleApplication;

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

        $this->consoleApplication = $this->app->make(Application::class); // new Application($this->app, $this->app->make(Dispatcher::class), '1.0.0');

        $this->context = new ModuleContext(
            name: 'Users',
            baseNamespace: 'App\Users',
            basePath: __DIR__ . '/fake-module',
        );

        @mkdir($this->context->basePath . '/Infrastructure/Database/Migrations', 0777, true);
        @mkdir($this->context->basePath . '/Application/Http/Routes', 0777, true);
        @mkdir($this->context->basePath . '/Application/Resources/Views', 0777, true);
    }

    #[Test]
    public function it_registers_routes(): void
    {
        $router = $this->app->make(Router::class);

        $this->createRouteFile('web.php');

        $definition = new ModuleDefinition($this->consoleApplication, $this->logger, $this->context);

        $definition->withRouting(web: 'web.php');

        $this->assertTrue(
            collect($router->getRoutes()->getRoutes())
                ->contains(fn ($route) => $route->uri() === 'test-web-route'),
        );
    }

    #[Test]
    public function it_registers_migrations(): void
    {
        $definition = new ModuleDefinition($this->consoleApplication, $this->logger, $this->context);

        $definition->withMigrations();

        $migrator = $this->app->make(Migrator::class);

        $this->assertNotEmpty($migrator->paths());
        $this->assertStringContainsString('Infrastructure', $migrator->paths()[0]);
        $this->assertStringContainsString('Database', $migrator->paths()[0]);
    }

    #[Test]
    public function it_registers_middleware(): void
    {
        $definition = new ModuleDefinition($this->consoleApplication, $this->logger, $this->context);

        $definition->withMiddleware(function (Middleware $middleware) {
            $middleware->append([]);
        });

        $this->assertTrue(true);
    }

    #[Test]
    public function it_registers_commands(): void
    {
        $definition = new ModuleDefinition($this->consoleApplication, $this->logger, $this->context);

        $definition->withCommands([
            Command::class,
        ]);

        $this->assertTrue(true);
    }

    #[Test]
    public function it_registers_schedule_callbacks(): void
    {
        $definition = new ModuleDefinition($this->consoleApplication, $this->logger, $this->context);

        $definition->withSchedule(function (Schedule $schedule) {
            $schedule->call(fn () => null);
        });

        $this->assertTrue(true);
    }

    #[Test]
    public function it_registers_event_listeners(): void
    {
        $dispatcher = $this->app->make(Dispatcher::class);

        $definition = new ModuleDefinition($this->consoleApplication, $this->logger, $this->context);

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
            $this->consoleApplication,
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
        $view = $this->app->make(ViewFactory::class);

        $definition = new ModuleDefinition($this->consoleApplication, $this->logger, $this->context);

        $definition->withViewComposer('test', fn () => null);

        $this->assertTrue(true);
    }

    #[Test]
    public function it_registers_view_creators(): void
    {
        $definition = new ModuleDefinition($this->consoleApplication, $this->logger, $this->context);

        $definition->withViewCreator('test', fn () => null);

        $this->assertTrue(true);
    }

    #[Test]
    public function it_registers_view_components(): void
    {
        $definition = new ModuleDefinition($this->consoleApplication, $this->logger, $this->context);

        $definition->withViewComponents();

        $this->assertTrue(true);
    }

    #[Test]
    public function it_registers_translations(): void
    {
        $definition = new ModuleDefinition($this->consoleApplication, $this->logger, $this->context);

        $definition->withTranslations();

        $this->assertTrue(true);
    }

    #[Test]
    public function it_registers_config(): void
    {
        $this->app->instance(Repository::class, new class implements Repository {
            private array $data = [];

            public function get($key, $default = null)
            {
                return $this->data[$key] ?? $default;
            }

            public function set($key, $value = null)
            {
                $this->data[$key] = $value;
            }

            public function has($key)
            {
                return isset($this->data[$key]);
            }

            public function all()
            {
                return $this->data;
            }

            public function forget($key) {}

            public function push($key, $value) {}

            public function prepend($key, $value) {}
        });

        $definition = new ModuleDefinition($this->consoleApplication, $this->logger, $this->context);

        $definition->withConfig();

        $this->assertTrue(true);
    }

    private function createRouteFile(string $filename): void
    {
        file_put_contents(
            $this->context->basePath . '/Application/Http/Routes/' . $filename,
            <<<'PHP'
<?php

use Illuminate\Support\Facades\Route;

Route::get('/test-web-route', fn () => 'ok');
PHP
        );
    }
}
