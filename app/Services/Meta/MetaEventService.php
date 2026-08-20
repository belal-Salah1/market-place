<?php

namespace App\Services\Meta;

use App\Enums\MetaEventStatus;
use App\Jobs\SendMetaCapiEvent;
use App\Models\CartItem;
use App\Models\MetaEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class MetaEventService
{
    public function __construct(private readonly ConversionsApiClient $capi) {}

    /**
     * Track one add-to-cart on the Pixel and the Conversions API under a single
     * event_id, so Meta merges the pair instead of counting two.
     *
     * @return array<string, mixed>|null payload for the browser, null when there is nothing to track
     */
    public function addToCart(Request $request, CartItem $item, int $addedQuantity): ?array
    {
        if ($addedQuantity < 1 || blank(config('services.meta.pixel_id'))) {
            return null;
        }

        $product = $item->product;
        $eventId = "atc_{$item->id}_".now()->timestamp;

        $customData = [
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
        ];

        $this->queueServerEvent($request, $eventId, $customData);

        return [
            'name' => 'AddToCart',
            'event_id' => $eventId,
            'params' => $customData,
        ];
    }

    /**
     * @param  array<string, mixed>  $customData
     */
    private function queueServerEvent(Request $request, string $eventId, array $customData): void
    {
        if (! $this->capi->isConfigured()) {
            return;
        }

        // Tracking must never break the cart: a queue backend that is down (or a
        // sync driver surfacing a Meta outage) stays a logged warning.
        try {
            $event = MetaEvent::create([
                'event_name' => 'AddToCart',
                'event_id' => $eventId,
                'status' => MetaEventStatus::PENDING,
                'payload' => [
                    'event_name' => 'AddToCart',
                    'event_time' => now()->timestamp,
                    'event_id' => $eventId,
                    'event_source_url' => $request->headers->get('referer') ?? $request->fullUrl(),
                    'action_source' => 'website',
                    'user_data' => MetaUserData::fromRequest($request)->toArray(),
                    'custom_data' => $customData,
                ],
            ]);

            SendMetaCapiEvent::dispatch($event);
        } catch (Throwable $e) {
            Log::warning('Meta AddToCart could not be queued.', [
                'event_id' => $eventId,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
