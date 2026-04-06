<?php

namespace App\Models;

use App\Enums\PaymentMethodStatus;
use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'amount',
        'method',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'method' => PaymentMethodStatus::class,
            'status' => PaymentStatus::class,
            'amount' => 'decimal:2',
        ];
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
