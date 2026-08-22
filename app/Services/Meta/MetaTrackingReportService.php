<?php

namespace App\Services\Meta;

use App\Enums\MetaEventStatus;
use App\Enums\MetaStandardEvent;
use App\Models\MetaBrowserEvent;
use App\Models\MetaEvent;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Aggregation behind the admin tracking dashboard. Every method takes a nullable
 * `$from`; null means all time.
 */
class MetaTrackingReportService
{
    /**
     * Browser fires against Conversions API rows, per event, in funnel order.
     *
     * Rows come from the data actually present, so an event we have not shipped yet
     * is simply absent instead of a permanent zero row.
     *
     * @return array<int, array{event_name: string, browser: int, server: int}>
     */
    public function funnel(?Carbon $from): array
    {
        $browser = $this->countsByEventName(MetaBrowserEvent::query(), $from);
        $server = $this->countsByEventName(MetaEvent::query(), $from);

        return $browser->keys()
            ->merge($server->keys())
            ->unique()
            ->sortBy(fn (string $name) => MetaStandardEvent::position($name))
            ->values()
            ->map(fn (string $name) => [
                'event_name' => $name,
                'browser' => $browser->get($name, 0),
                'server' => $server->get($name, 0),
            ])
            ->all();
    }

    /**
     * Every status is present in the result, including the ones sitting at zero —
     * "Failed 0" is information the dashboard needs to state.
     *
     * @return array<string, int>
     */
    public function capiHealth(?Carbon $from): array
    {
        $counts = MetaEvent::query()
            ->when($from, fn (Builder $query) => $query->where('created_at', '>=', $from))
            ->groupBy('status')
            ->selectRaw('status, count(*) as total')
            ->get()
            ->mapWithKeys(fn (MetaEvent $row) => [$row->status->value => (int) $row->total]);

        return collect(MetaEventStatus::cases())
            ->mapWithKeys(fn (MetaEventStatus $status) => [
                $status->value => $counts->get($status->value, 0),
            ])
            ->all();
    }

    /**
     * What Meta ends up counting once it merges our pairs.
     *
     * Only `sent` rows can deduplicate: a pending or failed event never reached
     * Meta, so it has nothing to merge with. `matched` is the health signal — well
     * below either side means Meta is counting two events where we intended one.
     *
     * @return array{browser: int, server: int, matched: int, deduplicated: int}
     */
    public function deduplication(?Carbon $from): array
    {
        $browserIds = MetaBrowserEvent::query()
            ->when($from, fn (Builder $query) => $query->where('created_at', '>=', $from))
            ->whereNotNull('event_id')
            ->distinct()
            ->pluck('event_id');

        $serverIds = MetaEvent::query()
            ->when($from, fn (Builder $query) => $query->where('created_at', '>=', $from))
            ->where('status', MetaEventStatus::SENT)
            ->distinct()
            ->pluck('event_id');

        return [
            'browser' => $browserIds->count(),
            'server' => $serverIds->count(),
            'matched' => $browserIds->intersect($serverIds)->count(),
            'deduplicated' => $browserIds->merge($serverIds)->unique()->count(),
        ];
    }

    /**
     * The handful of figures worth putting on the admin landing page.
     *
     * `failed` is deliberately NOT windowed. An event that failed three weeks ago is
     * still a conversion Meta never received, so letting it age out of the range
     * would quietly hide unreported revenue.
     *
     * @return array{purchases: int, matched: int, deduplicated: int, failed: int}
     */
    public function summary(?Carbon $from): array
    {
        $dedup = $this->deduplication($from);

        return [
            'purchases' => MetaEvent::query()
                ->when($from, fn (Builder $query) => $query->where('created_at', '>=', $from))
                ->where('event_name', MetaStandardEvent::PURCHASE->value)
                ->where('status', MetaEventStatus::SENT)
                ->count(),
            'matched' => $dedup['matched'],
            'deduplicated' => $dedup['deduplicated'],
            'failed' => MetaEvent::where('status', MetaEventStatus::FAILED)->count(),
        ];
    }

    /**
     * The debugging view: what we sent, whether it landed, and why it did not.
     */
    public function recentEvents(?Carbon $from, int $perPage = 15): LengthAwarePaginator
    {
        return MetaEvent::query()
            ->when($from, fn (Builder $query) => $query->where('created_at', '>=', $from))
            ->latest('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Grouped counts keyed by event name. `get()` then map rather than `pluck()`,
     * because `pluck` rebuilds the select and loses the aggregate column.
     *
     * @return Collection<string, int>
     */
    private function countsByEventName(Builder $query, ?Carbon $from): Collection
    {
        return $query
            ->when($from, fn (Builder $q) => $q->where('created_at', '>=', $from))
            ->groupBy('event_name')
            ->selectRaw('event_name, count(*) as total')
            ->get()
            ->mapWithKeys(fn ($row) => [$row->event_name => (int) $row->total]);
    }
}
