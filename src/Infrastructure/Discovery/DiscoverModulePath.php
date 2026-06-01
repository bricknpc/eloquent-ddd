<?php

declare(strict_types=1);

namespace BrickNPC\EloquentDDD\Infrastructure\Discovery;

final readonly class DiscoverModulePath
{
    /**
     * @param class-string $serviceProvider
     */
    public static function fromServiceProvider(string $serviceProvider): string
    {
        $path = new \ReflectionClass($serviceProvider)->getFileName();

        if ($path === false) {
            throw new \RuntimeException(
                sprintf('Could not determine module path from service provider: %s', $serviceProvider),
            );
        }

        $segments = collect(explode(DIRECTORY_SEPARATOR, $path));

        $infrastructureIndex = $segments->search('Infrastructure');

        if ($infrastructureIndex === false || $infrastructureIndex === 0) {
            throw new \RuntimeException(
                sprintf('Could not determine module path from service provider: %s', $serviceProvider),
            );
        }

        return $segments
            ->slice(0, $infrastructureIndex) // @phpstan-ignore-line
            ->implode(DIRECTORY_SEPARATOR)
        ;
    }
}
