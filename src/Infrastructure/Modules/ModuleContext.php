<?php

declare(strict_types=1);

namespace BrickNPC\EloquentDDD\Infrastructure\Modules;

final class ModuleContext
{
    public string $viewNamespace {
        get => str($this->name)->snake()->toString();
    }

    public function __construct(
        public readonly string $name,
        public readonly string $baseNamespace,
        public readonly string $basePath,
    ) {}
}
