<?php

declare(strict_types=1);

namespace BrickNPC\EloquentDDD\Tests\Unit\Infrastructure\Modules;

use PHPUnit\Framework\Attributes\Test;
use BrickNPC\EloquentDDD\Tests\TestCase;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesFunction;
use BrickNPC\EloquentDDD\Infrastructure\Modules\ModulePaths;
use BrickNPC\EloquentDDD\Infrastructure\Modules\ModuleContext;

/**
 * @internal
 */
#[CoversClass(ModulePaths::class)]
#[UsesClass(ModuleContext::class)]
#[UsesFunction('BrickNPC\EloquentDDD\Domain\path')]
class ModulePathsTest extends TestCase
{
    private ModuleContext $context;

    protected function setUp(): void
    {
        parent::setUp();

        $this->context = new ModuleContext(
            name: 'Users',
            baseNamespace: 'App\Users',
            basePath: '/app/Users',
        );
    }

    #[Test]
    public function it_resolves_module_paths(): void
    {
        $this->assertSame($this->expected('Infrastructure', 'Database', 'Migrations'), ModulePaths::migrations($this->context));
        $this->assertSame($this->expected('Infrastructure', 'Config'), ModulePaths::config($this->context));
        $this->assertSame($this->expected('Application', 'Resources', 'Lang'), ModulePaths::translations($this->context));
        $this->assertSame($this->expected('Application', 'Http', 'Routes'), ModulePaths::routes($this->context));
        $this->assertSame($this->expected('Application', 'Resources', 'Views'), ModulePaths::views($this->context));
        $this->assertSame($this->expected('Application', 'Resources', 'Components'), ModulePaths::components($this->context));
    }

    private function expected(string ...$segments): string
    {
        return implode(DIRECTORY_SEPARATOR, [
            '/app/Users',
            ...$segments,
        ]) . DIRECTORY_SEPARATOR;
    }
}
