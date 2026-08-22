<?php

namespace App\Enums;

use Illuminate\Support\Carbon;

/**
 * The tracking dashboard's date filter. `ALL` resolves to null, which every report
 * query reads as "do not constrain".
 *
 * Named `since()` rather than `from()` because every backed enum already inherits a
 * static `BackedEnum::from()`, and PHP forbids a static and an instance method
 * sharing a name.
 */
enum MetaTrackingRange: string
{
    case TODAY = 'today';
    case WEEK = '7d';
    case MONTH = '30d';
    case ALL = 'all';

    public function since(): ?Carbon
    {
        return match ($this) {
            self::TODAY => Carbon::today(),
            self::WEEK => Carbon::now()->subDays(7),
            self::MONTH => Carbon::now()->subDays(30),
            self::ALL => null,
        };
    }

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
