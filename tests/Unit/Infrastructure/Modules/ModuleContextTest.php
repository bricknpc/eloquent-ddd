<?php

declare(strict_types=1);

namespace BrickNPC\EloquentDDD\Tests\Unit\Infrastructure\Modules;

use PHPUnit\Framework\Attributes\Test;
use BrickNPC\EloquentDDD\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use BrickNPC\EloquentDDD\Infrastructure\Modules\ModuleContext;

/**
 * @internal
 */
#[CoversClass(ModuleContext::class)]
class ModuleContextTest extends TestCase
{
    #[Test]
    public function it_stores_module_context_properties(): void
    {
        $context = new ModuleContext(
            name: 'Users',
            baseNamespace: 'App\Users',
            basePath: '/app/Users',
        );

        $this->assertSame('Users', $context->name);
        $this->assertSame('App\Users', $context->baseNamespace);
        $this->assertSame('/app/Users', $context->basePath);
    }

    #[Test]
    public function it_generates_view_namespace_from_module_name(): void
    {
        $context = new ModuleContext(
            name: 'Users',
            baseNamespace: 'App\Users',
            basePath: '/app/Users',
        );

        $this->assertSame('users', $context->viewNamespace);
    }

    #[Test]
    public function it_converts_studly_module_names_to_snake_case_view_namespace(): void
    {
        $context = new ModuleContext(
            name: 'UserManagement',
            baseNamespace: 'App\UserManagement',
            basePath: '/app/UserManagement',
        );

        $this->assertSame('user_management', $context->viewNamespace);
    }

    #[Test]
    public function it_converts_multi_word_module_names_to_snake_case_view_namespace(): void
    {
        $context = new ModuleContext(
            name: 'BillingSystem',
            baseNamespace: 'App\BillingSystem',
            basePath: '/app/BillingSystem',
        );

        $this->assertSame('billing_system', $context->viewNamespace);
    }
}
