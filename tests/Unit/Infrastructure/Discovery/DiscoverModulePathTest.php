<?php

declare(strict_types=1);

namespace BrickNPC\EloquentDDD\Tests\Unit\Infrastructure\Discovery;

use PHPUnit\Framework\Attributes\Test;
use BrickNPC\EloquentDDD\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use App\Users\Infrastructure\Providers\FakeServiceProvider;
use BrickNPC\EloquentDDD\Infrastructure\Discovery\DiscoverModulePath;

/**
 * @internal
 */
#[CoversClass(DiscoverModulePath::class)]
class DiscoverModulePathTest extends TestCase
{
    private string $basePath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->basePath = sys_get_temp_dir() . '/eloquent-ddd-module';

        if (is_dir($this->basePath)) {
            $this->deleteDirectory($this->basePath);
        }

        $filePath = $this->basePath . '/App/Users/Infrastructure/Providers/FakeServiceProvider.php';

        mkdir(dirname($filePath), 0777, true);

        file_put_contents(
            $filePath,
            '<?php namespace App\Users\Infrastructure\Providers; class FakeServiceProvider {}',
        );

        require_once $filePath;
    }

    protected function tearDown(): void
    {
        $this->deleteDirectory($this->basePath);

        parent::tearDown();
    }

    #[Test]
    public function it_returns_module_path_before_infrastructure_segment(): void
    {
        $result = DiscoverModulePath::fromServiceProvider(
            FakeServiceProvider::class,
        );

        $this->assertStringContainsString(
            $this->basePath . '/App/Users',
            $result,
        );
    }

    #[Test]
    public function it_throws_when_infrastructure_segment_is_missing(): void
    {
        $this->expectException(\RuntimeException::class);

        DiscoverModulePath::fromServiceProvider(
            TestCase::class,
        );
    }

    #[Test]
    public function it_throws_class_does_not_exist(): void
    {
        $this->expectException(\ReflectionException::class);

        DiscoverModulePath::fromServiceProvider(
            'invalid',
        );
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
