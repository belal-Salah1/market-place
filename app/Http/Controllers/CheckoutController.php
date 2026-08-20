<?php

namespace App\Http\Controllers;

use App\Enums\PaymentMethodStatus;
use App\Services\CartService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CheckoutController extends Controller
{
    public function __construct(private readonly CartService $cart) {}

    public function index(Request $request)
    {
        $cart = $this->cart->loadedForCustomer($request->user());

        if ($cart->items->isEmpty()) {
            return redirect()->route('customer.cart.index')
                ->with('error', 'Your cart is empty.');
        }

        return Inertia::render('Customer/Checkout/Index', [
            'items' => $cart->items->values(),
            'total' => $cart->totalPrice(),
            'paymentMethods' => array_map(
                fn ($m) => ['value' => $m->value, 'label' => ucwords(str_replace('_', ' ', $m->value))],
                PaymentMethodStatus::cases()
            ),
        ]);
    }
}
