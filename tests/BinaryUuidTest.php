<?php

namespace Tests;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Webpatser\LaravelUuid\BinaryUuidCast;
use Webpatser\LaravelUuid\BinaryUuidMigrations;
use Webpatser\LaravelUuid\HasBinaryUuids;
use Webpatser\Uuid\Uuid;

// Test model using binary UUIDs
class BinaryUuidTestModel extends Model
{
    use HasBinaryUuids;

    protected $table = 'binary_uuid_test_models';

    protected $fillable = ['name', 'parent_id'];

    protected $casts = [
        'id' => BinaryUuidCast::class,
        'parent_id' => BinaryUuidCast::class,
    ];

    public $timestamps = false;
}

class BinaryUuidTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test table with binary UUIDs
        Schema::create('binary_uuid_test_models', function (Blueprint $table) {
            BinaryUuidMigrations::addBinaryUuidPrimary($table);
            BinaryUuidMigrations::addBinaryUuidColumn($table, 'parent_id', true);
            $table->string('name');
        });
    }

    public function test_binary_uuid_str_macros(): void
    {
        // Test binary UUID generation
        $binaryUuid = Str::fastBinaryUuid();
        $this->assertEquals(16, strlen($binaryUuid));

        // Test ordered binary UUID
        $orderedBinary = Str::fastBinaryOrderedUuid();
        $this->assertEquals(16, strlen($orderedBinary));

        // Test conversion between formats
        $stringUuid = Str::binaryToUuid($binaryUuid);
        $this->assertTrue(Str::fastIsUuid($stringUuid));
        $this->assertEquals(36, strlen($stringUuid));

        // Test reverse conversion
        $convertedBinary = Str::uuidToBinary($stringUuid);
        $this->assertEquals($binaryUuid, $convertedBinary);

        // Test binary validation
        $this->assertTrue(Str::isValidBinaryUuid($binaryUuid));
        $this->assertFalse(Str::isValidBinaryUuid('invalid'));
        $this->assertFalse(Str::isValidBinaryUuid(str_repeat('x', 15))); // Wrong length
    }

    public function test_binary_uuid_trait_generation(): void
    {
        $model = new BinaryUuidTestModel(['name' => 'Test']);
        $model->save();

        // Check that ID was generated as 16-byte binary
        $this->assertEquals(16, strlen($model->getRawOriginal('id')));

        // Check that the cast returns UUID object
        $this->assertInstanceOf(Uuid::class, $model->id);

        // Check string conversion
        $this->assertEquals(36, strlen($model->getUuidAsString()));
        $this->assertTrue(Str::fastIsUuid($model->getUuidAsString()));
    }

    public function test_binary_uuid_cast(): void
    {
        $model = new BinaryUuidTestModel(['name' => 'Test']);

        // Test setting UUID from string
        $stringUuid = Str::fastUuid();
        $model->parent_id = $stringUuid;
        $model->save();

        // Verify it was stored as binary
        $this->assertEquals(16, strlen($model->getRawOriginal('parent_id')));

        // Verify it can be retrieved as UUID object
        $this->assertInstanceOf(Uuid::class, $model->parent_id);
        $this->assertEquals($stringUuid, $model->parent_id->string);
    }

    public function test_binary_uuid_route_model_binding(): void
    {
        $model = new BinaryUuidTestModel(['name' => 'Test']);
        $model->save();

        // Get the string representation for route binding
        $routeKey = $model->getRouteKey();
        $this->assertEquals(36, strlen($routeKey));
        $this->assertTrue(Str::fastIsUuid($routeKey));

        // Test route binding query
        $query = BinaryUuidTestModel::query();
        $boundQuery = $model->resolveRouteBindingQuery($query, $routeKey);

        $found = $boundQuery->first();
        $this->assertNotNull($found);
        $this->assertEquals($model->name, $found->name);
    }

    public function test_binary_uuid_versions(): void
    {
        $model = new BinaryUuidTestModel(['name' => 'Test']);

        // Test V7 (default)
        $model->save();
        $this->assertEquals(7, $model->getUuidVersion());
        $this->assertTrue($model->usesOrderedUuids());
        $this->assertIsFloat($model->getUuidTimestamp());

        // Test V4 override
        $v4Model = new BinaryUuidTestModel(['name' => 'V4 Test']);
        $v4Model->id = Str::fastBinaryUuid(); // V4 binary
        $v4Model->save();

        $this->assertEquals(4, $v4Model->getUuidVersion());
        $this->assertFalse($v4Model->usesOrderedUuids());
        $this->assertNull($v4Model->getUuidTimestamp()); // V4 has no timestamp
    }

    public function test_binary_uuid_helper_methods(): void
    {
        $model = new BinaryUuidTestModel(['name' => 'Test']);

        // Test different UUID generation methods
        $randomBinary = $model->newRandomBinaryUuid();
        $this->assertEquals(16, strlen($randomBinary));
        $this->assertEquals(4, Uuid::import($randomBinary)->version);

        $orderedBinary = $model->newOrderedBinaryUuid();
        $this->assertEquals(16, strlen($orderedBinary));
        $this->assertEquals(7, Uuid::import($orderedBinary)->version);

        $timeBasedBinary = $model->newTimeBasedBinaryUuid();
        $this->assertEquals(16, strlen($timeBasedBinary));
        $this->assertEquals(1, Uuid::import($timeBasedBinary)->version);
    }

    public function test_binary_uuid_migration_helpers(): void
    {
        // Test migration stub generation
        $stub = BinaryUuidMigrations::getMigrationStub('test_table', [
            'user_id' => ['nullable' => true],
            'category_id' => ['nullable' => false],
        ]);

        $this->assertStringContainsString('test_table', $stub);
        $this->assertStringContainsString('addBinaryUuidPrimary', $stub);
        $this->assertStringContainsString('user_id', $stub);
        $this->assertStringContainsString('category_id', $stub);

        // Test conversion SQL generation
        $sql = BinaryUuidMigrations::getConversionSql('users', 'id');
        $this->assertStringContainsString('users', $sql);
        $this->assertStringContainsString('UNHEX', $sql);
        $this->assertStringContainsString('id_binary', $sql);
    }

    public function test_binary_uuid_storage_efficiency(): void
    {
        // Create models with both string and binary UUIDs for comparison
        $models = [];

        for ($i = 0; $i < 10; $i++) {
            $model = new BinaryUuidTestModel(['name' => "Test {$i}"]);
            $model->save();
            $models[] = $model;
        }

        // Verify all have 16-byte binary storage
        foreach ($models as $model) {
            $this->assertEquals(16, strlen($model->getRawOriginal('id')));
        }

        // Test that we can still work with them as UUID objects
        $firstModel = $models[0];
        $this->assertInstanceOf(Uuid::class, $firstModel->id);
        $this->assertEquals(7, $firstModel->id->version); // V7 by default
    }

    public function test_binary_uuid_conversion_macros(): void
    {
        // Test all binary conversion macros
        $timeBasedBinary = Str::binaryTimeBasedUuid();
        $this->assertEquals(16, strlen($timeBasedBinary));
        $this->assertEquals(1, Uuid::import($timeBasedBinary)->version);

        $reorderedBinary = Str::binaryReorderedTimeUuid();
        $this->assertEquals(16, strlen($reorderedBinary));
        $this->assertEquals(6, Uuid::import($reorderedBinary)->version);

        $customBinary = Str::binaryCustomUuid('test-data');
        $this->assertEquals(16, strlen($customBinary));
        $this->assertEquals(8, Uuid::import($customBinary)->version);
    }

    public function test_binary_uuid_cast_error_handling(): void
    {
        $cast = new BinaryUuidCast;
        $model = new BinaryUuidTestModel;

        // Test invalid input for set method
        $this->expectException(\InvalidArgumentException::class);
        $cast->set($model, 'test', 'invalid-uuid', []);
    }

    public function test_binary_uuid_serialization(): void
    {
        $model = new BinaryUuidTestModel(['name' => 'Test']);
        $model->save();

        // Test JSON serialization
        $json = $model->toJson();
        $decoded = json_decode($json, true);

        // Should serialize to string format for JSON
        $this->assertEquals(36, strlen($decoded['id']));
        $this->assertTrue(Str::fastIsUuid($decoded['id']));
    }
}
