<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    /** @use HasFactory<\Database\Factories\CartFactory> */
    use HasFactory;

    protected $fillable = [
        'customer_id',
    ];

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function items()
    {
        return $this->hasMany(CartItem::class);
    }

    /**
     * Live total, priced from the products rather than a stored snapshot.
     */
    public function totalPrice(): float
    {
        return (float) $this->items->sum(fn (CartItem $item) => $item->subtotal());
    }

    public function totalQuantity(): int
    {
        return (int) $this->items->sum('quantity');
    }
}
