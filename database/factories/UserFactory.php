<?php

namespace Database\Factories;

use App\Models\User;
use App\Support\AdminPermissionCatalog;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    public function configure(): static
    {
        return $this->afterCreating(function (User $user): void {
            if ($user->roles()->doesntExist()) {
                $user->assignRole(Role::findOrCreate('doctor', 'web'));
            }
        });
    }

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
            'status' => 'active',
        ];
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

    public function admin(): static
    {
        return $this->afterCreating(function (User $user): void {
            $user->syncRoles([Role::findOrCreate('admin', 'web')]);
            $permissions = collect(array_keys(AdminPermissionCatalog::all()))
                ->map(fn (string $name) => Permission::findOrCreate($name, 'web'));
            $user->syncPermissions($permissions);
        });
    }

    public function restrictedAdmin(): static
    {
        return $this->afterCreating(function (User $user): void {
            foreach (array_keys(AdminPermissionCatalog::all()) as $permissionName) {
                Permission::findOrCreate($permissionName, 'web');
            }

            $user->syncRoles([Role::findOrCreate('admin', 'web')]);
            $user->syncPermissions([]);
        });
    }

    public function doctor(): static
    {
        return $this->afterCreating(fn (User $user) => $user->syncRoles([Role::findOrCreate('doctor', 'web')]));
    }

    public function superAdmin(): static
    {
        return $this->afterCreating(fn (User $user) => $user->syncRoles([Role::findOrCreate('super-admin', 'web')]));
    }
}
