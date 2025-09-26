<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Isaac Dev',
            'email' => 'isaac@pimono.ai',
            'password' => Hash::make('password'),
            'balance' => 1000.00,
        ]);

        User::create([
            'name' => 'Oluwasegun Dev',
            'email' => 'oluwasegun@pimono.ai',
            'password' => Hash::make('password'),
            'balance' => 500.00,
        ]);

        User::create([
            'name' => 'Charlie Dev',
            'email' => 'charlie@pimono.ai',
            'password' => Hash::make('password'),
            'balance' => '250.00'
        ]);
    }
}
