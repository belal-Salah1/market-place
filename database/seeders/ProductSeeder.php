<?php

namespace Database\Seeders;

use App\Enums\RoleStatus;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $vendors = User::whereHas('role', fn ($q) => $q->where('name', RoleStatus::VENDOR->value))
            ->where('is_approved', true)
            ->pluck('id');

        Product::factory(15)
            ->sequence(fn ($sequence) => $vendors->isEmpty() ? [] : ['vendor_id' => $vendors->random()])
            ->create();
    }
}
