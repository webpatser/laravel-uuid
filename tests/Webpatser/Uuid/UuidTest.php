<?php

use PHPUnit\Framework\TestCase;
use Webpatser\Uuid\Uuid;

class UuidTest extends TestCase
{
    public function test_static_generation()
    {
        $uuid = Uuid::generate(1);
        $this->assertInstanceOf('Webpatser\Uuid\Uuid', $uuid);

        $uuid = Uuid::generate(3, 'example.com', Uuid::NS_DNS);
        $this->assertInstanceOf('Webpatser\Uuid\Uuid', $uuid);

        $uuid = Uuid::generate(4);
        $this->assertInstanceOf('Webpatser\Uuid\Uuid', $uuid);

        $uuid = Uuid::generate(5, 'example.com', Uuid::NS_DNS);
        $this->assertInstanceOf('Webpatser\Uuid\Uuid', $uuid);
    }

    public function test_import_all_zero_uuid()
    {
        $uuid = Uuid::import('00000000-0000-0000-0000-000000000000');
        $this->assertInstanceOf('Webpatser\Uuid\Uuid', $uuid);
        $this->assertEquals('00000000-0000-0000-0000-000000000000', (string) $uuid);
    }

    public function test_generation_of_valid_uuid_via_regex()
    {
        $uuid = Uuid::generate(1);
        $this->assertMatchesRegularExpression('~'.Uuid::VALID_UUID_REGEX.'~', (string) $uuid);

        $uuid = Uuid::generate(3, 'example.com', Uuid::NS_DNS);
        $this->assertMatchesRegularExpression('~'.Uuid::VALID_UUID_REGEX.'~', (string) $uuid);

        $uuid = Uuid::generate(4);
        $this->assertMatchesRegularExpression('~'.Uuid::VALID_UUID_REGEX.'~', (string) $uuid);

        $uuid = Uuid::generate(5, 'example.com', Uuid::NS_DNS);
        $this->assertMatchesRegularExpression('~'.Uuid::VALID_UUID_REGEX.'~', (string) $uuid);
    }

    public function test_generation_of_valid_uuid_via_validator()
    {
        $uuid = Uuid::generate(1);
        $this->assertTrue(Uuid::validate($uuid->string));

        $uuid = Uuid::generate(3, 'example.com', Uuid::NS_DNS);
        $this->assertTrue(Uuid::validate($uuid->string));

        $uuid = Uuid::generate(4);
        $this->assertTrue(Uuid::validate($uuid->string));

        $uuid = Uuid::generate(5, 'example.com', Uuid::NS_DNS);
        $this->assertTrue(Uuid::validate($uuid->string));

        $uuid = Uuid::generate(1);
        $this->assertTrue(Uuid::validate($uuid->bytes));

        $uuid = Uuid::generate(3, 'example.com', Uuid::NS_DNS);
        $this->assertTrue(Uuid::validate($uuid->bytes));

        $uuid = Uuid::generate(4);
        $this->assertTrue(Uuid::validate($uuid->bytes));

        $uuid = Uuid::generate(5, 'example.com', Uuid::NS_DNS);
        $this->assertTrue(Uuid::validate($uuid->bytes));

        $uuid = Uuid::generate(1);
        $this->assertTrue(Uuid::validate($uuid->urn));

        $uuid = Uuid::generate(3, 'example.com', Uuid::NS_DNS);
        $this->assertTrue(Uuid::validate($uuid->urn));

        $uuid = Uuid::generate(4);
        $this->assertTrue(Uuid::validate($uuid->urn));

        $uuid = Uuid::generate(5, 'example.com', Uuid::NS_DNS);
        $this->assertTrue(Uuid::validate($uuid->urn));

        $this->assertTrue(Uuid::validate(Uuid::generate(1)));

        $this->assertTrue(Uuid::validate(Uuid::generate(3, 'example.com', Uuid::NS_DNS)));

        $this->assertTrue(Uuid::validate(Uuid::generate(4)));

        $this->assertTrue(Uuid::validate(Uuid::generate(5, 'example.com', Uuid::NS_DNS)));
    }

    public function test_correct_version_uuid()
    {
        $uuidOne = Uuid::generate(1);
        $this->assertEquals(1, $uuidOne->version);

        $uuidThree = Uuid::generate(3, 'example.com', Uuid::NS_DNS);
        $this->assertEquals(3, $uuidThree->version);

        $uuidFour = Uuid::generate(4);
        $this->assertEquals(4, $uuidFour->version);

        $uuidFive = Uuid::generate(5, 'example.com', Uuid::NS_DNS);
        $this->assertEquals(5, $uuidFive->version);
    }

    public function test_correct_variant_uuid()
    {
        $uuidOne = Uuid::generate(1);
        $this->assertEquals(1, $uuidOne->variant);

        $uuidThree = Uuid::generate(3, 'example.com', Uuid::NS_DNS);
        $this->assertEquals(1, $uuidThree->variant);

        $uuidFour = Uuid::generate(4);
        $this->assertEquals(1, $uuidFour->variant);

        $uuidFive = Uuid::generate(5, 'example.com', Uuid::NS_DNS);
        $this->assertEquals(1, $uuidFive->variant);
    }

    public function test_correct_version_of_imported_uuid()
    {
        $uuidOne = Uuid::generate(1);
        $importedOne = Uuid::import((string) $uuidOne);
        $this->assertEquals($uuidOne->version, $importedOne->version);

        $uuidThree = Uuid::generate(3, 'example.com', Uuid::NS_DNS);
        $importedThree = Uuid::import((string) $uuidThree);
        $this->assertEquals($uuidThree->version, $importedThree->version);

        $uuidFour = Uuid::generate(4);
        $importedFour = Uuid::import((string) $uuidFour);
        $this->assertEquals($uuidFour->version, $importedFour->version);

        $uuidFive = Uuid::generate(5, 'example.com', Uuid::NS_DNS);
        $importedFive = Uuid::import((string) $uuidFive);
        $this->assertEquals($uuidFive->version, $importedFive->version);
    }

    public function test_correct_node_of_generated_uuid()
    {
        $macAdress = Faker\Provider\Internet::macAddress();
        $uuidThree = Uuid::generate(1, $macAdress);
        $this->assertEquals(strtolower(str_replace(':', '', $macAdress)), $uuidThree->node);

        $uuidThree = Uuid::generate(3, $macAdress, Uuid::NS_DNS);
        $this->assertNull($uuidThree->node);

        $uuidThree = Uuid::generate(4, $macAdress);
        $this->assertNull($uuidThree->node);

        $uuidThree = Uuid::generate(5, $macAdress, Uuid::NS_DNS);
        $this->assertNull($uuidThree->node);
    }

    public function test_correct_time_of_imported_uuid()
    {
        $uuidOne = Uuid::generate(1);
        $importedOne = Uuid::import((string) $uuidOne);
        $this->assertEquals($uuidOne->time, $importedOne->time);

        $uuidThree = Uuid::generate(3, 'example.com', Uuid::NS_DNS);
        $importedThree = Uuid::import((string) $uuidThree);
        $this->assertEmpty($importedThree->time);

        $uuidFour = Uuid::generate(4);
        $importedFour = Uuid::import((string) $uuidFour);
        $this->assertEmpty($importedFour->time);

        $uuidFive = Uuid::generate(5, 'example.com', Uuid::NS_DNS);
        $importedFive = Uuid::import((string) $uuidFive);
        $this->assertEmpty($importedFive->time);
    }

    public function test_uuid_compare()
    {
        $uuid1 = (string) Uuid::generate(1);
        $uuid2 = (string) Uuid::generate(1);

        $this->assertTrue(Uuid::compare($uuid1, $uuid1));
        $this->assertFalse(Uuid::compare($uuid1, $uuid2));
    }

    public function test_nil_uuid_creation()
    {
        $nil = Uuid::nil();
        $this->assertInstanceOf('Webpatser\Uuid\Uuid', $nil);
        $this->assertEquals('00000000-0000-0000-0000-000000000000', (string) $nil);
        $this->assertEquals(Uuid::NIL, (string) $nil);
    }

    public function test_nil_uuid_properties()
    {
        $nil = Uuid::nil();
        $this->assertEquals(0, $nil->version);
        $this->assertEquals(0, $nil->variant);
        $this->assertNull($nil->time);
        $this->assertNull($nil->node);
    }

    public function test_is_nil_method()
    {
        $nil = Uuid::nil();
        $this->assertTrue($nil->isNil());

        $regular = Uuid::generate(4);
        $this->assertFalse($regular->isNil());

        $importedNil = Uuid::import('00000000-0000-0000-0000-000000000000');
        $this->assertTrue($importedNil->isNil());
    }

    public function test_is_nil_uuid_static_method()
    {
        // Test with UUID object
        $nil = Uuid::nil();
        $this->assertTrue(Uuid::isNilUuid($nil));

        $regular = Uuid::generate(4);
        $this->assertFalse(Uuid::isNilUuid($regular));

        // Test with string
        $this->assertTrue(Uuid::isNilUuid('00000000-0000-0000-0000-000000000000'));
        $this->assertTrue(Uuid::isNilUuid(Uuid::NIL));
        $this->assertFalse(Uuid::isNilUuid('123e4567-e89b-12d3-a456-426614174000'));

        // Test with different formats
        $this->assertTrue(Uuid::isNilUuid('00000000000000000000000000000000')); // no hyphens
    }

    public function test_nil_uuid_constant()
    {
        $this->assertEquals('00000000-0000-0000-0000-000000000000', Uuid::NIL);
    }

    public function test_nil_uuid_validation()
    {
        $nil = Uuid::nil();
        $this->assertTrue(Uuid::validate($nil));
        $this->assertTrue(Uuid::validate(Uuid::NIL));
        $this->assertTrue(Uuid::validate('00000000-0000-0000-0000-000000000000'));
    }

    public function test_version7_generation()
    {
        $uuid = Uuid::generate(7);
        $this->assertInstanceOf('Webpatser\Uuid\Uuid', $uuid);
        $this->assertEquals(7, $uuid->version);
        $this->assertEquals(1, $uuid->variant);
    }

    public function test_version7_validation()
    {
        $uuid = Uuid::generate(7);
        $this->assertTrue(Uuid::validate($uuid));
        $this->assertMatchesRegularExpression('~'.Uuid::VALID_UUID_REGEX.'~', (string) $uuid);
    }

    public function test_version7_properties()
    {
        $beforeTime = microtime(true);
        $uuid = Uuid::generate(7);
        $afterTime = microtime(true);

        $this->assertEquals(7, $uuid->version);
        $this->assertEquals(1, $uuid->variant);

        // Test timestamp extraction
        $extractedTime = $uuid->time;
        $this->assertNotNull($extractedTime);
        $this->assertIsFloat($extractedTime);

        // Timestamp should be within reasonable bounds (within 1 second of generation)
        $this->assertGreaterThanOrEqual($beforeTime - 1, $extractedTime);
        $this->assertLessThanOrEqual($afterTime + 1, $extractedTime);

        // Node should be null for V7 (no MAC address)
        $this->assertNull($uuid->node);
    }

    public function test_version7_uniqueness()
    {
        $uuids = [];
        for ($i = 0; $i < 1000; $i++) {
            $uuid = (string) Uuid::generate(7);
            $this->assertNotContains($uuid, $uuids, 'Generated duplicate UUID: '.$uuid);
            $uuids[] = $uuid;
        }
    }

    public function test_version7_sortability()
    {
        $uuids = [];
        $timestamps = [];

        // Generate UUIDs with small delays to ensure different timestamps
        for ($i = 0; $i < 5; $i++) {
            if ($i > 0) {
                usleep(1000);
            } // 1ms delay
            $uuid = Uuid::generate(7);
            $uuids[] = (string) $uuid;
            $timestamps[] = $uuid->time;
        }

        // Sort UUIDs lexicographically
        $sortedUuids = $uuids;
        sort($sortedUuids);

        // Sort timestamps numerically
        $sortedTimestamps = $timestamps;
        sort($sortedTimestamps);

        // Lexicographic UUID order should match timestamp order
        $this->assertEquals($uuids, $sortedUuids, 'UUIDs are not naturally sortable by creation time');
        $this->assertEquals($timestamps, $sortedTimestamps, 'Timestamps are not in ascending order');
    }

    public function test_version7_timestamp_accuracy()
    {
        $currentTime = microtime(true);
        $uuid = Uuid::generate(7);
        $extractedTime = $uuid->time;

        // Should be within 10ms of current time (allowing for execution delay)
        $timeDiff = abs($extractedTime - $currentTime);
        $this->assertLessThan(0.01, $timeDiff, 'Timestamp accuracy is off by more than 10ms');
    }

    public function test_version7_import()
    {
        $original = Uuid::generate(7);
        $imported = Uuid::import((string) $original);

        $this->assertEquals($original->version, $imported->version);
        $this->assertEquals($original->variant, $imported->variant);
        $this->assertEquals($original->time, $imported->time);
        $this->assertEquals((string) $original, (string) $imported);
    }

    public function test_version6_generation()
    {
        $uuid = Uuid::generate(6);
        $this->assertInstanceOf('Webpatser\Uuid\Uuid', $uuid);
        $this->assertEquals(6, $uuid->version);
        $this->assertEquals(1, $uuid->variant);
    }

    public function test_version6_with_mac_address()
    {
        $macAddress = '00:11:22:33:44:55';
        $uuid = Uuid::generate(6, $macAddress);

        $this->assertEquals(6, $uuid->version);
        $this->assertEquals(1, $uuid->variant);
        $this->assertEquals('001122334455', $uuid->node);
        $this->assertNotNull($uuid->time);
    }

    public function test_version6_properties()
    {
        $beforeTime = microtime(true);
        $uuid = Uuid::generate(6);
        $afterTime = microtime(true);

        $this->assertEquals(6, $uuid->version);
        $this->assertEquals(1, $uuid->variant);

        // Test timestamp extraction
        $extractedTime = $uuid->time;
        $this->assertNotNull($extractedTime);
        $this->assertIsFloat($extractedTime);

        // Timestamp should be within reasonable bounds
        $this->assertGreaterThanOrEqual($beforeTime - 1, $extractedTime);
        $this->assertLessThanOrEqual($afterTime + 1, $extractedTime);

        // Should have a node (MAC address)
        $this->assertNotNull($uuid->node);
        $this->assertEquals(12, strlen($uuid->node)); // 12 hex chars = 6 bytes
    }

    public function test_version6_sortability()
    {
        $uuids = [];
        $timestamps = [];

        // Generate UUIDs with small delays
        for ($i = 0; $i < 5; $i++) {
            if ($i > 0) {
                usleep(1000);
            } // 1ms delay
            $uuid = Uuid::generate(6);
            $uuids[] = (string) $uuid;
            $timestamps[] = $uuid->time;
        }

        // Sort UUIDs lexicographically
        $sortedUuids = $uuids;
        sort($sortedUuids);

        // Sort timestamps numerically
        $sortedTimestamps = $timestamps;
        sort($sortedTimestamps);

        // V6 should be sortable (unlike V1)
        $this->assertEquals($uuids, $sortedUuids, 'V6 UUIDs should be naturally sortable by creation time');
        $this->assertEquals($timestamps, $sortedTimestamps, 'Timestamps should be in ascending order');
    }

    public function test_version6_validation()
    {
        $uuid = Uuid::generate(6);
        $this->assertTrue(Uuid::validate($uuid));
        $this->assertMatchesRegularExpression('~'.Uuid::VALID_UUID_REGEX.'~', (string) $uuid);
    }

    public function test_version8_generation()
    {
        $uuid = Uuid::generate(8);
        $this->assertInstanceOf('Webpatser\Uuid\Uuid', $uuid);
        $this->assertEquals(8, $uuid->version);
        $this->assertEquals(1, $uuid->variant);
    }

    public function test_version8_with_custom_data()
    {
        $customData = 'test-data-123';
        $uuid1 = Uuid::generate(8, $customData);
        $uuid2 = Uuid::generate(8, $customData);

        $this->assertEquals(8, $uuid1->version);
        $this->assertEquals(8, $uuid2->version);

        // Same input should produce same UUID for V8
        $this->assertEquals((string) $uuid1, (string) $uuid2);
    }

    public function test_version8_with_binary_data()
    {
        $binaryData = random_bytes(16);
        $uuid = Uuid::generate(8, $binaryData);

        $this->assertEquals(8, $uuid->version);
        $this->assertEquals(1, $uuid->variant);

        // Version and variant bits should be set correctly despite custom data
        $versionBits = ord($uuid->bytes[6]) >> 4;
        $this->assertEquals(8, $versionBits);
    }

    public function test_version8_properties()
    {
        $uuid = Uuid::generate(8);

        $this->assertEquals(8, $uuid->version);
        $this->assertEquals(1, $uuid->variant);

        // V8 doesn't have time or node properties
        $this->assertNull($uuid->time);
        $this->assertNull($uuid->node);
    }

    public function test_version8_validation()
    {
        $uuid = Uuid::generate(8);
        $this->assertTrue(Uuid::validate($uuid));
        $this->assertMatchesRegularExpression('~'.Uuid::VALID_UUID_REGEX.'~', (string) $uuid);
    }

    public function test_version8_uniqueness()
    {
        $uuids = [];
        for ($i = 0; $i < 100; $i++) {
            // Without custom data, should be random
            $uuid = (string) Uuid::generate(8);
            $this->assertNotContains($uuid, $uuids, 'Generated duplicate V8 UUID: '.$uuid);
            $uuids[] = $uuid;
        }
    }

    public function test_version8_custom_data_types()
    {
        // Test with different data types
        $arrayData = ['key' => 'value', 'number' => 123];
        $uuid1 = Uuid::generate(8, $arrayData);
        $uuid2 = Uuid::generate(8, $arrayData);

        $this->assertEquals((string) $uuid1, (string) $uuid2);

        // Test with object
        $object = (object) ['prop' => 'value'];
        $uuid3 = Uuid::generate(8, $object);
        $this->assertEquals(8, $uuid3->version);

        // Test with number
        $uuid4 = Uuid::generate(8, 42);
        $uuid5 = Uuid::generate(8, 42);
        $this->assertEquals((string) $uuid4, (string) $uuid5);
    }

    public function test_all_versions_generation()
    {
        // Test that all supported versions can be generated
        $versions = [1, 3, 4, 5, 6, 7, 8];

        foreach ($versions as $version) {
            $uuid = match ($version) {
                3, 5 => Uuid::generate($version, 'test', Uuid::NS_DNS),
                default => Uuid::generate($version),
            };

            $this->assertEquals($version, $uuid->version, "Version $version generation failed");
            $this->assertEquals(1, $uuid->variant, "Version $version has wrong variant");
            $this->assertTrue(Uuid::validate($uuid), "Version $version validation failed");
        }
    }
}
