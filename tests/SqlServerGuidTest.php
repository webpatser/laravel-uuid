<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;
use Webpatser\Uuid\Uuid;
use Illuminate\Support\Str;
use Webpatser\LaravelUuid\UuidMacros;

/**
 * Tests for SQL Server GUID byte order conversion
 * 
 * Addresses GitHub issue #11 where SQL Server uniqueidentifier fields
 * have mixed endianness that causes byte order issues when importing.
 */
class SqlServerGuidTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        // Register macros for testing
        UuidMacros::register();
    }

    /**
     * Test the exact case from GitHub issue #11
     * 
     * SQL Server UUID: 825B076B-44EC-E511-80DC-00155D0ABC54
     * Expected PHP UUID: 6B075B82-EC44-11E5-80DC-00155D0ABC54 (corrected)
     */
    public function testGitHubIssue11SqlServerByteOrder(): void
    {
        $sqlServerGuid = '825B076B-44EC-E511-80DC-00155D0ABC54';
        $expectedStandardUuid = '6B075B82-EC44-11E5-80DC-00155D0ABC54';

        // Test core library method
        $uuid = Uuid::importFromSqlServer($sqlServerGuid);
        $this->assertEquals($expectedStandardUuid, strtoupper($uuid->string));

        // Test Laravel macro
        $convertedUuid = Str::uuidFromSqlServer($sqlServerGuid);
        $this->assertEquals($expectedStandardUuid, strtoupper($convertedUuid));
    }

    /**
     * Test round-trip conversion: Standard UUID -> SQL Server -> Standard UUID
     */
    public function testRoundTripConversion(): void
    {
        $originalUuid = '6B075B82-EC44-11E5-80DC-00155D0ABC54';
        
        // Convert to SQL Server format
        $uuid = Uuid::import($originalUuid);
        $sqlServerFormat = $uuid->toSqlServer();
        
        // This should match the original SQL Server GUID from issue #11
        $this->assertEquals('825B076B-44EC-E511-80DC-00155D0ABC54', $sqlServerFormat);
        
        // Convert back to standard format
        $backToStandard = Uuid::importFromSqlServer($sqlServerFormat);
        $this->assertEquals($originalUuid, strtoupper($backToStandard->string));
    }

    /**
     * Test Laravel macro round-trip conversion
     */
    public function testLaravelMacroRoundTrip(): void
    {
        $originalUuid = '6B075B82-EC44-11E5-80DC-00155D0ABC54';
        
        // Convert to SQL Server format using macro
        $sqlServerGuid = Str::uuidToSqlServer($originalUuid);
        $this->assertEquals('825B076B-44EC-E511-80DC-00155D0ABC54', $sqlServerGuid);
        
        // Convert back using macro
        $backToStandard = Str::uuidFromSqlServer($sqlServerGuid);
        $this->assertEquals($originalUuid, strtoupper($backToStandard));
    }

    /**
     * Test binary conversion for SQL Server
     */
    public function testSqlServerBinaryConversion(): void
    {
        $standardUuid = '6B075B82-EC44-11E5-80DC-00155D0ABC54';
        $uuid = Uuid::import($standardUuid);
        
        // Get SQL Server binary format
        $sqlServerBinary = $uuid->toSqlServerBinary();
        $this->assertEquals(16, strlen($sqlServerBinary));
        
        // Test Laravel macro for binary conversion
        $macroBinary = Str::uuidToSqlServerBinary($standardUuid);
        $this->assertEquals($sqlServerBinary, $macroBinary);
        
        // Test reverse conversion with macro
        $backToUuid = Str::sqlServerBinaryToUuid($sqlServerBinary);
        $this->assertEquals($standardUuid, strtoupper($backToUuid));
    }

    /**
     * Test that normal UUIDs are not affected by SQL Server methods
     */
    public function testNormalUuidUnaffected(): void
    {
        // Generate a normal UUID
        $normalUuid = (string) Uuid::v4();
        
        // Converting to SQL Server and back should be lossless
        $toSqlServer = Str::uuidToSqlServer($normalUuid);
        $backToNormal = Str::uuidFromSqlServer($toSqlServer);
        
        $this->assertEquals(strtoupper($normalUuid), strtoupper($backToNormal));
        $this->assertTrue(Uuid::validate($toSqlServer));
        $this->assertTrue(Uuid::validate($backToNormal));
    }

    /**
     * Test multiple UUID versions with SQL Server conversion
     */
    public function testMultipleVersionsWithSqlServer(): void
    {
        $testUuids = [
            (string) Uuid::generate(1),  // Time-based
            (string) Uuid::v4(),         // Random
            (string) Uuid::v7(),         // Time-ordered
        ];

        foreach ($testUuids as $original) {
            $sqlServerFormat = Str::uuidToSqlServer($original);
            $backToOriginal = Str::uuidFromSqlServer($sqlServerFormat);
            
            $this->assertEquals(strtoupper($original), strtoupper($backToOriginal), 
                "Round-trip failed for UUID: {$original}");
            $this->assertTrue(Uuid::validate($sqlServerFormat));
        }
    }

    /**
     * Test error handling for invalid input
     */
    public function testErrorHandling(): void
    {
        // Test invalid SQL Server GUID
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid SQL Server GUID format');
        Uuid::importFromSqlServer('invalid-guid');
    }

    /**
     * Test Laravel macro error handling
     */
    public function testLaravelMacroErrorHandling(): void
    {
        // Test invalid UUID for conversion to SQL Server
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid UUID format');
        Str::uuidToSqlServer('invalid-uuid');
    }

    /**
     * Test binary error handling
     */
    public function testBinaryErrorHandling(): void
    {
        // Test invalid binary length
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('SQL Server GUID binary must be exactly 16 bytes');
        Str::sqlServerBinaryToUuid('too-short');
    }

    /**
     * Demonstrate the fix for the original issue
     */
    public function testOriginalIssueFix(): void
    {
        // This is the exact scenario from GitHub issue #11
        $sqlServerGuid = '825B076B-44EC-E511-80DC-00155D0ABC54';
        
        // Before fix: importing this would give wrong UUID
        // After fix: importing this gives correct UUID
        $correctedUuid = Uuid::importFromSqlServer($sqlServerGuid);
        
        // Now App\Deployment::find($correctedUuid->string) should work
        // because the byte order has been corrected
        $this->assertTrue(Uuid::validate($correctedUuid->string));
        $this->assertNotEquals($sqlServerGuid, $correctedUuid->string);
        $this->assertEquals('6B075B82-EC44-11E5-80DC-00155D0ABC54', strtoupper($correctedUuid->string));
    }
}