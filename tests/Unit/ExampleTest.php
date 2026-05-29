<?php

declare(strict_types=1);

namespace BrickNPC\EloquentDDD\Tests\Unit;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\CoversNothing;

/**
 * @internal
 */
#[CoversNothing]
class ExampleTest extends TestCase
{
    #[Test]
    public function is_true(): void
    {
        $this->assertTrue(true);
    }
}
