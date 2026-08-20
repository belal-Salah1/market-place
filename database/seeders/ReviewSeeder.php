<?php

namespace Database\Seeders;

use App\Enums\RoleStatus;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customers = User::whereHas('role', fn ($q) => $q->where('name', RoleStatus::CUSTOMER->value))->pluck('id');
        $products = Product::pluck('id');

        Review::factory(15)
            ->sequence(fn ($sequence) => array_filter([
                'customer_id' => $customers->isEmpty() ? null : $customers->random(),
                'product_id' => $products->isEmpty() ? null : $products->random(),
            ]))
            ->create();
    }
}
