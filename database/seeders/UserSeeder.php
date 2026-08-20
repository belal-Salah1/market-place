<?php

namespace Database\Seeders;

use App\Enums\RoleStatus;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Known accounts seeded for local sign-in, all using the password "password".
     *
     * @var array<string, array{name: string, role: RoleStatus, approved: bool}>
     */
    protected array $accounts = [
        'test@example.com' => ['name' => 'Test User', 'role' => RoleStatus::ADMIN, 'approved' => true],
        'admin@example.com' => ['name' => 'Admin User', 'role' => RoleStatus::ADMIN, 'approved' => true],
        'vendor@example.com' => ['name' => 'Vendor User', 'role' => RoleStatus::VENDOR, 'approved' => true],
        'pending@example.com' => ['name' => 'Pending Vendor', 'role' => RoleStatus::VENDOR, 'approved' => false],
        'customer@example.com' => ['name' => 'Customer User', 'role' => RoleStatus::CUSTOMER, 'approved' => false],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach ($this->accounts as $email => $account) {
            if (User::where('email', $email)->exists()) {
                continue;
            }

            User::factory()
                ->role($account['role'])
                ->state(['is_approved' => $account['approved']])
                ->create(['name' => $account['name'], 'email' => $email]);
        }

        // Bulk demo users are only created on a fresh database.
        if (User::count() > count($this->accounts)) {
            return;
        }

        User::factory(5)->vendor()->create();
        User::factory(3)->vendor()->unapproved()->create();
        User::factory(10)->customer()->create();
    }
}
