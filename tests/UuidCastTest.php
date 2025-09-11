<?php

declare(strict_types=1);

namespace Tests;

use Illuminate\Database\Eloquent\Model;
use PHPUnit\Framework\TestCase;
use Webpatser\LaravelUuid\UuidCast;
use Webpatser\Uuid\Uuid;

class UuidCastTest extends TestCase
{
    private UuidCast $cast;

    private Model $model;

    protected function setUp(): void
    {
        $this->cast = new UuidCast;
        $this->model = new class extends Model
        {
            protected $table = 'test';
        };
    }

    public function test_get_with_null_value(): void
    {
        $result = $this->cast->get($this->model, 'id', null, []);
        $this->assertNull($result);
    }

    public function test_get_with_uuid_instance(): void
    {
        $uuid = Uuid::v4();
        $result = $this->cast->get($this->model, 'id', $uuid, []);
        $this->assertSame($uuid, $result);
    }

    public function test_get_with_valid_string(): void
    {
        $uuidString = '550e8400-e29b-41d4-a716-446655440000';
        $result = $this->cast->get($this->model, 'id', $uuidString, []);

        $this->assertInstanceOf(Uuid::class, $result);
        $this->assertEquals($uuidString, $result->string);
    }

    public function test_set_with_null_value(): void
    {
        $result = $this->cast->set($this->model, 'id', null, []);
        $this->assertNull($result);
    }

    public function test_set_with_uuid_instance(): void
    {
        $uuid = Uuid::v4();
        $result = $this->cast->set($this->model, 'id', $uuid, []);
        $this->assertEquals($uuid->string, $result);
    }

    public function test_set_with_valid_string(): void
    {
        $uuidString = '550e8400-e29b-41d4-a716-446655440000';
        $result = $this->cast->set($this->model, 'id', $uuidString, []);
        $this->assertEquals($uuidString, $result);
    }

    public function test_set_with_invalid_string_throws_exception(): void
    {
        $this->expectException(\TypeError::class);
        $this->cast->set($this->model, 'id', 'invalid-uuid', []);
    }

    public function test_round_trip(): void
    {
        // Test that we can set and get back the same UUID
        $originalUuid = Uuid::v4();

        // Set (convert UUID to string for storage)
        $storedValue = $this->cast->set($this->model, 'id', $originalUuid, []);
        $this->assertIsString($storedValue);

        // Get (convert string back to UUID)
        $retrievedUuid = $this->cast->get($this->model, 'id', $storedValue, []);
        $this->assertInstanceOf(Uuid::class, $retrievedUuid);
        $this->assertEquals($originalUuid->string, $retrievedUuid->string);
    }
}
