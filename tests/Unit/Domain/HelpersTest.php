<?php

declare(strict_types=1);

namespace BrickNPC\EloquentDDD\Tests\Unit\Domain;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\CoversFunction;

use function BrickNPC\EloquentDDD\Domain\path;
use function BrickNPC\EloquentDDD\Domain\assert_named_arguments;

/**
 * @internal
 */
#[CoversFunction('BrickNPC\EloquentDDD\Domain\assert_named_arguments')]
#[CoversFunction('BrickNPC\EloquentDDD\Domain\path')]
class HelpersTest extends TestCase
{
    #[Test]
    public function it_accepts_named_arguments(): void
    {
        $this->expectNotToPerformAssertions();

        assert_named_arguments('TestMethod', name: 'Taylor');
    }

    #[Test]
    public function it_rejects_positional_arguments(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('TestMethod only supports named arguments.');

        assert_named_arguments('TestMethod', 'Taylor');
    }

    #[Test]
    public function it_uses_a_default_method_name_when_rejecting_positional_arguments(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('This method only supports named arguments.');

        assert_named_arguments(null, 'Taylor');
    }

    #[Test]
    public function it_builds_a_directory_path_from_segments(): void
    {
        $this->assertSame(
            'base' . DIRECTORY_SEPARATOR . 'Nested' . DIRECTORY_SEPARATOR . 'File' . DIRECTORY_SEPARATOR,
            path('base' . DIRECTORY_SEPARATOR, 'Nested', 'File'),
        );
    }
}
