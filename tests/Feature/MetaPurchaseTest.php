<?php

use App\Enums\MetaEventStatus;
use App\Events\PaymentCompleted;
use App\Jobs\SendMetaCapiEvent;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\MetaEvent;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    config()->set('services.meta.pixel_id', '1562473862273045');
    config()->set('services.meta.capi_token', 'test-token');
    config()->set('services.meta.api_version', 'v21.0');
    config()->set('services.meta.currency', 'USD');
    config()->set('services.meta.test_event_code', null);
});

/**
 * A customer holding two products in their cart, priced 25.00 x 2 and 10.00 x 1.
 *
 * @return array{0: User, 1: Product, 2: Product}
 */
function customerReadyToPay(): array
{
    $user = User::factory()->customer()->create();
    $first = Product::factory()->create(['stock' => 10, 'price' => 25.00]);
    $second = Product::factory()->create(['stock' => 10, 'price' => 10.00]);
    $cart = Cart::factory()->create(['customer_id' => $user->id]);

    CartItem::factory()->create(['cart_id' => $cart->id, 'product_id' => $first->id, 'quantity' => 2]);
    CartItem::factory()->create(['cart_id' => $cart->id, 'product_id' => $second->id, 'quantity' => 1]);

    return [$user, $first, $second];
}

function placeOrder(User $user, string $method = 'cash')
{
    return test()->actingAs($user)
        ->post(route('customer.orders.store'), ['payment_method' => $method]);
}

it('dispatches PaymentCompleted once the order transaction commits', function () {
    Event::fake([PaymentCompleted::class]);

    [$user] = customerReadyToPay();

    placeOrder($user)->assertRedirect();

    $order = Order::sole();

    Event::assertDispatched(
        PaymentCompleted::class,
        fn (PaymentCompleted $event) => $event->order->is($order) && $event->order->exists
    );
});

it('flashes a Purchase payload for the pixel keyed on the order', function () {
    Queue::fake();

    [$user, $first, $second] = customerReadyToPay();

    placeOrder($user);

    $order = Order::sole();
    $payload = session('meta_event');

    expect($payload['name'])->toBe('Purchase')
        ->and($payload['event_id'])->toBe("order_{$order->id}")
        ->and($payload['params'])->toMatchArray([
            'value' => 60.0,
            'currency' => 'USD',
            'content_type' => 'product',
            'num_items' => 3,
        ])
        ->and($payload['params']['content_ids'])->toEqualCanonicalizing([
            (string) $first->id,
            (string) $second->id,
        ])
        ->and($payload['params']['contents'])->toEqualCanonicalizing([
            ['id' => (string) $first->id, 'quantity' => 2, 'item_price' => 25.0],
            ['id' => (string) $second->id, 'quantity' => 1, 'item_price' => 10.0],
        ]);
});

it('queues a CAPI Purchase linked to the order under the same event_id', function () {
    Queue::fake();

    [$user] = customerReadyToPay();

    placeOrder($user);

    $order = Order::sole();
    $record = MetaEvent::where('event_name', 'Purchase')->sole();

    expect($record->event_id)->toBe("order_{$order->id}")
        ->and($record->order_id)->toBe($order->id)
        ->and($record->status)->toBe(MetaEventStatus::PENDING)
        ->and($record->payload['action_source'])->toBe('website')
        ->and($record->payload['custom_data']['value'])->toEqual(60.0);

    Queue::assertPushed(SendMetaCapiEvent::class, fn ($job) => $job->event->is($record));
});

it('reports a paid order to Meta only once, however often payment is confirmed', function () {
    Queue::fake();

    [$user] = customerReadyToPay();

    placeOrder($user);

    $order = Order::sole();

    // A gateway retrying its webhook must not become a second purchase.
    event(PaymentCompleted::fromRequest($order, request()));
    event(PaymentCompleted::fromRequest($order, request()));

    expect(MetaEvent::where('event_name', 'Purchase')->count())->toBe(1);
});

it('sends the purchase to Meta with a hashed email', function () {
    Queue::fake();
    Http::fake(['graph.facebook.com/*' => Http::response(['events_received' => 1])]);

    [$user] = customerReadyToPay();
    $user->update(['email' => 'Buyer@Example.COM']);

    placeOrder($user);

    $event = MetaEvent::where('event_name', 'Purchase')->sole();
    (new SendMetaCapiEvent($event))->handle(app(App\Services\Meta\ConversionsApiClient::class));

    Http::assertSent(function ($request) {
        $sent = $request->data()['data'][0];

        return $sent['event_name'] === 'Purchase'
            && $sent['user_data']['em'] === [hash('sha256', 'buyer@example.com')]
            && ! str_contains(strtolower(json_encode($sent)), 'buyer@example.com');
    });

    expect($event->fresh()->status)->toBe(MetaEventStatus::SENT);
});

it('places the order even when tracking cannot be queued', function () {
    Queue::fake();
    Queue::shouldReceive('push')->andThrow(new RuntimeException('queue is down'));

    [$user] = customerReadyToPay();

    placeOrder($user)->assertRedirect();

    expect(Order::count())->toBe(1)
        ->and(Order::sole()->payment->amount)->toBe('60.00');
});

it('tracks nothing at all when the pixel is not configured', function () {
    Queue::fake();
    config()->set('services.meta.pixel_id', null);

    [$user] = customerReadyToPay();

    placeOrder($user)->assertRedirect();

    expect(session('meta_event'))->toBeNull()
        ->and(MetaEvent::count())->toBe(0)
        ->and(Order::count())->toBe(1);
});
