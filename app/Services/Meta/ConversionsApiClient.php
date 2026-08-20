<?php

namespace App\Services\Meta;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class ConversionsApiClient
{
    public function isConfigured(): bool
    {
        return filled(config('services.meta.pixel_id'))
            && filled(config('services.meta.capi_token'));
    }

    /**
     * @param  array<string, mixed>  $event
     */
    public function send(array $event): Response
    {
        $pixelId = config('services.meta.pixel_id');
        $version = config('services.meta.api_version');

        return Http::asJson()
            ->timeout(15)
            ->post("https://graph.facebook.com/{$version}/{$pixelId}/events", array_filter([
                'data' => [$event],
                'access_token' => config('services.meta.capi_token'),
                'test_event_code' => config('services.meta.test_event_code'),
            ]))
            ->throw();
    }
}
