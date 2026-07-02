# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [7.0.1] - 2026-07-02

### Fixed
- `Str::isSqlServerGuid()` now validates via `Uuid::validate()` instead of the non-existent `Uuid::isSqlServerFormat()`, which threw a runtime error. SQL Server `uniqueidentifier` values share the 8-4-4-4-12 hex text format, so string validation is the correct check.

## [7.0.0] - 2026-03-20

### Changed
- **Laravel 13 Support**: Now requires Laravel 13.x exclusively
- **PHP 8.5 Requirement**: Minimum PHP version bumped to 8.5
- **UUID Package**: Requires `webpatser/uuid` ^2.0
- **Test Framework**: Migrated entire test suite from PHPUnit to Pest 4
- **Code Modernization**: Refactored `switch` statements to PHP `match` expressions in `BinaryUuidMigrations`
- **Dev Dependencies**: Replaced PHPUnit with Pest PHP, added Pest plugin support

### Removed
- Dropped Laravel 11.x and 12.x support
- Dropped PHP 8.2–8.4 support
- Removed PHPUnit bootstrap file (`tests/bootstrap.php`)
- Removed local path repository configuration from `composer.json`

## [6.2.0] - 2025-09-12

### Changed
- **Directory Structure Refactoring**: Modernized PSR-4 autoloading structure
  - Moved all classes from `src/Webpatser/LaravelUuid/` directly to `src/`
  - Simplified autoload mapping in `composer.json` to follow Laravel package conventions
  - Eliminated redundant nested directory structure while maintaining namespace consistency

### Removed
- Cleaned up development artifacts: `benchmark_results.php`, `.travis.yml`, and `.phpunit.cache/`
- Added `.phpunit.cache/` to `.gitignore` for cleaner repository maintenance

## [6.1.0] - 2025-09-11

### Added
- **SQL Server GUID Support**: Complete Laravel integration for SQL Server's mixed-endianness GUID format
  - `Str::uuidFromSqlServer()` - Import UUID from SQL Server with automatic byte order correction
  - `Str::uuidToSqlServer()` - Export UUID to SQL Server GUID format
  - `Str::uuidToSqlServerBinary()` - Get SQL Server binary format for uniqueidentifier columns
  - `Str::sqlServerBinaryToUuid()` - Import SQL Server binary uniqueidentifier to standard UUID
  - `Str::isSqlServerGuid()` - Heuristic detection of SQL Server GUID format
- **SQL Server Migration Support**: 
  - Automatic `uniqueidentifier` column support in `BinaryUuidMigrations`
  - SQL Server database detection and conversion SQL generation
  - Cross-database compatibility with unified migration helpers
- **Database Support Matrix**: Extended to include SQL Server alongside MySQL, PostgreSQL, and SQLite
- Comprehensive test suite for SQL Server GUID handling (10 tests, 27 assertions)
- Complete documentation with examples, migration guides, and database compatibility matrix

### Changed
- **Default Database**: Changed fallback database from MySQL to SQLite (Laravel's default)
- **Code Formatting**: Applied Laravel Pint formatting across entire codebase (16 files, 15 style fixes)
- **Documentation**: Enhanced README with SQL Server GUID section and updated database support tables
- **Dependencies**: Updated to use webpatser/uuid v1.3.0 with SQL Server support

### Fixed
- SQL Server GUID byte order problems when working with uniqueidentifier columns
- Cross-database UUID compatibility issues between SQL Server and other databases

## [6.0.1] - 2025-09-10

### Fixed
- Removed benchmark function references from README documentation
- Updated documentation to match actual available methods

## [6.0.0] - 2025-09-10 - Major Architecture Change

### Added
- **Split Architecture**: UUID logic moved to dedicated `webpatser/uuid` package
- **High-Performance Str Macros**: 
  - `Str::fastUuid()` - 15% faster than `Str::uuid()`
  - `Str::fastOrderedUuid()` - 25% faster than `Str::orderedUuid()`
  - `Str::fastIsUuid()` - Fast UUID validation
- **Extended UUID Methods**: Support for all UUID versions (1, 3, 4, 5, 6, 7, 8)
  - `Str::timeBasedUuid()` - V1 time-based
  - `Str::reorderedTimeUuid()` - V6 reordered time
  - `Str::customUuid()` - V8 custom
  - `Str::nameUuidSha1()` - V5 name-based
- **Binary UUID Storage**: 55% storage savings with 16-byte binary columns
  - `HasBinaryUuids` trait for automatic binary UUID support
  - `BinaryUuidCast` for automatic conversion
  - `BinaryUuidMigrations` for database-optimized migrations
- **Production Ready**: Comprehensive testing on MariaDB, MySQL, PostgreSQL, SQLite

### Breaking Changes
- **Architecture**: Core UUID functionality moved to separate `webpatser/uuid` package
- **Dependencies**: Now requires `webpatser/uuid` as core dependency
- **Performance**: Existing Laravel UUID methods unchanged, new high-performance alternatives added

### Performance Improvements
- **15-25% faster** UUID generation compared to Laravel's built-in methods
- **55% storage savings** with binary UUID storage
- **Better database performance** with optimized column types
- Modern PHP 8.2+ optimizations

### Features
- ✅ **Drop-in Replacements**: High-performance alternatives to Laravel's UUID components
- ✅ **Auto-Discovery Support**: Zero-configuration setup
- ✅ **Laravel 11.x & 12.x**: Latest Laravel support
- ✅ **Route Model Binding**: Seamless URL-to-model resolution
- ✅ **Foreign Keys**: Both binary and string UUID relationships
- ✅ **Migration Helpers**: Database-specific column types

## [5.x] - Previous Architecture

Previous versions were standalone UUID implementations. See git history for details.

[7.0.0]: https://github.com/webpatser/laravel-uuid/compare/v6.2.0...v7.0.0
[6.2.0]: https://github.com/webpatser/laravel-uuid/compare/v6.1.0...v6.2.0
[6.1.0]: https://github.com/webpatser/laravel-uuid/compare/v6.0.1...v6.1.0
[6.0.1]: https://github.com/webpatser/laravel-uuid/compare/v6.0.0...v6.0.1
[6.0.0]: https://github.com/webpatser/laravel-uuid/compare/v5.x...v6.0.0