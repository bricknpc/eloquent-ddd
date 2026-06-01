<?php

declare(strict_types=1);

namespace BrickNPC\EloquentDDD\Infrastructure\Registrars;

use Illuminate\Routing\Router;

use function BrickNPC\EloquentDDD\Domain\path;

final readonly class RoutingRegistrar
{
    public function __construct(
        private Router $router,
        private string $path,
    ) {}

    /**
     * @param null|array<int, string>|string $web
     * @param null|array<int, string>|string $api
     */
    public function __invoke(
        array|string|null $web = null,
        array|string|null $api = null,
        ?string $apiPrefix = null,
    ): void {
        $routesPath = $this->routesPath();

        $this->registerRoutes($routesPath, $api, ['api'], $apiPrefix);
        $this->registerRoutes($routesPath, $web, ['web']);
    }

    /**
     * @param null|array<int, string>|string $routes
     * @param array<int, string>             $middleware
     */
    private function registerRoutes(
        string $routesPath,
        array|string|null $routes,
        array $middleware = [],
        ?string $prefix = null,
    ): void {
        foreach ($this->normalise($routes) as $route) {
            $file = $routesPath . $route;

            if (!is_file($file)) {
                continue;
            }

            $this->router
                ->middleware($middleware)
                ->prefix($prefix) // @phpstan-ignore-line
                ->group($file)
            ;
        }
    }

    private function routesPath(): string
    {
        return path($this->path, 'Application', 'Http', 'Routes');
    }

    /**
     * @param null|array<int, string>|string $value
     *
     * @return array<int, string>
     */
    private function normalise(array|string|null $value): array
    {
        if ($value === null) {
            return [];
        }

        /** @var array<int, string> $result */
        $result = is_array($value) ? $value : [$value];

        return $result;
    }
}
