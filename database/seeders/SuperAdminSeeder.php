<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    /**
     * Creates the first Super Admin from environment variables so no
     * credential is ever hard-coded in source control (module 40).
     * Set ADMIN_EMAIL / ADMIN_PASSWORD in .env before seeding in production.
     */
    public function run(): void
    {
        $email = env('ADMIN_EMAIL', 'admin@example.com');
        $password = env('ADMIN_PASSWORD', 'ChangeMe123!');

        $admin = User::updateOrCreate(
            ['email' => $email],
            [
                'name' => 'Super Admin',
                'password' => Hash::make($password),
                'status' => User::STATUS_ACTIVE,
                'email_verified_at' => now(),
            ]
        );

        $role = Role::where('name', Role::SUPER_ADMIN)->first();

        if ($role) {
            $admin->roles()->syncWithoutDetaching([$role->id]);
        }

        if (env('ADMIN_PASSWORD') === null) {
            $this->command?->warn(
                'ADMIN_PASSWORD is not set in .env — a default password was used. Change it immediately.'
            );
        }
    }
}
