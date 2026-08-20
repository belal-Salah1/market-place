<?php

namespace App\Events;

use App\Models\Order;
use App\Services\Meta\MetaEventService;
use App\Services\Meta\MetaUserData;
use Illuminate\Http\Request;

/**
 * Payment for an order is confirmed and the money is real.
 *
 * The marketplace settles inline today, so this is dispatched once the payment
 * row is committed as COMPLETED. When a real gateway webhook lands it dispatches
 * this same event and nothing downstream changes.
 *
 * Matching signals are captured at dispatch time because they come from the
 * customer's browser (_fbp/_fbc cookies, their IP, their user agent) — a webhook
 * request has none of those, so it must supply them from stored attribution.
 */
class PaymentCompleted
{
    public function __construct(
        public readonly Order $order,
        public readonly MetaUserData $userData,
        public readonly ?string $sourceUrl = null,
    ) {}

    public static function fromRequest(Order $order, Request $request): self
    {
        return new self(
            order: $order,
            userData: MetaUserData::fromRequest($request),
            sourceUrl: MetaEventService::sourceUrl($request),
        );
    }
}
