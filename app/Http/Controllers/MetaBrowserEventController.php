<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMetaBrowserEventRequest;
use App\Models\MetaBrowserEvent;
use Illuminate\Http\Response;

class MetaBrowserEventController extends Controller
{
    /**
     * The Pixel calls this the moment `fbq` has actually run, so the dashboard can
     * compare what the browser really sent against what the Conversions API sent.
     * Flashing a payload only proves we asked for a fire; this proves one happened.
     */
    public function store(StoreMetaBrowserEventRequest $request): Response
    {
        // Mirrors the guard in MetaEventService::dualSend — no pixel, no tracking.
        if (filled(config('services.meta.pixel_id'))) {
            MetaBrowserEvent::create($request->safe()->only(['event_name', 'event_id']));
        }

        return response()->noContent();
    }
}
