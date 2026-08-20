<?php

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;

function customer(): User
{
    return User::factory()->customer()->create();
}

it('adds a product to the cart', function () {
    $user = customer();
    $product = Product::factory()->create(['stock' => 10]);

    $this->actingAs($user)
        ->post(route('customer.cart.store'), ['product_id' => $product->id, 'quantity' => 2])
        ->assertRedirect();

    expect(Cart::where('customer_id', $user->id)->exists())->toBeTrue();
    expect(CartItem::first())->product_id->toBe($product->id)->quantity->toBe(2);
});

it('tops up the quantity when the same product is added twice', function () {
    $user = customer();
    $product = Product::factory()->create(['stock' => 10]);

    $this->actingAs($user)->post(route('customer.cart.store'), ['product_id' => $product->id, 'quantity' => 2]);
    $this->actingAs($user)->post(route('customer.cart.store'), ['product_id' => $product->id, 'quantity' => 3]);

    expect(CartItem::count())->toBe(1)
        ->and(CartItem::first()->quantity)->toBe(5);
});

it('never lets the cart quantity exceed available stock', function () {
    $user = customer();
    $product = Product::factory()->create(['stock' => 4]);

    $this->actingAs($user)->post(route('customer.cart.store'), ['product_id' => $product->id, 'quantity' => 99]);

    expect(CartItem::first()->quantity)->toBe(4);
});

it('rejects an out of stock product', function () {
    $user = customer();
    $product = Product::factory()->create(['stock' => 0]);

    $this->actingAs($user)
        ->post(route('customer.cart.store'), ['product_id' => $product->id, 'quantity' => 1])
        ->assertSessionHasErrors('product_id');

    expect(CartItem::count())->toBe(0);
});

it('updates and removes a cart item', function () {
    $user = customer();
    $product = Product::factory()->create(['stock' => 10]);
    $cart = Cart::factory()->create(['customer_id' => $user->id]);
    $item = CartItem::factory()->create(['cart_id' => $cart->id, 'product_id' => $product->id, 'quantity' => 1]);

    $this->actingAs($user)->patch(route('customer.cart.update', $item), ['quantity' => 4]);
    expect($item->fresh()->quantity)->toBe(4);

    $this->actingAs($user)->delete(route('customer.cart.destroy', $item));
    expect(CartItem::count())->toBe(0);
});

it('forbids touching another customer cart item', function () {
    $owner = customer();
    $intruder = customer();
    $cart = Cart::factory()->create(['customer_id' => $owner->id]);
    $item = CartItem::factory()->create(['cart_id' => $cart->id, 'quantity' => 1]);

    $this->actingAs($intruder)->patch(route('customer.cart.update', $item), ['quantity' => 9])->assertForbidden();
    $this->actingAs($intruder)->delete(route('customer.cart.destroy', $item))->assertForbidden();

    expect($item->fresh()->quantity)->toBe(1);
});

it('shares the cart count with customers only', function () {
    $user = customer();
    $product = Product::factory()->create(['stock' => 10]);
    $this->actingAs($user)->post(route('customer.cart.store'), ['product_id' => $product->id, 'quantity' => 3]);

    $this->actingAs($user)->get(route('customer.cart.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->where('cartCount', 3));

    $this->actingAs(User::factory()->admin()->create())->get(route('admin.dashboard'))
        ->assertInertia(fn ($page) => $page->where('cartCount', 0));
});

it('redirects to the cart when checking out with an empty cart', function () {
    $this->actingAs(customer())
        ->get(route('customer.checkout.index'))
        ->assertRedirect(route('customer.cart.index'));
});

it('shows checkout when the cart has items', function () {
    $user = customer();
    $product = Product::factory()->create(['stock' => 10, 'price' => 25]);
    $cart = Cart::factory()->create(['customer_id' => $user->id]);
    CartItem::factory()->create(['cart_id' => $cart->id, 'product_id' => $product->id, 'quantity' => 2]);

    $this->actingAs($user)->get(route('customer.checkout.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Customer/Checkout/Index')->where('total', 50));
});

it('places an order from the cart and clears it', function () {
    $user = customer();
    $a = Product::factory()->create(['stock' => 10, 'price' => 30]);
    $b = Product::factory()->create(['stock' => 10, 'price' => 20]);
    $cart = Cart::factory()->create(['customer_id' => $user->id]);
    CartItem::factory()->create(['cart_id' => $cart->id, 'product_id' => $a->id, 'quantity' => 2]);
    CartItem::factory()->create(['cart_id' => $cart->id, 'product_id' => $b->id, 'quantity' => 1]);

    $this->actingAs($user)
        ->post(route('customer.orders.store'), ['payment_method' => 'cash'])
        ->assertRedirect();

    $order = Order::first();

    expect((float) $order->total_price)->toBe(80.0)
        ->and($order->status)->toBe(OrderStatus::PENDING)
        ->and($order->items)->toHaveCount(2)
        ->and($order->payment->status)->toBe(PaymentStatus::COMPLETED)
        ->and($a->fresh()->stock)->toBe(8)
        ->and($b->fresh()->stock)->toBe(9)
        ->and(CartItem::count())->toBe(0);
});

it('refuses to place an order with an empty cart', function () {
    $this->actingAs(customer())
        ->post(route('customer.orders.store'), ['payment_method' => 'cash'])
        ->assertRedirect(route('customer.cart.index'));

    expect(Order::count())->toBe(0);
});

it('refuses to place an order when stock dropped below the cart quantity', function () {
    $user = customer();
    $product = Product::factory()->create(['stock' => 10]);
    $cart = Cart::factory()->create(['customer_id' => $user->id]);
    CartItem::factory()->create(['cart_id' => $cart->id, 'product_id' => $product->id, 'quantity' => 5]);

    $product->update(['stock' => 2]);

    $this->actingAs($user)
        ->post(route('customer.orders.store'), ['payment_method' => 'cash'])
        ->assertSessionHasErrors('items');

    expect(Order::count())->toBe(0)
        ->and($product->fresh()->stock)->toBe(2);
});
