<?php

declare(strict_types=1);

namespace BrickNPC\EloquentDDD\Infrastructure\Discovery;

final readonly class DiscoverModuleName
{
    /**
     * @param class-string $serviceProvider
     */
    public static function fromServiceProvider(string $serviceProvider): string
    {
        $parts = str($serviceProvider)->explode('\\');

        $infrastructureIndex = $parts->search('Infrastructure');

        if ($infrastructureIndex === false || $infrastructureIndex === 0) {
            throw new \RuntimeException(
                sprintf('Could not determine module name from service provider: %s', $serviceProvider),
            );
        }

        return $parts->get($infrastructureIndex - 1); // @phpstan-ignore-line
    }
}
