<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;
use Webpatser\LaravelUuid\UuidCast;
use Webpatser\Uuid\Uuid;
use Illuminate\Database\Eloquent\Model;

class UuidCastTest extends TestCase
{
    private UuidCast $cast;
    private Model $model;

    protected function setUp(): void
    {
        $this->cast = new UuidCast();
        $this->model = new class extends Model {
            protected $table = 'test';
        };
    }

    public function testGetWithNullValue(): void
    {
        $result = $this->cast->get($this->model, 'id', null, []);
        $this->assertNull($result);
    }

    public function testGetWithUuidInstance(): void
    {
        $uuid = Uuid::v4();
        $result = $this->cast->get($this->model, 'id', $uuid, []);
        $this->assertSame($uuid, $result);
    }

    public function testGetWithValidString(): void
    {
        $uuidString = '550e8400-e29b-41d4-a716-446655440000';
        $result = $this->cast->get($this->model, 'id', $uuidString, []);
        
        $this->assertInstanceOf(Uuid::class, $result);
        $this->assertEquals($uuidString, $result->string);
    }

    public function testSetWithNullValue(): void
    {
        $result = $this->cast->set($this->model, 'id', null, []);
        $this->assertNull($result);
    }

    public function testSetWithUuidInstance(): void
    {
        $uuid = Uuid::v4();
        $result = $this->cast->set($this->model, 'id', $uuid, []);
        $this->assertEquals($uuid->string, $result);
    }

    public function testSetWithValidString(): void
    {
        $uuidString = '550e8400-e29b-41d4-a716-446655440000';
        $result = $this->cast->set($this->model, 'id', $uuidString, []);
        $this->assertEquals($uuidString, $result);
    }

    public function testSetWithInvalidStringThrowsException(): void
    {
        $this->expectException(\TypeError::class);
        $this->cast->set($this->model, 'id', 'invalid-uuid', []);
    }

    public function testRoundTrip(): void
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