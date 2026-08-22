<?php

namespace App\Models;

use App\Enums\MetaEventStatus;
use Illuminate\Database\Eloquent\Model;

class MetaEvent extends Model
{
    protected $fillable = [
        'event_name',
        'event_id',
        'order_id',
        'payload',
        'status',
        'attempts',
        'last_error',
        'sent_at',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'status' => MetaEventStatus::class,
            'attempts' => 'integer',
            'sent_at' => 'datetime',
        ];
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function markSent(): void
    {
        $this->update([
            'status' => MetaEventStatus::SENT,
            'sent_at' => now(),
            'last_error' => null,
        ]);
    }

    public function markFailed(string $error): void
    {
        $this->update([
            'status' => MetaEventStatus::FAILED,
            'last_error' => $error,
        ]);
    }

    /**
     * Put a failed event back in the queue. `attempts` deliberately keeps climbing
     * rather than resetting — the cumulative number is the useful diagnostic.
     */
    public function markPending(): void
    {
        $this->update([
            'status' => MetaEventStatus::PENDING,
            'last_error' => null,
        ]);
    }
}
