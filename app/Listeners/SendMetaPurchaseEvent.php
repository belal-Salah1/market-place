<?php

namespace App\Listeners;

use App\Events\PaymentCompleted;
use App\Services\Meta\MetaEventService;

class SendMetaPurchaseEvent
{
    public function __construct(private readonly MetaEventService $metaEvents) {}

    public function handle(PaymentCompleted $event): void
    {
        $this->metaEvents->purchase($event->order, $event->userData, $event->sourceUrl);
    }
}
