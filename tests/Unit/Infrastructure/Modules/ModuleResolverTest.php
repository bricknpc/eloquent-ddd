<?php

declare(strict_types=1);

namespace BrickNPC\EloquentDDD\Tests\Unit\Infrastructure\Modules;

use PHPUnit\Framework\Attributes\Test;
use BrickNPC\EloquentDDD\Tests\TestCase;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\Attributes\CoversClass;
use BrickNPC\EloquentDDD\Domain\Models\DomainModel;
use App\Users\Infrastructure\Providers\UserServiceProvider;
use BrickNPC\EloquentDDD\Infrastructure\Modules\ModuleContext;
use BrickNPC\EloquentDDD\Infrastructure\Modules\ModuleResolver;

/**
 * @internal
 */
#[CoversClass(ModuleResolver::class)]
#[UsesClass(ModuleContext::class)]
class ModuleResolverTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        require_once __DIR__ . '/../../../Fixtures/App/Users/Infrastructure/Providers/UserServiceProvider.php';
    }

    #[Test]
    public function it_resolves_module_context_from_service_provider(): void
    {
        $context = ModuleResolver::fromServiceProvider(
            UserServiceProvider::class,
        );

        $this->assertInstanceOf(ModuleContext::class, $context);

        $this->assertSame('Users', $context->name);
        $this->assertSame('App\Users', $context->baseNamespace);
        $this->assertStringContainsString('App/Users', $context->basePath);
    }

    #[Test]
    public function it_allows_overriding_name_namespace_and_path(): void
    {
        $context = ModuleResolver::fromServiceProvider(
            UserServiceProvider::class,
            name: 'Custom',
            baseNamespace: 'Custom\Namespace',
            basePath: '/custom/path',
        );

        $this->assertSame('Custom', $context->name);
        $this->assertSame('Custom\Namespace', $context->baseNamespace);
        $this->assertSame('/custom/path', $context->basePath);
    }

    #[Test]
    public function it_throws_when_class_does_not_exist(): void
    {
        $this->expectException(\RuntimeException::class);

        ModuleResolver::fromServiceProvider('NonExistent\ClassName');
    }

    #[Test]
    public function it_throws_when_namespace_does_not_contain_infrastructure(): void
    {
        $this->expectException(\RuntimeException::class);

        ModuleResolver::fromServiceProvider(
            DomainModel::class,
        );
    }

    #[Test]
    public function it_throws_when_path_cannot_be_resolved(): void
    {
        $this->expectException(\RuntimeException::class);

        // stdClass exists but has no meaningful filesystem structure
        ModuleResolver::fromServiceProvider(
            \stdClass::class,
        );
    }
}
