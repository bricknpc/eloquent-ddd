<?php

declare(strict_types=1);

namespace BrickNPC\EloquentDDD\Tests\Unit\Infrastructure\Registrars;

use Illuminate\Routing\Router;
use PHPUnit\Framework\Attributes\Test;
use BrickNPC\EloquentDDD\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesFunction;
use BrickNPC\EloquentDDD\Infrastructure\Registrars\RoutingRegistrar;

/**
 * @internal
 */
#[CoversClass(RoutingRegistrar::class)]
#[UsesFunction('BrickNPC\EloquentDDD\Domain\path')]
class RoutingRegistrarTest extends TestCase
{
    private string $basePath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->basePath = sys_get_temp_dir() . '/eloquent-ddd-module';

        // Clean slate
        if (is_dir($this->basePath)) {
            $this->deleteDirectory($this->basePath);
        }

        mkdir($this->basePath, 0777, true);

        // Create expected structure
        mkdir($this->basePath . '/Application/Http/Routes', 0777, true);
    }

    protected function tearDown(): void
    {
        $this->deleteDirectory($this->basePath);

        parent::tearDown();
    }

    #[Test]
    public function it_registers_web_routes(): void
    {
        $this->createRouteFile('web.php');

        $registrar = $this->makeRegistrar();

        $registrar->__invoke(
            web: 'web.php',
        );

        $this->assertRouteExists('/test-web-route');
    }

    #[Test]
    public function it_registers_api_routes_with_prefix(): void
    {
        $this->createRouteFile('api.php');

        $registrar = $this->makeRegistrar();

        $registrar->__invoke(
            api: 'api.php',
            apiPrefix: 'v1',
        );

        $this->assertRouteExists('v1/test-api-route');
    }

    #[Test]
    public function it_registers_multiple_web_routes(): void
    {
        $this->createRouteFile('web.php');
        $this->createRouteFile('web-extra.php');

        $registrar = $this->makeRegistrar();

        $registrar->__invoke(
            web: ['web.php', 'web-extra.php'],
        );

        $this->assertRouteExists('/test-web-route');
        $this->assertRouteExists('/test-web-extra-route');
    }

    #[Test]
    public function it_registers_multiple_api_routes(): void
    {
        $this->createRouteFile('api.php');
        $this->createRouteFile('api-extra.php');

        $registrar = $this->makeRegistrar();

        $registrar->__invoke(
            api: ['api.php', 'api-extra.php'],
            apiPrefix: 'v1',
        );

        $this->assertRouteExists('v1/test-api-route');
        $this->assertRouteExists('v1/test-api-extra-route');
    }

    #[Test]
    public function it_skips_non_existing_route_files(): void
    {
        $this->createRouteFile('web.php');

        $registrar = $this->makeRegistrar();

        $registrar->__invoke(
            web: ['web.php', 'missing.php'],
        );

        $this->assertRouteExists('/test-web-route');
    }

    #[Test]
    public function it_handles_null_values_gracefully(): void
    {
        $this->expectNotToPerformAssertions();

        $registrar = $this->makeRegistrar();

        $registrar->__invoke();
    }

    private function makeRegistrar(): RoutingRegistrar
    {
        return new RoutingRegistrar(
            router: $this->app->make(Router::class),
            path: $this->basePath,
        );
    }

    private function createRouteFile(string $filename): void
    {
        $filePath = $this->basePath . '/Application/Http/Routes/' . $filename;

        file_put_contents($filePath, <<<'PHP'
<?php

use Illuminate\Support\Facades\Route;

Route::get('/test-'.str_replace('.php', '', basename(__FILE__)).'-route', function () {
    return 'ok';
});
PHP);
    }

    private function assertRouteExists(string $uri): void
    {
        $response = $this->get($uri);

        $response->assertSuccessful();
    }

    private function deleteDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        foreach (scandir($dir) as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $path = $dir . '/' . $file;

            if (is_dir($path)) {
                $this->deleteDirectory($path);
            } else {
                unlink($path);
            }
        }

        rmdir($dir);
    }
}
