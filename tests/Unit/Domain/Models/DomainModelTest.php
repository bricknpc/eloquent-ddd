<?php

declare(strict_types=1);

namespace BrickNPC\EloquentDDD\Tests\Unit\Domain\Models;

use PHPUnit\Framework\Attributes\Test;
use BrickNPC\EloquentDDD\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesFunction;
use BrickNPC\EloquentDDD\Domain\Models\DomainModel;

/**
 * @internal
 */
#[CoversClass(DomainModel::class)]
#[UsesFunction('BrickNPC\EloquentDDD\Domain\assert_named_arguments')]
class DomainModelTest extends TestCase
{
    #[Test]
    public function it_fills_attributes_from_named_arguments(): void
    {
        $model = new TestDomainModel();

        $this->assertSame($model, $model->fill(name: 'Taylor', email: 'taylor@example.com'));
        $this->assertSame('Taylor', $model->readAttribute('name'));
        $this->assertSame('taylor@example.com', $model->readAttribute('email'));
    }

    #[Test]
    public function it_rejects_positional_arguments_when_filling(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(DomainModel::class . '::fill only supports named arguments.');

        (new TestDomainModel())->fill(['name' => 'Taylor']);
    }

    #[Test]
    public function it_creates_models_from_named_arguments(): void
    {
        $model = TestDomainModel::create(name: 'Taylor');

        $this->assertSame('Taylor', $model->readAttribute('name'));
        $this->assertTrue($model->saved);
    }

    #[Test]
    public function it_rejects_positional_arguments_when_creating(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(DomainModel::class . '::create only supports named arguments.');

        TestDomainModel::create(['name' => 'Taylor']);
    }

    #[Test]
    public function it_exposes_protected_attribute_helpers_to_subclasses(): void
    {
        $model = new TestDomainModel();

        $model->writeAttribute('name', 'Taylor');

        $this->assertSame('Taylor', $model->readAttribute('name'));
    }
}

final class TestDomainModel extends DomainModel
{
    public bool $saved = false;
    protected $guarded = [];

    public function __construct() {}

    public function save(array $options = []): bool
    {
        $this->saved = true;

        return true;
    }

    public function writeAttribute(string $key, mixed $value): void
    {
        $this->set($key, $value);
    }

    public function readAttribute(string $key): mixed
    {
        return $this->get($key);
    }
}
