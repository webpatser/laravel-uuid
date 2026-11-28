<?php

declare(strict_types=1);

use Illuminate\Support\Str;
use Webpatser\LaravelUuid\UuidMacros;

beforeEach(function () {
    UuidMacros::register();
});

it('generates fast uuid v4', function () {
    $uuid = Str::fastUuid();

    expect($uuid)->toBeString()->toHaveLength(36)
        ->and(Str::fastIsUuid($uuid))->toBeTrue()
        ->and(Str::uuidVersion($uuid))->toBe(4);
});

it('generates fast ordered uuid v7', function () {
    $uuid = Str::fastOrderedUuid();

    expect($uuid)->toBeString()->toHaveLength(36)
        ->and(Str::fastIsUuid($uuid))->toBeTrue()
        ->and(Str::uuidVersion($uuid))->toBe(7);
});

it('validates uuids with fastIsUuid', function () {
    expect(Str::fastIsUuid('550e8400-e29b-41d4-a716-446655440000'))->toBeTrue()
        ->and(Str::fastIsUuid('not-a-uuid'))->toBeFalse();
});

it('supports additional uuid macros', function () {
    $timeUuid = Str::timeBasedUuid();
    expect(Str::isUuid($timeUuid))->toBeTrue()
        ->and(Str::uuidVersion($timeUuid))->toBe(1)
        ->and(Str::uuidTimestamp($timeUuid))->not->toBeNull();

    $reorderedTimeUuid = Str::reorderedTimeUuid();
    expect(Str::isUuid($reorderedTimeUuid))->toBeTrue()
        ->and(Str::uuidVersion($reorderedTimeUuid))->toBe(6);

    $customUuid = Str::customUuid('test-data');
    expect(Str::isUuid($customUuid))->toBeTrue()
        ->and(Str::uuidVersion($customUuid))->toBe(8);

    // Same custom data should produce same UUID
    $customUuid2 = Str::customUuid('test-data');
    expect($customUuid)->toBe($customUuid2);
});

it('generates name-based uuids', function () {
    $name = 'example.com';

    $md5Uuid = Str::nameUuidMd5($name);
    expect(Str::isUuid($md5Uuid))->toBeTrue()
        ->and(Str::uuidVersion($md5Uuid))->toBe(3);

    // Same name should produce same UUID
    expect(Str::nameUuidMd5($name))->toBe($md5Uuid);

    $sha1Uuid = Str::nameUuidSha1($name);
    expect(Str::isUuid($sha1Uuid))->toBeTrue()
        ->and(Str::uuidVersion($sha1Uuid))->toBe(5);

    // Same name should produce same UUID
    expect(Str::nameUuidSha1($name))->toBe($sha1Uuid);
});

it('supports nil uuid macros', function () {
    $nil = Str::nilUuid();

    expect($nil)->toBe('00000000-0000-0000-0000-000000000000')
        ->and(Str::isNilUuid($nil))->toBeTrue()
        ->and(Str::isNilUuid(Str::fastUuid()))->toBeFalse();
});

it('detects uuid version', function () {
    expect(Str::uuidVersion(Str::fastUuid()))->toBe(4)
        ->and(Str::uuidVersion(Str::fastOrderedUuid()))->toBe(7)
        ->and(Str::uuidVersion('invalid-uuid'))->toBeNull();
});

it('extracts uuid timestamp', function () {
    $timeUuid = Str::timeBasedUuid();
    $orderedUuid = Str::fastOrderedUuid();
    $randomUuid = Str::fastUuid();

    expect(Str::uuidTimestamp($timeUuid))->toBeFloat()
        ->and(Str::uuidTimestamp($orderedUuid))->toBeFloat()
        ->and(Str::uuidTimestamp($randomUuid))->toBeNull()
        ->and(Str::uuidTimestamp('invalid-uuid'))->toBeNull();
});

it('generates sortable ordered uuids', function () {
    $uuids = [];

    for ($i = 0; $i < 5; $i++) {
        if ($i > 0) {
            usleep(1000);
        }
        $uuids[] = Str::fastOrderedUuid();
    }

    $sortedUuids = $uuids;
    sort($sortedUuids);

    expect($uuids)->toBe($sortedUuids);
});

it('generates reordered time uuids that sort by creation time', function () {
    $uuids = [];

    for ($i = 0; $i < 5; $i++) {
        if ($i > 0) {
            // 5ms is far above the 100ns timestamp resolution
            usleep(5000);
        }
        $uuids[] = Str::reorderedTimeUuid();
    }

    $sortedUuids = $uuids;
    sort($sortedUuids, SORT_STRING);

    expect($uuids)->each(fn ($uuid) => $uuid->toBeString()->toHaveLength(36))
        ->and(array_unique($uuids))->toHaveCount(5)
        ->and($uuids)->toBe($sortedUuids);
});

it('returns the current time for a fresh reordered time uuid', function () {
    $before = microtime(true);
    $timestamp = Str::uuidTimestamp(Str::reorderedTimeUuid());
    $after = microtime(true);

    expect($timestamp)->toBeFloat()
        ->and($timestamp)->toBeGreaterThanOrEqual($before - 1)
        ->and($timestamp)->toBeLessThanOrEqual($after + 1);
});

it('puts the high 32 timestamp bits first in reordered time uuids (RFC 9562)', function () {
    // Same arithmetic as Uuid::mintTimeV6(), so float rounding cannot push the value outside the bounds
    $ticks = fn (float $t): int => (int) ($t * 10000000 + 0x01B21DD213814000) >> 28;

    $before = microtime(true);
    $uuid = Str::reorderedTimeUuid();
    $after = microtime(true);

    $high = hexdec(substr($uuid, 0, 8));

    expect($high)->toBeGreaterThanOrEqual($ticks($before))
        ->toBeLessThanOrEqual($ticks($after));
});

it('throws on invalid uuid string for uuidToBinary', function () {
    Str::uuidToBinary('not-a-valid-uuid');
})->throws(InvalidArgumentException::class, 'Invalid UUID format');

it('throws on wrong-length binary for binaryToUuid', function () {
    Str::binaryToUuid('too-short');
})->throws(InvalidArgumentException::class, 'Binary UUID must be exactly 16 bytes');
