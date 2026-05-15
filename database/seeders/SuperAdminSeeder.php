<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'superadmin@example.com'],
            [
                'name'      => 'Super Admin',
                'password'  => Hash::make('password'),   // ⚠ Change in production!
                'is_active' => true,
            ]
        );

        $user->assignRole('superadmin');

        $this->command->info('✅  Superadmin user created: superadmin@example.com / password');
        $this->command->warn('    ⚠  Remember to change the default password before going live!');
    }
}