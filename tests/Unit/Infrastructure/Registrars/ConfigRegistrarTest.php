<?php

declare(strict_types=1);

namespace BrickNPC\EloquentDDD\Tests\Unit\Infrastructure\Registrars;

use PHPUnit\Framework\Attributes\Test;
use BrickNPC\EloquentDDD\Tests\TestCase;
use Illuminate\Contracts\Config\Repository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesFunction;
use BrickNPC\EloquentDDD\Infrastructure\Registrars\ConfigRegistrar;

/**
 * @internal
 */
#[CoversClass(ConfigRegistrar::class)]
#[UsesFunction('BrickNPC\EloquentDDD\Domain\path')]
class ConfigRegistrarTest extends TestCase
{
    private string $basePath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->basePath = sys_get_temp_dir() . '/eloquent-ddd-config-test';

        $this->deleteDirectory($this->basePath);

        mkdir($this->basePath . '/Infrastructure/Config/Admin', 0777, true);
    }

    protected function tearDown(): void
    {
        $this->deleteDirectory($this->basePath);

        parent::tearDown();
    }

    #[Test]
    public function it_registers_default_config_file_under_module_key(): void
    {
        $this->createConfigFile('user-management.php', [
            'driver' => 'database',
            'cache'  => true,
        ]);

        $repository = $this->app->make(Repository::class);

        $registrar = new ConfigRegistrar(
            repository: $repository,
            configPath: $this->basePath,
            module: 'UserManagement',
        );

        $registrar('user-management.php');

        $this->assertSame(
            [
                'driver' => 'database',
                'cache'  => true,
            ],
            $repository->get('user-management'),
        );
    }

    #[Test]
    public function it_registers_nested_config_files_using_dot_notation(): void
    {
        $this->createConfigFile('Admin/permissions.php', [
            'enabled' => true,
        ]);

        $repository = $this->app->make(Repository::class);

        $registrar = new ConfigRegistrar(
            repository: $repository,
            configPath: $this->basePath,
            module: 'UserManagement',
        );

        $registrar('Admin/permissions.php');

        $this->assertSame(
            [
                'enabled' => true,
            ],
            $repository->get('user-management.admin.permissions'),
        );
    }

    #[Test]
    public function it_merges_existing_configuration(): void
    {
        $this->createConfigFile('user-management.php', [
            'driver' => 'database',
            'cache'  => true,
        ]);

        $repository = $this->app->make(Repository::class);

        $repository->set('user-management', [
            'cache' => false,
            'ttl'   => 3600,
        ]);

        $registrar = new ConfigRegistrar(
            repository: $repository,
            configPath: $this->basePath,
            module: 'UserManagement',
        );

        $registrar('user-management.php');

        $this->assertSame(
            [
                'driver' => 'database',
                'cache'  => false,
                'ttl'    => 3600,
            ],
            $repository->get('user-management'),
        );
    }

    #[Test]
    public function it_registers_multiple_config_files(): void
    {
        $this->createConfigFile('user-management.php', [
            'enabled' => true,
        ]);

        $this->createConfigFile('Admin/permissions.php', [
            'roles' => ['admin'],
        ]);

        $repository = $this->app->make(Repository::class);

        $registrar = new ConfigRegistrar(
            repository: $repository,
            configPath: $this->basePath,
            module: 'UserManagement',
        );

        $registrar(
            'user-management.php',
            'Admin/permissions.php',
        );

        $this->assertSame(
            [
                'enabled' => true,
                'admin'   => [
                    'permissions' => [
                        'roles' => ['admin'],
                    ],
                ],
            ],
            $repository->get('user-management'),
        );

        $this->assertSame(
            [
                'roles' => ['admin'],
            ],
            $repository->get('user-management.admin.permissions'),
        );
    }

    #[Test]
    public function it_throws_when_config_file_does_not_exist(): void
    {
        $repository = $this->app->make(Repository::class);

        $registrar = new ConfigRegistrar(
            repository: $repository,
            configPath: $this->basePath,
            module: 'UserManagement',
        );

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Config file');

        $registrar('missing.php');
    }

    /**
     * @param array<string, mixed> $contents
     */
    private function createConfigFile(string $filename, array $contents): void
    {
        $fullPath = $this->basePath . '/Infrastructure/Config/' . $filename;

        $directory = dirname($fullPath);

        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }

        $export = var_export($contents, true);

        file_put_contents(
            $fullPath,
            <<<PHP
<?php

return {$export};
PHP
        );
    }

    private function deleteDirectory(string $directory): void
    {
        if (!is_dir($directory)) {
            return;
        }

        foreach (scandir($directory) as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $path = $directory . '/' . $file;

            if (is_dir($path)) {
                $this->deleteDirectory($path);
            } else {
                unlink($path);
            }
        }

        rmdir($directory);
    }
}
