<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@gmail.com'], // Identifier
            [
                'name' => 'Super Admin',
                'phone' => '+6283125014403',
                'role' => 'admin',
                'password' => Hash::make('password'),
            ]
        );
    }
}
