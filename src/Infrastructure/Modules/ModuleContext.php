<?php

declare(strict_types=1);

namespace BrickNPC\EloquentDDD\Infrastructure\Modules;

final readonly class ModuleContext
{
    public function __construct(
        public string $name,
        public string $baseNamespace,
        public string $basePath,
    ) {}
}
