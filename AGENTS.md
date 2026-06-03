# AI Agent Instructions

Use this file as the working guide for AI coding agents in this repository.

## Project Overview

Eloquent DDD is a PHP package for building Laravel applications with a Domain Driven Design style architecture. The 
package is distributed as `bricknpc/eloquent-ddd` and targets PHP `^8.5` with Laravel Illuminate components `^12.0|^13.0`.

The package source is in `src/` using the `BrickNPC\EloquentDDD\` namespace. Tests are in `tests/` using the 
`BrickNPC\EloquentDDD\Tests\` namespace. Documentation lives in `docs/` and is a Docusaurus site.

## Architecture Boundaries

Respect the Deptrac layer rules in `deptrac.yaml`:

- `Domain` may depend only on vendor code.
- `Application` may depend on `Domain` and vendor code.
- `Infrastructure` may depend on `Application`, `Domain`, and vendor code.

Do not introduce dependencies from `Domain` to `Application` or `Infrastructure`. Keep framework integration, 
service providers, routing, configuration, and module discovery in `Infrastructure` unless an existing pattern clearly 
says otherwise.

## Development Workflow

Prefer running project commands through Docker, as documented in `readme.md`:

```bash
docker compose up -d
docker compose exec php composer install
docker compose exec php composer test
docker compose exec php composer cs
docker compose exec php composer ps
docker compose exec php composer dt
```

The Composer shortcuts are:

- `composer cs` for PHP CS Fixer.
- `composer ps` for PHPStan.
- `composer dt` for Deptrac.
- `composer test` for PHPUnit with coverage output.
- `composer commit` or `composer c` for the combined quality checks except tests.

If Docker is not available in the current environment, explain that clearly and run the closest local Composer command 
only when dependencies and PHP are available.

## Testing Expectations

Add or update PHPUnit tests for behaviour changes. Unit tests belong under `tests/Unit/`; integration-style package 
behaviour can go under `tests/Feature/`.

When fixing a bug, include a failing test that captures the bug before or alongside the fix. The README states that 
pull requests require 100% test coverage, so avoid adding untested public behaviour.

## Coding Style

Follow `.php-cs-fixer.php`:

- Use strict types in PHP files.
- Follow PSR-12 and the configured PhpCsFixer rules.
- Use short array syntax.
- Keep imports ordered by the configured fixer.
- PHPUnit test method names should be snake_case.
- PHPUnit test method names must not be prefixed with `test`. Instead use the `#[Test]` attribute.
- ALl test classes must extend `BrickNPC\EloquentDDD\Tests\TestCase`.
- All test classes should use the `#[CoversClass]` or similar attribute to indicate what classes they cover.
- All test classes should use the `#[UsesClass]` or similar attribute to indicate what classes they use but do no explicitly test.

Keep changes focused. Do not reformat unrelated files, regenerate caches, or modify generated/vendor files.

## Documentation

When adding or changing public behaviour, update the Docusaurus documentation in `docs/` in the same change. Do not 
split code and documentation updates unless the user explicitly asks for that.

The docs site is available through Docker at `http://localhost:3000/eloquent-ddd` when the stack is running.

## Repository Hygiene

- Do not edit `vendor/`, `.phpunit.cache/`, `.idea/`, `.deptrac.cache`, or `.php-cs-fixer.cache`.
- Avoid changing `composer.lock` unless dependencies actually change.
- Preserve existing public APIs unless the user asks for a breaking change.
- Before making broad changes, inspect nearby code and tests for established patterns.
- If the worktree already has unrelated changes, leave them alone.

## Useful Files

- `composer.json`: package metadata, autoloading, and Composer scripts.
- `phpunit.xml`: PHPUnit configuration.
- `phpstan.neon`: PHPStan configuration.
- `deptrac.yaml`: architecture dependency rules.
- `.php-cs-fixer.php`: code style rules.
- `readme.md`: user-facing setup and contribution guidance.
