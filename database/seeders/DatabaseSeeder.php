<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::create([
            'id' => 'AD0001',
            'name' => 'Admin',
            'email' => 'admin@allimni.com',
            'password' => \Illuminate\Support\Facades\Hash::make('password'),
            'type' => 'admin',
            'subscription_tier' => null,
        ]);
    }
}
