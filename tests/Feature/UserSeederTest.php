<?php

use App\Enums\RoleStatus;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Database\Seeders\UserSeeder;

function usersWithRole(RoleStatus $role): int
{
    return User::whereHas('role', fn ($q) => $q->where('name', $role->value))->count();
}

it('seeds users for every role', function () {
    $this->seed(RoleSeeder::class);
    $this->seed(UserSeeder::class);

    expect(usersWithRole(RoleStatus::ADMIN))->toBeGreaterThan(0)
        ->and(usersWithRole(RoleStatus::VENDOR))->toBeGreaterThan(0)
        ->and(usersWithRole(RoleStatus::CUSTOMER))->toBeGreaterThan(0);
});

it('seeds both approved and pending vendors', function () {
    $this->seed(RoleSeeder::class);
    $this->seed(UserSeeder::class);

    $vendors = User::whereHas('role', fn ($q) => $q->where('name', RoleStatus::VENDOR->value));

    expect((clone $vendors)->where('is_approved', true)->count())->toBeGreaterThan(0)
        ->and((clone $vendors)->where('is_approved', false)->count())->toBeGreaterThan(0);
});

it('seeds a known login account per role', function () {
    $this->seed(RoleSeeder::class);
    $this->seed(UserSeeder::class);

    foreach (['test@example.com', 'admin@example.com', 'vendor@example.com', 'pending@example.com', 'customer@example.com'] as $email) {
        expect(User::where('email', $email)->exists())->toBeTrue();
    }
});

it('can be seeded twice without duplicating accounts', function () {
    $this->seed(RoleSeeder::class);
    $this->seed(UserSeeder::class);
    $after = User::count();

    $this->seed(UserSeeder::class);

    expect(User::count())->toBe($after)
        ->and(User::where('email', 'admin@example.com')->count())->toBe(1);
});

it('assigns the matching role for each factory state', function () {
    expect(User::factory()->admin()->create()->role->name)->toBe(RoleStatus::ADMIN->value)
        ->and(User::factory()->vendor()->create()->role->name)->toBe(RoleStatus::VENDOR->value)
        ->and(User::factory()->customer()->create()->role->name)->toBe(RoleStatus::CUSTOMER->value);
});

it('makes vendors approved by default and unapproved on request', function () {
    expect(User::factory()->vendor()->create()->is_approved)->toBeTrue()
        ->and(User::factory()->vendor()->unapproved()->create()->is_approved)->toBeFalse();
});
