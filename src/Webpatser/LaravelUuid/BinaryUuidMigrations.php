<?php

declare(strict_types=1);

namespace Webpatser\LaravelUuid;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

/**
 * Binary UUID Migration Helpers
 * 
 * Provides convenient methods for creating binary UUID columns in Laravel migrations.
 * Binary UUIDs provide 55% storage savings and better database performance.
 * 
 * Usage in migrations:
 * 
 * use Webpatser\LaravelUuid\BinaryUuidMigrations;
 * 
 * Schema::create('users', function (Blueprint $table) {
 *     BinaryUuidMigrations::addBinaryUuidPrimary($table);
 *     BinaryUuidMigrations::addBinaryUuidColumn($table, 'parent_id', true);
 *     $table->string('name');
 *     $table->timestamps();
 * });
 * 
 * // Convert existing string UUID table to binary
 * BinaryUuidMigrations::convertStringToBinary('users', 'id');
 */
class BinaryUuidMigrations
{
    /**
     * Add a binary UUID primary key column (database-optimized)
     * 
     * @param Blueprint $table
     * @param string $column Column name (default: 'id')
     * @return void
     */
    public static function addBinaryUuidPrimary(Blueprint $table, string $column = 'id'): void
    {
        $driver = self::getDatabaseDriver();
        
        switch ($driver) {
            case 'mysql':
            case 'mariadb':
                $table->binary($column, 16)->primary();
                break;
                
            case 'pgsql':
                // PostgreSQL has native UUID type, but we use bytea for binary storage
                $table->addColumn('bytea', $column)->primary();
                break;
                
            case 'sqlite':
                // SQLite stores everything as text, but we can still use BLOB
                $table->binary($column, 16)->primary();
                break;
                
            default:
                // Fallback to standard binary
                $table->binary($column, 16)->primary();
        }
    }

    /**
     * Add a binary UUID column (database-optimized)
     * 
     * @param Blueprint $table
     * @param string $column Column name
     * @param bool $nullable Whether the column should be nullable
     * @param bool $index Whether to add an index on the column
     * @return void
     */
    public static function addBinaryUuidColumn(
        Blueprint $table, 
        string $column, 
        bool $nullable = false, 
        bool $index = false
    ): void {
        $driver = self::getDatabaseDriver();
        
        switch ($driver) {
            case 'mysql':
            case 'mariadb':
                $col = $table->binary($column, 16);
                break;
                
            case 'pgsql':
                $col = $table->addColumn('bytea', $column);
                break;
                
            case 'sqlite':
                $col = $table->binary($column, 16);
                break;
                
            default:
                $col = $table->binary($column, 16);
        }
        
        if ($nullable) {
            $col->nullable();
        }
        
        if ($index) {
            $col->index();
        }
    }

    /**
     * Add binary UUID foreign key column (database-optimized)
     * 
     * @param Blueprint $table
     * @param string $column Column name
     * @param string $references Referenced table.column (e.g., 'users.id')
     * @param bool $nullable Whether the foreign key should be nullable
     * @return void
     */
    public static function addBinaryUuidForeign(
        Blueprint $table,
        string $column,
        string $references,
        bool $nullable = false
    ): void {
        $driver = self::getDatabaseDriver();
        
        switch ($driver) {
            case 'mysql':
            case 'mariadb':
                $col = $table->binary($column, 16);
                break;
                
            case 'pgsql':
                $col = $table->addColumn('bytea', $column);
                break;
                
            case 'sqlite':
                $col = $table->binary($column, 16);
                break;
                
            default:
                $col = $table->binary($column, 16);
        }
        
        if ($nullable) {
            $col->nullable();
        }
        
        // Parse table.column reference
        [$referencedTable, $referencedColumn] = explode('.', $references, 2);
        $col->foreign($column)->references($referencedColumn)->on($referencedTable);
    }

    /**
     * Get the current database driver
     * 
     * @return string
     */
    protected static function getDatabaseDriver(): string
    {
        try {
            return DB::connection()->getDriverName();
        } catch (\Exception) {
            // Fallback when Laravel app is not bootstrapped
            return 'mysql'; // Default to MySQL
        }
    }

    /**
     * Convert existing string UUID column to binary (database-specific)
     * WARNING: This will lose data if not handled properly!
     * 
     * Recommended process:
     * 1. Add new binary column
     * 2. Populate binary column from string column 
     * 3. Update application code
     * 4. Drop string column
     * 5. Rename binary column
     * 
     * @param string $table Table name
     * @param string $column Column name to convert
     * @param string|null $driver Database driver (auto-detected if null)
     * @return string Raw SQL for manual execution
     */
    public static function getConversionSql(string $table, string $column, ?string $driver = null): string
    {
        $driver = $driver ?? self::getDatabaseDriver();
        
        switch ($driver) {
            case 'mysql':
            case 'mariadb':
                return self::getMysqlConversionSql($table, $column);
                
            case 'pgsql':
                return self::getPostgresqlConversionSql($table, $column);
                
            case 'sqlite':
                return self::getSqliteConversionSql($table, $column);
                
            default:
                return self::getMysqlConversionSql($table, $column) . "\n\n-- Note: This is MySQL syntax. Adjust for your database.";
        }
    }

    /**
     * Get MySQL/MariaDB specific conversion SQL
     */
    protected static function getMysqlConversionSql(string $table, string $column): string
    {
        return "
-- MySQL/MariaDB: String to Binary UUID Conversion
-- WARNING: Test this process thoroughly before running in production!

-- 1. Add temporary binary column
ALTER TABLE `{$table}` ADD COLUMN `{$column}_binary` BINARY(16) NULL AFTER `{$column}`;

-- 2. Populate binary column from string column
UPDATE `{$table}` 
SET `{$column}_binary` = UNHEX(REPLACE(`{$column}`, '-', '')) 
WHERE `{$column}` IS NOT NULL;

-- 3. Verify conversion (should return 0)
SELECT COUNT(*) FROM `{$table}` 
WHERE `{$column}` IS NOT NULL 
AND `{$column}_binary` IS NULL;

-- 4. After updating your application code:
-- ALTER TABLE `{$table}` DROP COLUMN `{$column}`;
-- ALTER TABLE `{$table}` CHANGE `{$column}_binary` `{$column}` BINARY(16) NOT NULL;
        ";
    }

    /**
     * Get PostgreSQL specific conversion SQL
     */
    protected static function getPostgresqlConversionSql(string $table, string $column): string
    {
        return "
-- PostgreSQL: String to Binary UUID Conversion
-- WARNING: Test this process thoroughly before running in production!

-- 1. Add temporary bytea column
ALTER TABLE \"{$table}\" ADD COLUMN \"{$column}_binary\" bytea;

-- 2. Populate binary column from string column  
UPDATE \"{$table}\" 
SET \"{$column}_binary\" = decode(replace(\"{$column}\", '-', ''), 'hex')
WHERE \"{$column}\" IS NOT NULL;

-- 3. Verify conversion (should return 0)
SELECT COUNT(*) FROM \"{$table}\" 
WHERE \"{$column}\" IS NOT NULL 
AND \"{$column}_binary\" IS NULL;

-- 4. After updating your application code:
-- ALTER TABLE \"{$table}\" DROP COLUMN \"{$column}\";
-- ALTER TABLE \"{$table}\" RENAME COLUMN \"{$column}_binary\" TO \"{$column}\";
        ";
    }

    /**
     * Get SQLite specific conversion SQL
     */
    protected static function getSqliteConversionSql(string $table, string $column): string
    {
        return "
-- SQLite: String to Binary UUID Conversion
-- WARNING: Test this process thoroughly before running in production!

-- 1. Add temporary blob column
ALTER TABLE `{$table}` ADD COLUMN `{$column}_binary` BLOB;

-- 2. Populate binary column from string column
UPDATE `{$table}` 
SET `{$column}_binary` = unhex(replace(`{$column}`, '-', ''))
WHERE `{$column}` IS NOT NULL;

-- 3. Verify conversion (should return 0)
SELECT COUNT(*) FROM `{$table}` 
WHERE `{$column}` IS NOT NULL 
AND `{$column}_binary` IS NULL;

-- 4. After updating your application code, recreate table:
-- (SQLite doesn't support dropping columns easily)
-- CREATE TABLE {$table}_new AS SELECT {$column}_binary AS {$column}, ... FROM {$table};
-- DROP TABLE {$table};
-- ALTER TABLE {$table}_new RENAME TO {$table};
        ";
    }

    /**
     * Get database-specific information about binary UUID implementation
     * 
     * @param string|null $driver Database driver (auto-detected if null)
     * @return array Database information
     */
    public static function getDatabaseInfo(?string $driver = null): array
    {
        $driver = $driver ?? self::getDatabaseDriver();
        
        switch ($driver) {
            case 'mysql':
            case 'mariadb':
                return [
                    'driver' => $driver,
                    'column_type' => 'BINARY(16)',
                    'storage_bytes' => 16,
                    'conversion_function' => 'UNHEX(REPLACE(uuid, "-", ""))',
                    'reverse_function' => 'INSERT(INSERT(INSERT(INSERT(HEX(binary_uuid), 9, 0, "-"), 14, 0, "-"), 19, 0, "-"), 24, 0, "-")',
                    'supports_native_uuid' => false,
                ];
                
            case 'pgsql':
                return [
                    'driver' => $driver,
                    'column_type' => 'bytea',
                    'storage_bytes' => 16,
                    'conversion_function' => 'decode(replace(uuid, "-", ""), "hex")',
                    'reverse_function' => 'encode(binary_uuid, "hex") with dashes inserted',
                    'supports_native_uuid' => true,
                    'note' => 'PostgreSQL has native UUID type, but bytea is used for binary storage'
                ];
                
            case 'sqlite':
                return [
                    'driver' => $driver,
                    'column_type' => 'BLOB',
                    'storage_bytes' => 16,
                    'conversion_function' => 'unhex(replace(uuid, "-", ""))',
                    'reverse_function' => 'hex(binary_uuid) with dashes inserted',
                    'supports_native_uuid' => false,
                ];
                
            default:
                return [
                    'driver' => $driver,
                    'column_type' => 'BINARY(16)',
                    'storage_bytes' => 16,
                    'conversion_function' => 'Database-specific function needed',
                    'reverse_function' => 'Database-specific function needed',
                    'supports_native_uuid' => 'unknown',
                ];
        }
    }

    /**
     * Get a complete migration stub for binary UUIDs
     * 
     * @param string $table Table name
     * @param array $columns Additional binary UUID columns
     * @return string Complete migration file content
     */
    public static function getMigrationStub(string $table, array $columns = []): string
    {
        $additionalColumns = '';
        foreach ($columns as $column => $config) {
            $nullable = $config['nullable'] ?? false;
            $nullableStr = $nullable ? 'true' : 'false';
            $additionalColumns .= "            BinaryUuidMigrations::addBinaryUuidColumn(\$table, '{$column}', {$nullableStr});\n";
        }

        $dbInfo = self::getDatabaseInfo();
        $dbComment = "// {$dbInfo['driver']}: Uses {$dbInfo['column_type']} ({$dbInfo['storage_bytes']} bytes)";
        
        return "<?php

use Illuminate\\Database\\Migrations\\Migration;
use Illuminate\\Database\\Schema\\Blueprint;
use Illuminate\\Support\\Facades\\Schema;
use Webpatser\\LaravelUuid\\BinaryUuidMigrations;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Binary UUID Storage Benefits:
     * - 55% smaller storage ({$dbInfo['storage_bytes']} bytes vs 36 chars)
     * - Faster database queries and indexing
     * - Better memory usage in large datasets
     * - Database: {$dbInfo['driver']} ({$dbInfo['column_type']})
     */
    public function up(): void
    {
        Schema::create('{$table}', function (Blueprint \$table) {
            {$dbComment}
            BinaryUuidMigrations::addBinaryUuidPrimary(\$table);
            
{$additionalColumns}            
            \$table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('{$table}');
    }
};
";
    }
}