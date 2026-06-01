<?php

declare(strict_types=1);

namespace BrickNPC\EloquentDDD\Infrastructure\Registrars;

use Illuminate\Contracts\Config\Repository;

use function BrickNPC\EloquentDDD\Domain\path;

final readonly class ConfigRegistrar
{
    public function __construct(
        private Repository $repository,
        private string $configPath,
        private string $module,
    ) {}

    public function __invoke(string ...$files): void
    {
        $configPath            = path($this->configPath, 'Infrastructure', 'Config');
        $defaultConfigFilename = str($this->module)->kebab()->toString();

        foreach ($files as $file) {
            $filename = realpath($configPath . $file);

            if ($filename === false || !file_exists($filename)) {
                throw new \RuntimeException(sprintf('Config file %s not found', $filename));
            }

            $key = str($file)->replace('.php', '')->explode('/')->join('.');

            if ($key !== $defaultConfigFilename) {
                $key = $defaultConfigFilename . '.' . $key;
            }

            $key = strtolower($key);

            /** @var array<string, mixed> $fileContents */
            $fileContents = require $filename;

            /** @var array<string, mixed> $existingConfig */
            $existingConfig = $this->repository->get($key, []);

            $this->repository->set($key, array_merge($fileContents, $existingConfig));
        }
    }
}
