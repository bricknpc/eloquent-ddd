<?php

declare(strict_types=1);

namespace BrickNPC\EloquentDDD\Tests;

use Orchestra\Testbench\TestCase as OrchestraTestCase;
use BrickNPC\EloquentDDD\Infrastructure\Providers\EloquentDDDServiceProvider;

abstract class TestCase extends OrchestraTestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            EloquentDDDServiceProvider::class,
        ];
    }
}
