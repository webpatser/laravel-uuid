<?php

declare(strict_types=1);

namespace Tests;

use Illuminate\Database\Eloquent\Model;
use PHPUnit\Framework\TestCase;
use Webpatser\LaravelUuid\HasUuids;
use Webpatser\Uuid\Uuid;

class HasUuidsTraitTest extends TestCase
{
    private TestModel $model;

    protected function setUp(): void
    {
        $this->model = new TestModel;
    }

    public function test_new_unique_id_generates_v7_uuid(): void
    {
        $uuid = $this->model->newUniqueId();

        $this->assertIsString($uuid);
        $this->assertEquals(36, strlen($uuid));
        $this->assertTrue(Uuid::validate($uuid));
        $this->assertEquals(7, Uuid::import($uuid)->version);
    }

    public function test_is_valid_unique_id(): void
    {
        $validUuid = '550e8400-e29b-41d4-a716-446655440000';
        $invalidUuid = 'not-a-uuid';

        $this->assertTrue($this->model->testIsValidUniqueId($validUuid));
        $this->assertFalse($this->model->testIsValidUniqueId($invalidUuid));
    }

    public function test_helper_methods(): void
    {
        // Test random UUID helper
        $randomUuid = $this->model->newRandomUuid();
        $this->assertEquals(4, Uuid::import($randomUuid)->version);

        // Test ordered UUID helper
        $orderedUuid = $this->model->newOrderedUuid();
        $this->assertEquals(7, Uuid::import($orderedUuid)->version);

        // Test time-based UUID helper
        $timeUuid = $this->model->newTimeBasedUuid();
        $this->assertEquals(1, Uuid::import($timeUuid)->version);
    }

    public function test_get_uuid_version_with_valid_uuid(): void
    {
        // Set a valid UUID as the primary key
        $this->model->id = Uuid::v4()->string;
        $this->assertEquals(4, $this->model->getUuidVersion());

        $this->model->id = Uuid::v7()->string;
        $this->assertEquals(7, $this->model->getUuidVersion());
    }

    public function test_get_uuid_version_with_invalid_uuid(): void
    {
        $this->model->id = 'invalid-uuid';
        $this->assertNull($this->model->getUuidVersion());
    }

    public function test_get_uuid_version_with_null_id(): void
    {
        $this->model->id = null;
        $this->assertNull($this->model->getUuidVersion());
    }

    public function test_uses_ordered_uuids(): void
    {
        // Test with V7 UUID (ordered)
        $this->model->id = Uuid::v7()->string;
        $this->assertTrue($this->model->usesOrderedUuids());

        // Test with V4 UUID (not ordered)
        $this->model->id = Uuid::v4()->string;
        $this->assertFalse($this->model->usesOrderedUuids());
    }

    public function test_get_uuid_timestamp(): void
    {
        // Test with time-based UUID (V1)
        $timeUuid = Uuid::generate(1);
        $this->model->id = $timeUuid->string;
        $this->assertIsFloat($this->model->getUuidTimestamp());
        $this->assertNotNull($this->model->getUuidTimestamp());

        // Test with ordered UUID (V7)
        $orderedUuid = Uuid::v7();
        $this->model->id = $orderedUuid->string;
        $this->assertIsFloat($this->model->getUuidTimestamp());
        $this->assertNotNull($this->model->getUuidTimestamp());

        // Test with random UUID (V4 - no timestamp)
        $randomUuid = Uuid::v4();
        $this->model->id = $randomUuid->string;
        $this->assertNull($this->model->getUuidTimestamp());
    }

    public function test_generated_uuids_are_different(): void
    {
        $uuid1 = $this->model->newUniqueId();
        $uuid2 = $this->model->newUniqueId();

        $this->assertNotEquals($uuid1, $uuid2);
    }

    public function test_ordered_uuids_are_sortable(): void
    {
        $uuids = [];

        // Generate multiple UUIDs with small delays
        for ($i = 0; $i < 5; $i++) {
            if ($i > 0) {
                usleep(1000); // 1ms delay
            }
            $uuids[] = $this->model->newOrderedUuid();
        }

        $sortedUuids = $uuids;
        sort($sortedUuids);

        // V7 UUIDs should be naturally sortable
        $this->assertEquals($uuids, $sortedUuids);
    }
}

// Test model class for testing the trait
class TestModel extends Model
{
    use HasUuids;

    protected $table = 'test_models';

    // Helper method to test protected method
    public function testIsValidUniqueId($value): bool
    {
        return $this->isValidUniqueId($value);
    }
}
