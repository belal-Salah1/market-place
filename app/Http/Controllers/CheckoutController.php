<?php

namespace App\Http\Controllers;

use App\Enums\PaymentMethodStatus;
use App\Http\Requests\TrackPaymentMethodRequest;
use App\Services\CartService;
use App\Services\Meta\MetaEventService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CheckoutController extends Controller
{
    public function __construct(
        private readonly CartService $cart,
        private readonly MetaEventService $metaEvents,
    ) {}

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

    /**
     * The checkout page pings this when the customer picks a payment method, so
     * the server mints the event_id and AddPaymentInfo goes out on both sides.
     */
    public function paymentMethod(TrackPaymentMethodRequest $request)
    {
        $cart = $this->cart->loadedForCustomer($request->user());

        $this->metaEvents->addPaymentInfo($request, $cart, $request->paymentMethod());

        return back();
    }
}
