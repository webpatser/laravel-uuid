<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;
use Webpatser\Uuid\Uuid;

class LaravelIntegrationTest extends TestCase
{
    public function testUuidClassIsAvailable(): void
    {
        // Test that the pure UUID library is accessible
        $uuid = Uuid::v4();
        $this->assertInstanceOf(Uuid::class, $uuid);
        $this->assertEquals(4, $uuid->version);
    }

    public function testUuidGenerationMethods(): void
    {
        // Test various generation methods work
        $uuid1 = Uuid::generate(1);
        $uuid4 = Uuid::generate(4);
        $uuid7 = Uuid::generate(7);
        
        $this->assertEquals(1, $uuid1->version);
        $this->assertEquals(4, $uuid4->version);
        $this->assertEquals(7, $uuid7->version);
    }

    public function testUuidValidation(): void
    {
        // Test validation methods work
        $uuid = Uuid::v4();
        $this->assertTrue(Uuid::validate($uuid->string));
        
        // Test string validation
        $validUuid = '550e8400-e29b-41d4-a716-446655440000';
        $this->assertTrue(Uuid::validate($validUuid));
        $this->assertFalse(Uuid::validate('invalid-uuid'));
    }

    public function testShorthandMethods(): void
    {
        // Test shorthand methods from pure library
        $uuid4 = Uuid::v4();
        $uuid7 = Uuid::v7();
        
        $this->assertEquals(4, $uuid4->version);
        $this->assertEquals(7, $uuid7->version);
    }

    public function testUuidProperties(): void
    {
        // Test that UUID properties are accessible
        $uuid = Uuid::v4();
        
        $this->assertIsString($uuid->string);
        $this->assertIsString($uuid->hex);
        $this->assertIsString($uuid->bytes);
        $this->assertEquals(36, strlen($uuid->string));
        $this->assertEquals(32, strlen($uuid->hex));
        $this->assertEquals(16, strlen($uuid->bytes));
    }

    public function testNilUuid(): void
    {
        // Test nil UUID functionality
        $nil = Uuid::nil();
        $this->assertTrue($nil->isNil());
        $this->assertEquals('00000000-0000-0000-0000-000000000000', (string) $nil);
        $this->assertTrue(Uuid::isNilUuid($nil));
    }

    public function testModernUuidVersions(): void
    {
        // Test that modern UUID versions (6, 7, 8) work
        $uuid6 = Uuid::generate(6);
        $uuid7 = Uuid::generate(7);
        $uuid8 = Uuid::generate(8);
        
        $this->assertEquals(6, $uuid6->version);
        $this->assertEquals(7, $uuid7->version);
        $this->assertEquals(8, $uuid8->version);
    }

    public function testBackwardCompatibility(): void
    {
        // Test that old method signatures still work
        $uuid3 = Uuid::generate(3, 'test', Uuid::NS_DNS);
        $uuid5 = Uuid::generate(5, 'test', Uuid::NS_DNS);
        
        $this->assertEquals(3, $uuid3->version);
        $this->assertEquals(5, $uuid5->version);
        
        // Test that same input produces same output for name-based UUIDs
        $uuid3_2 = Uuid::generate(3, 'test', Uuid::NS_DNS);
        $this->assertEquals((string) $uuid3, (string) $uuid3_2);
    }
}