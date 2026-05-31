<?php

declare(strict_types=1);

namespace BrickNPC\EloquentDDD\Domain;

function assert_named_arguments(?string $method = null, mixed ...$arguments): void
{
    if (\array_is_list($arguments)) {
        throw new \InvalidArgumentException(sprintf('%s only supports named arguments.', $method ?? 'This method'));
    }
}
