<?php

use App\Enums\MetaEventStatus;
use App\Jobs\SendMetaCapiEvent;
use App\Models\CartItem;
use App\Models\MetaEvent;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    config()->set('services.meta.pixel_id', '1562473862273045');
    config()->set('services.meta.capi_token', 'test-token');
    config()->set('services.meta.api_version', 'v21.0');
    config()->set('services.meta.currency', 'USD');
    config()->set('services.meta.test_event_code', null);
});

function addToCart(User $user, Product $product, int $quantity = 2)
{
    return test()->actingAs($user)
        ->post(route('customer.cart.store'), ['product_id' => $product->id, 'quantity' => $quantity]);
}

it('flashes an AddToCart payload for the pixel', function () {
    Queue::fake();

    $user = User::factory()->customer()->create();
    $product = Product::factory()->create(['stock' => 10, 'price' => 25.50]);

    addToCart($user, $product, 2)->assertRedirect();

    $payload = session('meta_event');

    expect($payload['name'])->toBe('AddToCart')
        ->and($payload['event_id'])->toStartWith('atc_')
        ->and($payload['params'])->toMatchArray([
            'value' => 51.0,
            'currency' => 'USD',
            'content_type' => 'product',
            'content_ids' => [(string) $product->id],
            'num_items' => 2,
        ])
        ->and($payload['params']['contents'])->toBe([[
            'id' => (string) $product->id,
            'quantity' => 2,
            'item_price' => 25.5,
        ]]);
});

it('exposes the payload to the browser as an inertia prop', function () {
    Queue::fake();

    $user = User::factory()->customer()->create();
    $product = Product::factory()->create(['stock' => 10, 'price' => 10]);

    addToCart($user, $product, 1);

    $this->actingAs($user)->get(route('customer.cart.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->where('metaEvent.name', 'AddToCart'));
});

it('queues a CAPI event sharing the pixel event_id', function () {
    Queue::fake();

    $user = User::factory()->customer()->create();
    $product = Product::factory()->create(['stock' => 10, 'price' => 10]);

    addToCart($user, $product, 3);

    $eventId = session('meta_event')['event_id'];
    $record = MetaEvent::sole();

    expect($record->event_name)->toBe('AddToCart')
        ->and($record->event_id)->toBe($eventId)
        ->and($record->status)->toBe(MetaEventStatus::PENDING)
        ->and($record->attempts)->toBe(0)
        ->and($record->payload['action_source'])->toBe('website')
        ->and($record->payload['custom_data']['num_items'])->toBe(3);

    Queue::assertPushed(SendMetaCapiEvent::class, fn ($job) => $job->event->is($record));
});

it('hashes the email and never sends it raw', function () {
    Queue::fake();
    Http::fake([
        'graph.facebook.com/*' => Http::response(['events_received' => 1]),
    ]);

    $user = User::factory()->customer()->create(['email' => ' Belal@Example.COM ']);
    $product = Product::factory()->create(['stock' => 10, 'price' => 10]);

    addToCart($user, $product, 1);

    $event = MetaEvent::sole();
    (new SendMetaCapiEvent($event))->handle(app(\App\Services\Meta\ConversionsApiClient::class));

    $expected = hash('sha256', 'belal@example.com');

    Http::assertSent(function ($request) use ($expected) {
        $sent = $request->data();
        $userData = $sent['data'][0]['user_data'];

        return str_contains($request->url(), '/v21.0/1562473862273045/events')
            && $userData['em'] === [$expected]
            && ! str_contains(json_encode($sent), 'Belal@Example.COM')
            && ! str_contains(strtolower(json_encode($sent)), 'belal@example.com');
    });

    expect($event->fresh()->status)->toBe(MetaEventStatus::SENT)
        ->and($event->fresh()->sent_at)->not->toBeNull()
        ->and($event->fresh()->attempts)->toBe(1);
});

it('records the error when Meta rejects the event', function () {
    Queue::fake();
    Http::fake([
        'graph.facebook.com/*' => Http::response(['error' => ['message' => 'Invalid token']], 400),
    ]);

    $user = User::factory()->customer()->create();
    $product = Product::factory()->create(['stock' => 10, 'price' => 10]);

    addToCart($user, $product, 1);

    $event = MetaEvent::sole();
    $job = new SendMetaCapiEvent($event);

    expect(fn () => $job->handle(app(\App\Services\Meta\ConversionsApiClient::class)))
        ->toThrow(Illuminate\Http\Client\RequestException::class);

    $job->failed(new Exception('Invalid token'));

    expect($event->fresh()->status)->toBe(MetaEventStatus::FAILED)
        ->and($event->fresh()->last_error)->toContain('Invalid token');
});

it('fires nothing when the quantity was clamped to zero', function () {
    Queue::fake();

    $user = User::factory()->customer()->create();
    $product = Product::factory()->create(['stock' => 2, 'price' => 10]);

    addToCart($user, $product, 2);
    session()->forget('meta_event');

    addToCart($user, $product, 5);

    expect(session('meta_event'))->toBeNull()
        ->and(CartItem::sole()->quantity)->toBe(2)
        ->and(MetaEvent::count())->toBe(1);
});

it('still fires the pixel but skips CAPI when no token is configured', function () {
    Queue::fake();
    config()->set('services.meta.capi_token', null);

    $user = User::factory()->customer()->create();
    $product = Product::factory()->create(['stock' => 10, 'price' => 10]);

    addToCart($user, $product, 1);

    expect(session('meta_event')['name'])->toBe('AddToCart')
        ->and(MetaEvent::count())->toBe(0);

    Queue::assertNothingPushed();
});

it('tracks nothing at all when the pixel is not configured', function () {
    Queue::fake();
    config()->set('services.meta.pixel_id', null);

    $user = User::factory()->customer()->create();
    $product = Product::factory()->create(['stock' => 10, 'price' => 10]);

    addToCart($user, $product, 1);

    expect(session('meta_event'))->toBeNull()
        ->and(MetaEvent::count())->toBe(0);
});
