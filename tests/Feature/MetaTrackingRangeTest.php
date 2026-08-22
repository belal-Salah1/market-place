<?php

use App\Enums\MetaTrackingRange;
use Illuminate\Support\Carbon;

it('resolves each range to a start time', function () {
    Carbon::setTestNow('2026-08-22 14:00:00');

    expect(MetaTrackingRange::TODAY->since()->toDateTimeString())->toBe('2026-08-22 00:00:00')
        ->and(MetaTrackingRange::WEEK->since()->toDateTimeString())->toBe('2026-08-15 14:00:00')
        ->and(MetaTrackingRange::MONTH->since()->toDateTimeString())->toBe('2026-07-23 14:00:00')
        ->and(MetaTrackingRange::ALL->since())->toBeNull();
});

it('lists its values for the dashboard tabs', function () {
    expect(MetaTrackingRange::values())->toBe(['today', '7d', '30d', 'all']);
});
