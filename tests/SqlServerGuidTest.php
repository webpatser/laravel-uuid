<?php

declare(strict_types=1);

use Illuminate\Support\Str;
use Webpatser\LaravelUuid\UuidMacros;
use Webpatser\Uuid\Uuid;

beforeEach(function () {
    UuidMacros::register();
});

it('fixes github issue 11 sql server byte order', function () {
    $sqlServerGuid = '825B076B-44EC-E511-80DC-00155D0ABC54';
    $expectedStandardUuid = '6B075B82-EC44-11E5-80DC-00155D0ABC54';

    $uuid = Uuid::importFromSqlServer($sqlServerGuid);
    expect(strtoupper($uuid->string))->toBe($expectedStandardUuid);

    $convertedUuid = Str::uuidFromSqlServer($sqlServerGuid);
    expect(strtoupper($convertedUuid))->toBe($expectedStandardUuid);
});

it('supports round-trip conversion', function () {
    $originalUuid = '6B075B82-EC44-11E5-80DC-00155D0ABC54';

    $uuid = Uuid::import($originalUuid);
    $sqlServerFormat = $uuid->toSqlServer();

    expect($sqlServerFormat)->toBe('825B076B-44EC-E511-80DC-00155D0ABC54');

    $backToStandard = Uuid::importFromSqlServer($sqlServerFormat);
    expect(strtoupper($backToStandard->string))->toBe($originalUuid);
});

it('supports laravel macro round-trip', function () {
    $originalUuid = '6B075B82-EC44-11E5-80DC-00155D0ABC54';

    $sqlServerGuid = Str::uuidToSqlServer($originalUuid);
    expect($sqlServerGuid)->toBe('825B076B-44EC-E511-80DC-00155D0ABC54');

    $backToStandard = Str::uuidFromSqlServer($sqlServerGuid);
    expect(strtoupper($backToStandard))->toBe($originalUuid);
});

it('handles binary conversion for sql server', function () {
    $standardUuid = '6B075B82-EC44-11E5-80DC-00155D0ABC54';
    $uuid = Uuid::import($standardUuid);

    $sqlServerBinary = $uuid->toSqlServerBinary();
    expect($sqlServerBinary)->toHaveLength(16);

    $macroBinary = Str::uuidToSqlServerBinary($standardUuid);
    expect($macroBinary)->toBe($sqlServerBinary);

    $backToUuid = Str::sqlServerBinaryToUuid($sqlServerBinary);
    expect(strtoupper($backToUuid))->toBe($standardUuid);
});

it('does not affect normal uuids', function () {
    $normalUuid = (string) Uuid::v4();

    $toSqlServer = Str::uuidToSqlServer($normalUuid);
    $backToNormal = Str::uuidFromSqlServer($toSqlServer);

    expect(strtoupper($backToNormal))->toBe(strtoupper($normalUuid))
        ->and(Uuid::validate($toSqlServer))->toBeTrue()
        ->and(Uuid::validate($backToNormal))->toBeTrue();
});

it('converts multiple uuid versions', function () {
    $testUuids = [
        (string) Uuid::generate(1),
        (string) Uuid::v4(),
        (string) Uuid::v7(),
    ];

    foreach ($testUuids as $original) {
        $sqlServerFormat = Str::uuidToSqlServer($original);
        $backToOriginal = Str::uuidFromSqlServer($sqlServerFormat);

        expect(strtoupper($backToOriginal))->toBe(strtoupper($original))
            ->and(Uuid::validate($sqlServerFormat))->toBeTrue();
    }
});

it('throws on invalid sql server guid', function () {
    (void) Uuid::importFromSqlServer('invalid-guid');
})->throws(Exception::class, 'Invalid SQL Server GUID format');

it('throws on invalid uuid for sql server conversion', function () {
    Str::uuidToSqlServer('invalid-uuid');
})->throws(InvalidArgumentException::class, 'Invalid UUID format');

it('throws on invalid binary length', function () {
    Str::sqlServerBinaryToUuid('too-short');
})->throws(InvalidArgumentException::class, 'SQL Server GUID binary must be exactly 16 bytes');

it('validates sql server guid strings via the macro', function () {
    expect(Str::isSqlServerGuid('825B076B-44EC-E511-80DC-00155D0ABC54'))->toBeTrue()
        ->and(Str::isSqlServerGuid('6B075B82-EC44-11E5-80DC-00155D0ABC54'))->toBeTrue()
        ->and(Str::isSqlServerGuid('not-a-guid'))->toBeFalse()
        ->and(Str::isSqlServerGuid('zzzzzzzz-zzzz-zzzz-zzzz-zzzzzzzzzzzz'))->toBeFalse()
        ->and(Str::isSqlServerGuid(''))->toBeFalse();
});
