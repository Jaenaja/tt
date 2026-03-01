<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['username' => 'admin'],
            [
                'id'             => 1,
                'name'           => 'Admin',
                'username'       => 'admin',
                'password'       => '$2y$12$ZGMo81fvhYo1FlbCDVOydeOXWXg6FiMtSy7lekOY96Vbii6/i3lqS',
                'role'           => 'admin',
                'is_active'      => 1,
                'remember_token' => 'xZJLxjC0635MQbhcWfcjKKtWHYud2Fgu99UBi1qz64bYAjo5Jdhmuocpt8n3',
                'created_at'     => '2026-02-09 15:23:56',
                'updated_at'     => '2026-02-09 15:23:56',
            ]
        );
    }
}
