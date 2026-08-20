<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Database\Seeder;

class PaymentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $orders = Order::pluck('id');

        Payment::factory(15)
            ->sequence(fn ($sequence) => $orders->isEmpty() ? [] : ['order_id' => $orders->random()])
            ->create();
    }
}
