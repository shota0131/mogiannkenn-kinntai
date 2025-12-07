<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run()
    {
        Admin::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => '管理者',
                'password' => Hash::make('password'),
            ]
        );
    }
}
