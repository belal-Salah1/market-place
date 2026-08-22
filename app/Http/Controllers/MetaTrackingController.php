<?php

namespace App\Http\Controllers;

use App\Enums\MetaEventStatus;
use App\Enums\MetaTrackingRange;
use App\Jobs\SendMetaCapiEvent;
use App\Models\MetaEvent;
use App\Services\Meta\ConversionsApiClient;
use App\Services\Meta\MetaTrackingReportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MetaTrackingController extends Controller
{
    public function __construct(
        private readonly MetaTrackingReportService $reports,
        private readonly ConversionsApiClient $capi,
    ) {}

    public function index(Request $request): Response
    {
        // An unrecognised range falls back rather than 422ing a dashboard.
        $range = MetaTrackingRange::tryFrom((string) $request->query('range'))
            ?? MetaTrackingRange::WEEK;

        $from = $range->since();

        return Inertia::render('Admin/Tracking/Index', [
            'range' => $range->value,
            'ranges' => MetaTrackingRange::values(),
            'funnel' => $this->reports->funnel($from),
            'capi' => $this->reports->capiHealth($from),
            'dedup' => $this->reports->deduplication($from),
            'events' => $this->reports->recentEvents($from),
            'pixelConfigured' => filled(config('services.meta.pixel_id')),
            'capiConfigured' => $this->capi->isConfigured(),
        ]);
    }

    /**
     * Send one failed event back to the queue. Without this `markFailed` is
     * terminal, so a Meta outage past the job's five attempts leaves a real
     * conversion unreported with no way to recover it.
     */
    public function retry(MetaEvent $event): RedirectResponse
    {
        if ($event->status !== MetaEventStatus::FAILED) {
            return back()->with('error', 'Only a failed event can be retried.');
        }

        $event->markPending();

        SendMetaCapiEvent::dispatch($event);

        return back()->with('success', "Requeued {$event->event_name} ({$event->event_id}).");
    }
}
