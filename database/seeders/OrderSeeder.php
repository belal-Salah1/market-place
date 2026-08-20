<?php

namespace Database\Seeders;

use App\Enums\RoleStatus;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customers = User::whereHas('role', fn ($q) => $q->where('name', RoleStatus::CUSTOMER->value))->pluck('id');

        Order::factory(15)
            ->sequence(fn ($sequence) => $customers->isEmpty() ? [] : ['customer_id' => $customers->random()])
            ->create();
    }
}
