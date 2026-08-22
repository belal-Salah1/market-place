<?php

use App\Models\MetaBrowserEvent;

beforeEach(function () {
    config()->set('services.meta.pixel_id', '1562473862273045');
});

it('records a fire from a guest, since guests get the pixel too', function () {
    $this->postJson(route('meta.browser-event'), ['event_name' => 'PageView'])
        ->assertNoContent();

    $event = MetaBrowserEvent::sole();

    expect($event->event_name)->toBe('PageView')
        ->and($event->event_id)->toBeNull();
});

it('records the event_id so the fire can be matched against a CAPI send', function () {
    $this->postJson(route('meta.browser-event'), [
        'event_name' => 'Purchase',
        'event_id' => 'order_9843',
    ])->assertNoContent();

    expect(MetaBrowserEvent::sole()->event_id)->toBe('order_9843');
});

it('rejects an event name outside the standard allowlist', function () {
    $this->postJson(route('meta.browser-event'), ['event_name' => 'NotAnEvent'])
        ->assertInvalid('event_name');

    expect(MetaBrowserEvent::count())->toBe(0);
});

it('requires an event name', function () {
    $this->postJson(route('meta.browser-event'), [])
        ->assertInvalid('event_name');
});

it('ignores numeric fields the browser tries to supply', function () {
    $this->postJson(route('meta.browser-event'), [
        'event_name' => 'Purchase',
        'event_id' => 'order_1',
        'value' => 999999,
        'currency' => 'USD',
    ])->assertNoContent();

    expect(MetaBrowserEvent::sole()->getAttributes())
        ->not->toHaveKey('value')
        ->not->toHaveKey('currency');
});

it('records nothing when the pixel is not configured', function () {
    config()->set('services.meta.pixel_id', null);

    $this->postJson(route('meta.browser-event'), ['event_name' => 'PageView'])
        ->assertNoContent();

    expect(MetaBrowserEvent::count())->toBe(0);
});

it('throttles a flood of beacons', function () {
    foreach (range(1, 60) as $ignored) {
        $this->postJson(route('meta.browser-event'), ['event_name' => 'PageView'])
            ->assertNoContent();
    }

    $this->postJson(route('meta.browser-event'), ['event_name' => 'PageView'])
        ->assertStatus(429);
});
