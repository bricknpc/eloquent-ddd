<?php

declare(strict_types=1);

namespace BrickNPC\EloquentDDD\Domain\Models;

use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\Model;

use function BrickNPC\EloquentDDD\Domain\assert_named_arguments;

use Illuminate\Contracts\Container\Container as ContainerContract;

abstract class DomainModel extends Model
{
    /**
     * @param array<string, mixed> $attributes
     *
     * @throws \InvalidArgumentException when not using named arguments
     */
    public static function create(mixed ...$attributes): static
    {
        assert_named_arguments(__METHOD__, ...$attributes);

        /** @var ContainerContract $container */
        $container = Container::getInstance();

        return tap($container->make(static::class), function (self $model) use ($attributes): static {
            $model->fill(...$attributes);
            $model->save();

            return $model;
        });
    }

    /**
     * @param array<string, mixed> $attributes
     *
     * @throws \InvalidArgumentException when not using named arguments
     */
    public function fill(mixed ...$attributes): static
    {
        assert_named_arguments(__METHOD__, ...$attributes);

        parent::fill($attributes); // @phpstan-ignore-line

        return $this;
    }

    protected function set(string $key, mixed $value): void
    {
        $this->setAttribute($key, $value);
    }

    protected function get(string $key): mixed
    {
        return $this->getAttribute($key);
    }
}
