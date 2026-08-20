<?php

namespace App\Jobs;

use App\Models\MetaEvent;
use App\Services\Meta\ConversionsApiClient;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

class SendMetaCapiEvent implements ShouldQueue
{
    use Queueable;

    public int $tries = 5;

    /** @var array<int, int> */
    public array $backoff = [10, 30, 60, 300, 900];

    public function __construct(public readonly MetaEvent $event) {}

    public function handle(ConversionsApiClient $capi): void
    {
        $this->event->increment('attempts');

        $capi->send($this->event->payload);

        $this->event->markSent();
    }

    public function failed(?Throwable $e): void
    {
        $this->event->markFailed($e?->getMessage() ?? 'unknown error');
    }
}
