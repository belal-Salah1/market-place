<?php

namespace Database\Factories;

use App\Enums\RoleStatus;
use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'role_id' => fn () => $this->roleId(RoleStatus::CUSTOMER),
            'is_approved' => false,
        ];
    }

    /**
     * Assign the given role, creating it when the roles table has not been seeded.
     */
    public function role(RoleStatus $role): static
    {
        return $this->state(fn () => ['role_id' => $this->roleId($role)]);
    }

    public function admin(): static
    {
        return $this->role(RoleStatus::ADMIN)->approved();
    }

    /**
     * Vendors are approved by default; chain unapproved() for the pending-approval flow.
     */
    public function vendor(): static
    {
        return $this->role(RoleStatus::VENDOR)->approved();
    }

    public function customer(): static
    {
        return $this->role(RoleStatus::CUSTOMER);
    }

    public function approved(): static
    {
        return $this->state(['is_approved' => true]);
    }

    public function unapproved(): static
    {
        return $this->state(['is_approved' => false]);
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    protected function roleId(RoleStatus $role): int
    {
        return Role::firstOrCreate(['name' => $role->value])->id;
    }
}
