<?php

namespace App\Services;

use App\Models\CartItem;

/**
 * Stock clamping means the quantity asked for is not always the quantity added,
 * and tracking needs the amount that actually landed in the cart.
 */
class CartAddition
{
    public function __construct(
        public readonly CartItem $item,
        public readonly int $addedQuantity,
    ) {}
}
