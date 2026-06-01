<?php

declare(strict_types=1);

namespace BrickNPC\EloquentDDD\Tests\Unit\Infrastructure\Discovery;

use PHPUnit\Framework\Attributes\Test;
use BrickNPC\EloquentDDD\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use BrickNPC\EloquentDDD\Infrastructure\Discovery\DiscoverModuleNamespace;

/**
 * @internal
 */
#[CoversClass(DiscoverModuleNamespace::class)]
class DiscoverModuleNamespaceTest extends TestCase
{
    #[Test]
    public function it_returns_namespace_before_infrastructure_segment(): void
    {
        $result = DiscoverModuleNamespace::fromServiceProvider(
            'App\Users\Infrastructure\Providers\UserServiceProvider',
        );

        $this->assertSame('App\Users', $result);
    }

    #[Test]
    public function it_handles_modules_based_structure(): void
    {
        $result = DiscoverModuleNamespace::fromServiceProvider(
            'Modules\Users\Infrastructure\Providers\UserServiceProvider',
        );

        $this->assertSame('Modules\Users', $result);
    }

    #[Test]
    public function it_handles_app_modules_structure(): void
    {
        $result = DiscoverModuleNamespace::fromServiceProvider(
            'App\Modules\Users\Infrastructure\Providers\UserServiceProvider',
        );

        $this->assertSame('App\Modules\Users', $result);
    }

    #[Test]
    public function it_handles_deeply_nested_provider_namespaces(): void
    {
        $result = DiscoverModuleNamespace::fromServiceProvider(
            'App\Modules\Billing\Infrastructure\Providers\Http\V2\UserServiceProvider',
        );

        $this->assertSame('App\Modules\Billing', $result);
    }

    #[Test]
    public function it_handles_single_root_namespace_before_infrastructure(): void
    {
        $result = DiscoverModuleNamespace::fromServiceProvider(
            'App\Billing\Infrastructure\UserServiceProvider',
        );

        $this->assertSame('App\Billing', $result);
    }

    #[Test]
    public function it_throws_when_infrastructure_is_missing(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Could not determine module namespace');

        DiscoverModuleNamespace::fromServiceProvider(
            'App\Users\Providers\UserServiceProvider',
        );
    }

    #[Test]
    public function it_throws_when_infrastructure_is_first_segment(): void
    {
        $this->expectException(\RuntimeException::class);

        DiscoverModuleNamespace::fromServiceProvider(
            'Infrastructure\Providers\UserServiceProvider',
        );
    }
}
