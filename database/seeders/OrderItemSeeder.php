<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Database\Seeder;

class OrderItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $orders = Order::pluck('id');
        $products = Product::pluck('id');

        OrderItem::factory(15)
            ->sequence(fn ($sequence) => array_filter([
                'order_id' => $orders->isEmpty() ? null : $orders->random(),
                'product_id' => $products->isEmpty() ? null : $products->random(),
            ]))
            ->create();
    }
}
