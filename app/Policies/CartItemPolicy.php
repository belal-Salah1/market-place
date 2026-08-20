<?php

namespace App\Policies;

use App\Models\CartItem;
use App\Models\User;

class CartItemPolicy
{
    /**
     * A customer may only touch items sitting in their own cart.
     */
    public function update(User $user, CartItem $cartItem): bool
    {
        return $cartItem->cart->customer_id === $user->id;
    }

    public function delete(User $user, CartItem $cartItem): bool
    {
        return $this->update($user, $cartItem);
    }
}
