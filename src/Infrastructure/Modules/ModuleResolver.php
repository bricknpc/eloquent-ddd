<?php

declare(strict_types=1);

namespace BrickNPC\EloquentDDD\Infrastructure\Modules;

use BrickNPC\EloquentDDD\Infrastructure\Dto\ModuleContext;

final readonly class ModuleResolver
{
    private const string NAMESPACE_KEYWORD = 'Infrastructure';

    /**
     * @param class-string $serviceProvider
     *
     * @throws \RuntimeException
     */
    public static function fromServiceProvider(
        string $serviceProvider,
        ?string $name = null,
        ?string $baseNamespace = null,
        ?string $basePath = null,
    ): ModuleContext {
        try {
            $reflection = new \ReflectionClass($serviceProvider);

            $file = $reflection->getFileName();
        } catch (\ReflectionException $exception) {
            throw new \RuntimeException(
                message: sprintf('Cannot resolve module from service provider: %s', $serviceProvider),
                previous: $exception,
            );
        }

        if ($file === false) {
            throw new \RuntimeException(
                sprintf('Cannot resolve module from service provider: %s', $serviceProvider),
            );
        }

        $resolver = new self();

        return new ModuleContext(
            name: $name                   ?? $resolver->resolveName($serviceProvider),
            baseNamespace: $baseNamespace ?? $resolver->resolveNamespace($serviceProvider),
            basePath: $basePath           ?? $resolver->resolvePath($serviceProvider, $file),
        );
    }

    /**
     * @param class-string $serviceProvider
     */
    private function resolveName(string $serviceProvider): string
    {
        $parts = explode('\\', $serviceProvider);

        $index = $this->findKeywordIndex($parts);

        return $parts[$index - 1];
    }

    /**
     * @param class-string $serviceProvider
     */
    private function resolveNamespace(string $serviceProvider): string
    {
        $parts = explode('\\', $serviceProvider);

        $index = $this->findKeywordIndex($parts);

        return implode('\\', array_slice($parts, 0, $index));
    }

    private function resolvePath(string $serviceProvider, string $filePath): string
    {
        $parts = explode(DIRECTORY_SEPARATOR, $filePath);

        $index = $this->findKeywordIndex($parts);

        return implode(DIRECTORY_SEPARATOR, array_slice($parts, 0, $index));
    }

    /**
     * @param array<int, string> $parts
     */
    private function findKeywordIndex(array $parts): int
    {
        $index = array_search(self::NAMESPACE_KEYWORD, $parts, true);

        if ($index === false || $index === 0) {
            throw new \RuntimeException(
                sprintf('Cannot resolve module context: %s', implode('\\', $parts)),
            );
        }

        return $index;
    }
}
