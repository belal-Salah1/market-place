<?php

namespace App\Services\Meta;

use App\Enums\MetaEventStatus;
use App\Enums\PaymentMethodStatus;
use App\Jobs\SendMetaCapiEvent;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\MetaEvent;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class MetaEventService
{
    public function __construct(private readonly ConversionsApiClient $capi) {}

    /**
     * Track one add-to-cart on the Pixel and the Conversions API.
     */
    public function addToCart(Request $request, CartItem $item, int $addedQuantity): void
    {
        if ($addedQuantity < 1) {
            return;
        }

        $product = $item->product;

        $this->dualSend(
            eventName: 'AddToCart',
            eventId: "atc_{$item->id}_".now()->timestamp,
            customData: [
                'value' => round((float) $product->price * $addedQuantity, 2),
                'currency' => config('services.meta.currency'),
                'content_type' => 'product',
                'content_ids' => [(string) $product->id],
                'contents' => [[
                    'id' => (string) $product->id,
                    'quantity' => $addedQuantity,
                    'item_price' => round((float) $product->price, 2),
                ]],
                'num_items' => $addedQuantity,
            ],
            userData: MetaUserData::fromRequest($request),
            sourceUrl: self::sourceUrl($request),
        );
    }

    /**
     * A new account was created. The id is derived from the user so the same
     * signup can never be reported twice, however it was created.
     */
    public function completeRegistration(Request $request, User $user): void
    {
        $this->dualSend(
            eventName: 'CompleteRegistration',
            eventId: "registration_{$user->id}",
            customData: [
                // No money changes hands at signup, so `value` is deliberately
                // omitted rather than reported as a zero-value conversion.
                'content_name' => $user->role?->name,
                'status' => true,
            ],
            userData: MetaUserData::fromRequest($request),
            sourceUrl: self::sourceUrl($request),
        );
    }

    /**
     * The customer picked how they intend to pay. Fires per selection — swapping
     * the method is a fresh signal, so the id carries a timestamp.
     */
    public function addPaymentInfo(Request $request, Cart $cart, PaymentMethodStatus $method): void
    {
        if ($cart->items->isEmpty()) {
            return;
        }

        $this->dualSend(
            eventName: 'AddPaymentInfo',
            eventId: "apinfo_{$cart->id}_{$method->value}_".now()->timestamp,
            customData: [
                'value' => round($cart->totalPrice(), 2),
                'currency' => config('services.meta.currency'),
                'content_type' => 'product',
                'content_category' => $method->value,
                'content_ids' => $cart->items->pluck('product_id')->map(strval(...))->all(),
                'contents' => $cart->items->map(fn (CartItem $item) => [
                    'id' => (string) $item->product_id,
                    'quantity' => (int) $item->quantity,
                    'item_price' => round((float) $item->product->price, 2),
                ])->all(),
                'num_items' => $cart->totalQuantity(),
            ],
            userData: MetaUserData::fromRequest($request),
            sourceUrl: self::sourceUrl($request),
        );
    }

    /**
     * The money is real. Called off the PaymentCompleted event rather than the
     * "Place Order" request, so the confirmed payment is what triggers it — and
     * `order_{id}` keeps a gateway retry from reporting a second purchase.
     */
    public function purchase(Order $order, MetaUserData $userData, ?string $sourceUrl = null): void
    {
        $order->loadMissing('items');

        $this->dualSend(
            eventName: 'Purchase',
            eventId: "order_{$order->id}",
            customData: [
                'value' => round((float) $order->total_price, 2),
                'currency' => config('services.meta.currency'),
                'content_type' => 'product',
                'content_ids' => $order->items->pluck('product_id')->map(strval(...))->all(),
                'contents' => $order->items->map(fn ($item) => [
                    'id' => (string) $item->product_id,
                    'quantity' => (int) $item->quantity,
                    'item_price' => round((float) $item->price, 2),
                ])->all(),
                'num_items' => (int) $order->items->sum('quantity'),
            ],
            userData: $userData,
            sourceUrl: $sourceUrl,
            orderId: $order->id,
        );
    }

    /**
     * Send one event twice under a single event_id — once from the browser Pixel
     * (flashed for Inertia to pick up) and once from the Conversions API — so
     * Meta merges the pair instead of counting two.
     *
     * @param  array<string, mixed>  $customData
     */
    private function dualSend(
        string $eventName,
        string $eventId,
        array $customData,
        MetaUserData $userData,
        ?string $sourceUrl,
        ?int $orderId = null,
    ): void {
        if (blank(config('services.meta.pixel_id'))) {
            return;
        }

        session()->flash('meta_event', [
            'name' => $eventName,
            'event_id' => $eventId,
            'params' => array_filter($customData, fn ($value) => $value !== null),
        ]);

        $this->queueServerEvent($eventName, $eventId, $customData, $userData, $sourceUrl, $orderId);
    }

    /**
     * @param  array<string, mixed>  $customData
     */
    private function queueServerEvent(
        string $eventName,
        string $eventId,
        array $customData,
        MetaUserData $userData,
        ?string $sourceUrl,
        ?int $orderId,
    ): void {
        if (! $this->capi->isConfigured() || MetaEvent::where('event_id', $eventId)->exists()) {
            return;
        }

        // Tracking must never break the checkout: a queue backend that is down (or a
        // sync driver surfacing a Meta outage) stays a logged warning.
        try {
            $event = MetaEvent::create([
                'event_name' => $eventName,
                'event_id' => $eventId,
                'order_id' => $orderId,
                'status' => MetaEventStatus::PENDING,
                'payload' => array_filter([
                    'event_name' => $eventName,
                    'event_time' => now()->timestamp,
                    'event_id' => $eventId,
                    'event_source_url' => $sourceUrl,
                    'action_source' => 'website',
                    'user_data' => $userData->toArray(),
                    'custom_data' => array_filter($customData, fn ($value) => $value !== null),
                ], fn ($value) => $value !== null),
            ]);

            SendMetaCapiEvent::dispatch($event);
        } catch (Throwable $e) {
            Log::warning("Meta {$eventName} could not be queued.", [
                'event_id' => $eventId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * The page the customer was actually on, not the endpoint that handled the post.
     */
    public static function sourceUrl(Request $request): string
    {
        return $request->headers->get('referer') ?? $request->fullUrl();
    }
}
