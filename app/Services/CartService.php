<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;

class CartService
{
    public function forCustomer(User $customer): Cart
    {
        return Cart::firstOrCreate(['customer_id' => $customer->id]);
    }

    /**
     * Load a customer's cart with everything the cart and checkout pages need.
     */
    public function loadedForCustomer(User $customer): Cart
    {
        $cart = $this->forCustomer($customer);
        $cart->load(['items.product.category', 'items.product.vendor']);

        return $cart;
    }

    /**
     * Adding a product already in the cart tops up its quantity instead of
     * creating a second row.
     */
    public function add(User $customer, Product $product, int $quantity): CartAddition
    {
        $cart = $this->forCustomer($customer);

        $item = $cart->items()->firstOrNew(['product_id' => $product->id]);
        $before = $item->quantity ?? 0;
        $item->quantity = min($product->stock, $before + $quantity);
        $item->save();

        return new CartAddition($item, $item->quantity - $before);
    }

    public function updateQuantity(CartItem $item, int $quantity): CartItem
    {
        $item->update(['quantity' => min($item->product->stock, $quantity)]);

        return $item;
    }

    public function remove(CartItem $item): void
    {
        $item->delete();
    }

    public function clear(Cart $cart): void
    {
        $cart->items()->delete();
    }

    public function itemCount(User $customer): int
    {
        return (int) CartItem::whereRelation('cart', 'customer_id', $customer->id)->sum('quantity');
    }
}
