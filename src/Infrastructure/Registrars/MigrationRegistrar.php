<?php

declare(strict_types=1);

namespace BrickNPC\EloquentDDD\Infrastructure\Registrars;

use Illuminate\Database\Migrations\Migrator;

final readonly class MigrationRegistrar
{
    public function __construct(
        private Migrator $migrator,
    ) {}

    public function __invoke(string $path): void
    {
        $this->migrator->path($path);
    }
}
