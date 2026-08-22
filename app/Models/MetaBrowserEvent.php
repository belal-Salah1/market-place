<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * One Pixel fire that actually reached `fbevents.js` in a browser.
 *
 * Deliberately separate from MetaEvent: a browser fire is a completed fact with no
 * status, no attempts and nothing to retry, and keeping it out of `meta_events`
 * leaves that table's unique `event_id` — and the dedup guard in MetaEventService
 * that depends on it — working exactly as before.
 */
class MetaBrowserEvent extends Model
{
    protected $fillable = [
        'event_name',
        'event_id',
    ];
}
