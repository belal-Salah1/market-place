<?php

use App\Enums\MetaEventStatus;
use App\Models\MetaBrowserEvent;
use App\Models\MetaEvent;
use App\Services\Meta\MetaTrackingReportService;
use Illuminate\Support\Carbon;

function serverEvent(string $name, string $eventId, MetaEventStatus $status, ?Carbon $at = null): MetaEvent
{
    $event = MetaEvent::create([
        'event_name' => $name,
        'event_id' => $eventId,
        'status' => $status,
        'payload' => ['event_name' => $name, 'event_id' => $eventId],
    ]);

    if ($at) {
        $event->forceFill(['created_at' => $at])->save();
    }

    return $event;
}

function browserFire(string $name, ?string $eventId = null, ?Carbon $at = null): MetaBrowserEvent
{
    $fire = MetaBrowserEvent::create(['event_name' => $name, 'event_id' => $eventId]);

    if ($at) {
        $fire->forceFill(['created_at' => $at])->save();
    }

    return $fire;
}

beforeEach(function () {
    $this->reports = app(MetaTrackingReportService::class);
});

it('reports the funnel in funnel order with browser and server counts', function () {
    browserFire('PageView');
    browserFire('PageView');
    browserFire('AddToCart', 'atc_1');
    serverEvent('AddToCart', 'atc_1', MetaEventStatus::SENT);
    serverEvent('Purchase', 'order_1', MetaEventStatus::SENT);

    expect($this->reports->funnel(null))->toBe([
        ['event_name' => 'PageView', 'browser' => 2, 'server' => 0],
        ['event_name' => 'AddToCart', 'browser' => 1, 'server' => 1],
        ['event_name' => 'Purchase', 'browser' => 0, 'server' => 1],
    ]);
});

it('omits an event with no data rather than showing a permanent zero row', function () {
    browserFire('PageView');

    expect(collect($this->reports->funnel(null))->pluck('event_name')->all())
        ->toBe(['PageView']);
});

it('counts CAPI events by status', function () {
    serverEvent('Purchase', 'order_1', MetaEventStatus::SENT);
    serverEvent('Purchase', 'order_2', MetaEventStatus::SENT);
    serverEvent('AddToCart', 'atc_1', MetaEventStatus::FAILED);

    expect($this->reports->capiHealth(null))
        ->toBe(['pending' => 0, 'sent' => 2, 'failed' => 1]);
});

it('counts a matched pair as one deduplicated event', function () {
    browserFire('Purchase', 'order_1');
    serverEvent('Purchase', 'order_1', MetaEventStatus::SENT);

    expect($this->reports->deduplication(null))->toBe([
        'browser' => 1,
        'server' => 1,
        'matched' => 1,
        'deduplicated' => 1,
    ]);
});

it('shows an unmatched server event as a dedup gap', function () {
    browserFire('AddToCart', 'atc_1');
    serverEvent('AddToCart', 'atc_1', MetaEventStatus::SENT);
    // Ad-blocked in the browser, so CAPI is the only sender.
    serverEvent('Purchase', 'order_1', MetaEventStatus::SENT);

    expect($this->reports->deduplication(null))->toBe([
        'browser' => 1,
        'server' => 2,
        'matched' => 1,
        'deduplicated' => 2,
    ]);
});

it('excludes pending and failed events from the server dedup figure', function () {
    serverEvent('Purchase', 'order_1', MetaEventStatus::PENDING);
    serverEvent('Purchase', 'order_2', MetaEventStatus::FAILED);

    expect($this->reports->deduplication(null)['server'])->toBe(0);
});

it('ignores a browser fire with no event_id in the dedup figures', function () {
    browserFire('PageView');

    expect($this->reports->deduplication(null))->toBe([
        'browser' => 0,
        'server' => 0,
        'matched' => 0,
        'deduplicated' => 0,
    ]);
});

it('counts a repeated browser event_id once, so a double fire is visible in the funnel only', function () {
    browserFire('Purchase', 'order_1');
    browserFire('Purchase', 'order_1');

    expect($this->reports->deduplication(null)['browser'])->toBe(1)
        ->and($this->reports->funnel(null)[0]['browser'])->toBe(2);
});

it('scopes every figure to the requested range', function () {
    Carbon::setTestNow('2026-08-22 12:00:00');

    $old = Carbon::parse('2026-08-01 12:00:00');
    browserFire('Purchase', 'order_old', $old);
    serverEvent('Purchase', 'order_old', MetaEventStatus::SENT, $old);

    browserFire('Purchase', 'order_new');
    serverEvent('Purchase', 'order_new', MetaEventStatus::SENT);

    $from = Carbon::now()->subDays(7);

    expect($this->reports->funnel($from))->toBe([
        ['event_name' => 'Purchase', 'browser' => 1, 'server' => 1],
    ])
        ->and($this->reports->capiHealth($from)['sent'])->toBe(1)
        ->and($this->reports->deduplication($from)['deduplicated'])->toBe(1);
});

it('paginates recent events newest first', function () {
    serverEvent('AddToCart', 'atc_1', MetaEventStatus::SENT);
    serverEvent('Purchase', 'order_1', MetaEventStatus::FAILED);

    $page = $this->reports->recentEvents(null, perPage: 1);

    expect($page->total())->toBe(2)
        ->and($page->items()[0]->event_id)->toBe('order_1');
});

it('summarises the figures worth putting on the landing page', function () {
    browserFire('Purchase', 'order_1');
    serverEvent('Purchase', 'order_1', MetaEventStatus::SENT);
    serverEvent('Purchase', 'order_2', MetaEventStatus::SENT);
    serverEvent('AddToCart', 'atc_1', MetaEventStatus::SENT);

    expect($this->reports->summary(null))->toBe([
        'purchases' => 2,
        'matched' => 1,
        'deduplicated' => 3,
        'failed' => 0,
    ]);
});

it('counts a failed event outside the window, because it is still unreported', function () {
    Carbon::setTestNow('2026-08-22 12:00:00');

    serverEvent('Purchase', 'order_old', MetaEventStatus::FAILED, Carbon::parse('2026-07-01 12:00:00'));

    $summary = $this->reports->summary(Carbon::now()->subDays(7));

    expect($summary['failed'])->toBe(1)
        ->and($summary['purchases'])->toBe(0);
});
