<?php

declare(strict_types=1);

namespace BrickNPC\EloquentDDD\Tests\Unit\Infrastructure\Registrars;

use PHPUnit\Framework\Attributes\Test;
use BrickNPC\EloquentDDD\Tests\TestCase;
use Illuminate\Database\Migrations\Migrator;
use PHPUnit\Framework\Attributes\CoversClass;
use BrickNPC\EloquentDDD\Infrastructure\Registrars\MigrationRegistrar;

/**
 * @internal
 */
#[CoversClass(MigrationRegistrar::class)]
class MigrationRegistrarTest extends TestCase
{
    #[Test]
    public function it_registers_migration_path_on_migrator(): void
    {
        $path = '/app/Modules/Users/Infrastructure/Database/Migrations';

        $migrator = \Mockery::mock(Migrator::class);

        $migrator
            ->shouldReceive('path')
            ->once()
            ->with($path)
            ->andReturnSelf()
        ;

        $registrar = new MigrationRegistrar($migrator);

        $registrar($path);

        $this->addToAssertionCount(1);
    }

    #[Test]
    public function it_allows_multiple_registrations_if_called_multiple_times(): void
    {
        $migrator = \Mockery::mock(Migrator::class);

        $migrator
            ->shouldReceive('path')
            ->twice()
            ->withArgs(function ($path) {
                return str_contains($path, 'Infrastructure');
            })
            ->andReturnSelf()
        ;

        $registrar = new MigrationRegistrar($migrator);

        $registrar('/modules/users/Infrastructure/Database/Migrations');
        $registrar('/modules/billing/Infrastructure/Database/Migrations');

        $this->addToAssertionCount(1);
    }
}
