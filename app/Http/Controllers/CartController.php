<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCartItemRequest;
use App\Http\Requests\UpdateCartItemRequest;
use App\Models\CartItem;
use App\Models\Product;
use App\Services\CartService;
use App\Services\Meta\MetaEventService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class CartController extends Controller
{
    public function __construct(
        private readonly CartService $cart,
        private readonly MetaEventService $metaEvents,
    ) {}

    public function index(Request $request)
    {
        $cart = $this->cart->loadedForCustomer($request->user());

        return Inertia::render('Customer/Cart/Index', [
            'items' => $cart->items->values(),
            'total' => $cart->totalPrice(),
        ]);
    }

    public function store(StoreCartItemRequest $request)
    {
        $product = Product::findOrFail($request->validated('product_id'));

        if ($product->stock < 1) {
            return back()->withErrors(['product_id' => "{$product->name} is out of stock."]);
        }

        $addition = $this->cart->add($request->user(), $product, $request->validated('quantity'));

        $response = back()->with('success', "{$product->name} added to your cart.");

        if ($metaEvent = $this->metaEvents->addToCart($request, $addition->item, $addition->addedQuantity)) {
            $response->with('meta_event', $metaEvent);
        }

        return $response;
    }

    public function update(UpdateCartItemRequest $request, CartItem $cartItem)
    {
        Gate::authorize('update', $cartItem);

        $this->cart->updateQuantity($cartItem, $request->validated('quantity'));

        return back()->with('success', 'Cart updated.');
    }

    public function destroy(CartItem $cartItem)
    {
        Gate::authorize('delete', $cartItem);

        $this->cart->remove($cartItem);

        return back()->with('success', 'Item removed from your cart.');
    }
}
