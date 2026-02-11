<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (\App\Enums\RoleStatus::cases() as $role) {
            Role::firstOrCreate(['name' => $role->value]);
        }
    }
}
