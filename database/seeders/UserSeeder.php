<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'admin@simasi.test'],
            [
                'name' => 'Admin SIMASI',
                'password' => 'password',
                'role' => 'admin',
                'is_active' => true,
            ],
        );

        User::query()->updateOrCreate(
            ['email' => 'petugas@simasi.test'],
            [
                'name' => 'Petugas SIMASI',
                'password' => 'password',
                'role' => 'petugas',
                'is_active' => true,
            ],
        );
    }
}
