<?php

use App\Models\MetaBrowserEvent;

it('records the same event_id twice so a double fire stays visible', function () {
    MetaBrowserEvent::create(['event_name' => 'AddToCart', 'event_id' => 'atc_1']);
    MetaBrowserEvent::create(['event_name' => 'AddToCart', 'event_id' => 'atc_1']);

    expect(MetaBrowserEvent::count())->toBe(2);
});

it('records a fire that carries no event_id', function () {
    $event = MetaBrowserEvent::create(['event_name' => 'PageView']);

    expect($event->event_name)->toBe('PageView')
        ->and($event->event_id)->toBeNull();
});
