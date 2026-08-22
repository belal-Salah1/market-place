<?php

namespace App\Http\Requests;

use App\Enums\MetaStandardEvent;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * This endpoint is public and unauthenticated, so the body is hostile input. The
 * allowlist means a caller can only create rows for events we already expect, and
 * nothing numeric is accepted — a browser-supplied `value` would be trivially
 * forgeable, so the dashboard never reads one.
 */
class StoreMetaBrowserEventRequest extends FormRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'event_name' => ['required', 'string', Rule::in(MetaStandardEvent::names())],
            'event_id' => ['nullable', 'string', 'max:191'],
        ];
    }
}
