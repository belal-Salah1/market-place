<?php

use App\Enums\MetaEventStatus;
use App\Jobs\SendMetaCapiEvent;
use App\Models\MetaBrowserEvent;
use App\Models\MetaEvent;
use App\Models\User;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    config()->set('services.meta.pixel_id', '1562473862273045');
    config()->set('services.meta.capi_token', 'test-token');
});

function trackedEvent(string $name, string $eventId, MetaEventStatus $status): MetaEvent
{
    return MetaEvent::create([
        'event_name' => $name,
        'event_id' => $eventId,
        'status' => $status,
        'payload' => ['event_name' => $name, 'event_id' => $eventId],
    ]);
}

it('is closed to a customer', function () {
    $this->actingAs(User::factory()->customer()->create())
        ->get(route('admin.tracking.index'))
        ->assertForbidden();
});

it('is closed to a vendor', function () {
    $this->actingAs(User::factory()->vendor()->approved()->create())
        ->get(route('admin.tracking.index'))
        ->assertForbidden();
});

it('shows an admin the funnel, CAPI health and dedup figures', function () {
    // The Vue page is a later task; don't fail this assertion over the component
    // file not existing on disk yet.
    config()->set('inertia.testing.ensure_pages_exist', false);

    MetaBrowserEvent::create(['event_name' => 'PageView']);
    MetaBrowserEvent::create(['event_name' => 'Purchase', 'event_id' => 'order_1']);
    trackedEvent('Purchase', 'order_1', MetaEventStatus::SENT);
    trackedEvent('AddToCart', 'atc_1', MetaEventStatus::FAILED);

    $this->withoutVite()
        ->actingAs(User::factory()->admin()->create())
        ->get(route('admin.tracking.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Admin/Tracking/Index')
            ->where('range', '7d')
            ->where('capi.sent', 1)
            ->where('capi.failed', 1)
            ->where('capi.pending', 0)
            ->where('dedup.browser', 1)
            ->where('dedup.server', 1)
            ->where('dedup.matched', 1)
            ->where('dedup.deduplicated', 1)
            ->where('pixelConfigured', true)
            ->where('capiConfigured', true)
            ->has('funnel', 3)
            ->has('events.data', 2)
        );
});

it('accepts a range and falls back to 7d on nonsense', function () {
    $admin = User::factory()->admin()->create();

    $this->withoutVite()
        ->actingAs($admin)
        ->get(route('admin.tracking.index', ['range' => 'all']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->where('range', 'all'));

    $this->withoutVite()
        ->actingAs($admin)
        ->get(route('admin.tracking.index', ['range' => 'nonsense']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->where('range', '7d'));
});

it('requeues a failed event', function () {
    Queue::fake();

    $event = trackedEvent('Purchase', 'order_1', MetaEventStatus::FAILED);
    $event->update(['last_error' => 'Invalid token']);

    $this->actingAs(User::factory()->admin()->create())
        ->post(route('admin.tracking.retry', $event))
        ->assertRedirect();

    expect($event->fresh()->status)->toBe(MetaEventStatus::PENDING)
        ->and($event->fresh()->last_error)->toBeNull();

    Queue::assertPushed(SendMetaCapiEvent::class, fn ($job) => $job->event->is($event));
});

it('refuses to requeue an event that already reached Meta', function () {
    Queue::fake();

    $event = trackedEvent('Purchase', 'order_1', MetaEventStatus::SENT);

    $this->actingAs(User::factory()->admin()->create())
        ->post(route('admin.tracking.retry', $event))
        ->assertRedirect();

    expect($event->fresh()->status)->toBe(MetaEventStatus::SENT);

    Queue::assertNothingPushed();
});

it('does not let a customer requeue an event', function () {
    Queue::fake();

    $event = trackedEvent('Purchase', 'order_1', MetaEventStatus::FAILED);

    $this->actingAs(User::factory()->customer()->create())
        ->post(route('admin.tracking.retry', $event))
        ->assertForbidden();

    Queue::assertNothingPushed();
});
