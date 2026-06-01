<?php

declare(strict_types=1);

namespace BrickNPC\EloquentDDD\Domain;

function assert_named_arguments(?string $method = null, mixed ...$arguments): void
{
    if (\array_is_list($arguments)) {
        throw new \InvalidArgumentException(sprintf('%s only supports named arguments.', $method ?? 'This method'));
    }
}

function path(string ...$segments): string
{
    return collect($segments)
        ->map(fn (string $segment): string => rtrim($segment, DIRECTORY_SEPARATOR))
        ->implode(DIRECTORY_SEPARATOR)
        . DIRECTORY_SEPARATOR
    ;
}
