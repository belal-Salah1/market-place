<?php

use App\Enums\MetaEventStatus;
use App\Jobs\SendMetaCapiEvent;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\MetaEvent;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    config()->set('services.meta.pixel_id', '1562473862273045');
    config()->set('services.meta.capi_token', 'test-token');
    config()->set('services.meta.api_version', 'v21.0');
    config()->set('services.meta.currency', 'USD');
    config()->set('services.meta.test_event_code', null);
});

/**
 * @return array{0: User, 1: Product}
 */
function customerWithCart(float $price = 20.0, int $quantity = 3): array
{
    $user = User::factory()->customer()->create();
    $product = Product::factory()->create(['stock' => 10, 'price' => $price]);
    $cart = Cart::factory()->create(['customer_id' => $user->id]);

    CartItem::factory()->create([
        'cart_id' => $cart->id,
        'product_id' => $product->id,
        'quantity' => $quantity,
    ]);

    return [$user, $product];
}

function choosePaymentMethod(User $user, string $method = 'credit_card')
{
    return test()->actingAs($user)
        ->post(route('customer.checkout.payment-method'), ['payment_method' => $method]);
}

it('flashes an AddPaymentInfo payload for the pixel', function () {
    Queue::fake();

    [$user, $product] = customerWithCart(price: 20.0, quantity: 3);

    choosePaymentMethod($user, 'paypal')->assertRedirect();

    $payload = session('meta_event');

    expect($payload['name'])->toBe('AddPaymentInfo')
        ->and($payload['event_id'])->toStartWith('apinfo_')
        ->and($payload['params'])->toMatchArray([
            'value' => 60.0,
            'currency' => 'USD',
            'content_type' => 'product',
            'content_category' => 'paypal',
            'content_ids' => [(string) $product->id],
            'num_items' => 3,
        ])
        ->and($payload['params']['contents'])->toBe([[
            'id' => (string) $product->id,
            'quantity' => 3,
            'item_price' => 20.0,
        ]]);
});

it('queues a CAPI event sharing the pixel event_id', function () {
    Queue::fake();

    [$user] = customerWithCart();

    choosePaymentMethod($user);

    $record = MetaEvent::sole();

    expect($record->event_name)->toBe('AddPaymentInfo')
        ->and($record->event_id)->toBe(session('meta_event')['event_id'])
        ->and($record->status)->toBe(MetaEventStatus::PENDING)
        ->and($record->payload['custom_data']['content_category'])->toBe('credit_card');

    Queue::assertPushed(SendMetaCapiEvent::class, fn ($job) => $job->event->is($record));
});

it('reports each method the customer switches to', function () {
    Queue::fake();

    [$user] = customerWithCart();

    choosePaymentMethod($user, 'cash');
    choosePaymentMethod($user, 'paypal');

    expect(MetaEvent::pluck('payload')->map(fn ($p) => $p['custom_data']['content_category'])->all())
        ->toEqualCanonicalizing(['cash', 'paypal']);
});

it('rejects a payment method outside the enum', function () {
    Queue::fake();

    [$user] = customerWithCart();

    choosePaymentMethod($user, 'bitcoin')->assertSessionHasErrors('payment_method');

    expect(MetaEvent::count())->toBe(0);
});

it('tracks nothing when the cart is empty', function () {
    Queue::fake();

    $user = User::factory()->customer()->create();

    choosePaymentMethod($user);

    expect(session('meta_event'))->toBeNull()
        ->and(MetaEvent::count())->toBe(0);
});

it('tracks nothing at all when the pixel is not configured', function () {
    Queue::fake();
    config()->set('services.meta.pixel_id', null);

    [$user] = customerWithCart();

    choosePaymentMethod($user);

    expect(session('meta_event'))->toBeNull()
        ->and(MetaEvent::count())->toBe(0);
});
