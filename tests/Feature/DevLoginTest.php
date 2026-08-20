<?php

use App\Models\User;
use Database\Seeders\RoleSeeder;
use Database\Seeders\UserSeeder;

it('signs in the seeded customer and redirects to the customer dashboard', function () {
    $this->seed(RoleSeeder::class);
    $this->seed(UserSeeder::class);

    $this->get(route('dev.login-as-customer'))
        ->assertRedirect(route('customer.dashboard'));

    $this->assertAuthenticatedAs(User::where('email', 'customer@example.com')->first());
});

it('falls back to any customer when the seeded account is absent', function () {
    $customer = User::factory()->customer()->create();

    $this->get(route('dev.login-as-customer'))->assertRedirect(route('customer.dashboard'));

    $this->assertAuthenticatedAs($customer);
});

it('lands on the customer dashboard without hitting the role gate', function () {
    $this->seed(RoleSeeder::class);
    $this->seed(UserSeeder::class);

    $this->withoutVite()
        ->followingRedirects()
        ->get(route('dev.login-as-customer'))
        ->assertOk();
});

it('404s when no customer exists to sign in as', function () {
    User::factory()->admin()->create();

    $this->get(route('dev.login-as-customer'))->assertNotFound();

    $this->assertGuest();
});
