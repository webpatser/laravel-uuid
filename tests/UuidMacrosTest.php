<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;
use Webpatser\LaravelUuid\UuidMacros;
use Illuminate\Support\Str;

class UuidMacrosTest extends TestCase
{
    protected function setUp(): void
    {
        // Register the macros for testing
        UuidMacros::register();
    }

    public function testStrFastUuidMacro(): void
    {
        $uuid = Str::fastUuid();
        
        $this->assertIsString($uuid);
        $this->assertEquals(36, strlen($uuid));
        $this->assertTrue(Str::fastIsUuid($uuid));
        $this->assertEquals(4, Str::uuidVersion($uuid));
    }

    public function testStrFastOrderedUuidMacro(): void
    {
        $uuid = Str::fastOrderedUuid();
        
        $this->assertIsString($uuid);
        $this->assertEquals(36, strlen($uuid));
        $this->assertTrue(Str::fastIsUuid($uuid));
        $this->assertEquals(7, Str::uuidVersion($uuid));
    }

    public function testStrFastIsUuidMacro(): void
    {
        $validUuid = '550e8400-e29b-41d4-a716-446655440000';
        $invalidUuid = 'not-a-uuid';
        
        $this->assertTrue(Str::fastIsUuid($validUuid));
        $this->assertFalse(Str::fastIsUuid($invalidUuid));
    }

    public function testAdditionalUuidMacros(): void
    {
        // Test time-based UUID
        $timeUuid = Str::timeBasedUuid();
        $this->assertTrue(Str::isUuid($timeUuid));
        $this->assertEquals(1, Str::uuidVersion($timeUuid));
        $this->assertNotNull(Str::uuidTimestamp($timeUuid));

        // Test reordered time UUID
        $reorderedTimeUuid = Str::reorderedTimeUuid();
        $this->assertTrue(Str::isUuid($reorderedTimeUuid));
        $this->assertEquals(6, Str::uuidVersion($reorderedTimeUuid));

        // Test custom UUID
        $customUuid = Str::customUuid('test-data');
        $this->assertTrue(Str::isUuid($customUuid));
        $this->assertEquals(8, Str::uuidVersion($customUuid));

        // Same custom data should produce same UUID
        $customUuid2 = Str::customUuid('test-data');
        $this->assertEquals($customUuid, $customUuid2);
    }

    public function testNameBasedUuids(): void
    {
        $name = 'example.com';
        
        // Test MD5 name-based UUID
        $md5Uuid = Str::nameUuidMd5($name);
        $this->assertTrue(Str::isUuid($md5Uuid));
        $this->assertEquals(3, Str::uuidVersion($md5Uuid));
        
        // Same name should produce same UUID
        $md5Uuid2 = Str::nameUuidMd5($name);
        $this->assertEquals($md5Uuid, $md5Uuid2);

        // Test SHA-1 name-based UUID
        $sha1Uuid = Str::nameUuidSha1($name);
        $this->assertTrue(Str::isUuid($sha1Uuid));
        $this->assertEquals(5, Str::uuidVersion($sha1Uuid));
        
        // Same name should produce same UUID
        $sha1Uuid2 = Str::nameUuidSha1($name);
        $this->assertEquals($sha1Uuid, $sha1Uuid2);
    }

    public function testNilUuidMacros(): void
    {
        $nil = Str::nilUuid();
        
        $this->assertEquals('00000000-0000-0000-0000-000000000000', $nil);
        $this->assertTrue(Str::isNilUuid($nil));
        $this->assertFalse(Str::isNilUuid(Str::fastUuid()));
    }

    public function testUuidVersionMacro(): void
    {
        $this->assertEquals(4, Str::uuidVersion(Str::fastUuid()));
        $this->assertEquals(7, Str::uuidVersion(Str::fastOrderedUuid()));
        $this->assertNull(Str::uuidVersion('invalid-uuid'));
    }

    public function testUuidTimestampMacro(): void
    {
        $timeUuid = Str::timeBasedUuid();
        $orderedUuid = Str::fastOrderedUuid();
        $randomUuid = Str::fastUuid();
        
        $this->assertIsFloat(Str::uuidTimestamp($timeUuid));
        $this->assertIsFloat(Str::uuidTimestamp($orderedUuid));
        $this->assertNull(Str::uuidTimestamp($randomUuid)); // V4 has no timestamp
        $this->assertNull(Str::uuidTimestamp('invalid-uuid'));
    }

    public function testBenchmarkMacro(): void
    {
        $result = Str::benchmarkUuid(100, 7);
        
        $this->assertIsArray($result);
        $this->assertArrayHasKey('version', $result);
        $this->assertArrayHasKey('iterations', $result);
        $this->assertArrayHasKey('uuids_per_second', $result);
        $this->assertEquals(7, $result['version']);
        $this->assertEquals(100, $result['iterations']);
        $this->assertGreaterThan(0, $result['uuids_per_second']);
    }

    public function testOrderedUuidsAreSortable(): void
    {
        $uuids = [];
        
        // Generate multiple ordered UUIDs with small delays
        for ($i = 0; $i < 5; $i++) {
            if ($i > 0) {
                usleep(1000); // 1ms delay
            }
            $uuids[] = Str::fastOrderedUuid();
        }
        
        $sortedUuids = $uuids;
        sort($sortedUuids);
        
        // V7 UUIDs should be naturally sortable
        $this->assertEquals($uuids, $sortedUuids);
    }
}