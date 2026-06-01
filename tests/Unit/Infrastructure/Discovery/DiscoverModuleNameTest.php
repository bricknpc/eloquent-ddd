<?php

declare(strict_types=1);

namespace BrickNPC\EloquentDDD\Tests\Unit\Infrastructure\Discovery;

use PHPUnit\Framework\Attributes\Test;
use BrickNPC\EloquentDDD\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use BrickNPC\EloquentDDD\Infrastructure\Discovery\DiscoverModuleName;

/**
 * @internal
 */
#[CoversClass(DiscoverModuleName::class)]
class DiscoverModuleNameTest extends TestCase
{
    #[Test]
    public function it_extracts_module_name_from_service_provider_namespace(): void
    {
        $result = DiscoverModuleName::fromServiceProvider(
            'App\Users\Infrastructure\Providers\UserServiceProvider',
        );

        $this->assertSame('Users', $result);
    }

    #[Test]
    public function it_extracts_module_name_when_nested_inside_sub_namespace(): void
    {
        $result = DiscoverModuleName::fromServiceProvider(
            'Modules\Users\Infrastructure\Providers\Subspace\UserServiceProvider',
        );

        $this->assertSame('Users', $result);
    }

    #[Test]
    public function it_extracts_module_name_when_app_modules_structure_is_used(): void
    {
        $result = DiscoverModuleName::fromServiceProvider(
            'App\Modules\Users\Infrastructure\Providers\UserServiceProvider',
        );

        $this->assertSame('Users', $result);
    }

    #[Test]
    public function it_throws_exception_when_infrastructure_segment_is_missing(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Could not determine module name from service provider');

        DiscoverModuleName::fromServiceProvider(
            'App\Users\Providers\UserServiceProvider',
        );
    }

    #[Test]
    public function it_throws_exception_when_infrastructure_is_first_segment(): void
    {
        $this->expectException(\RuntimeException::class);

        DiscoverModuleName::fromServiceProvider(
            'Infrastructure\Providers\UserServiceProvider',
        );
    }

    #[Test]
    public function it_handles_deeply_nested_service_providers(): void
    {
        $result = DiscoverModuleName::fromServiceProvider(
            'App\Modules\Billing\Infrastructure\Providers\Http\V2\UserServiceProvider',
        );

        $this->assertSame('Billing', $result);
    }
}
