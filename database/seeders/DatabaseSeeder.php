<?php

namespace Database\Seeders;

use App\Models\Enums\UserStatus;
use App\Models\User;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Bastian Schur',
            'email' => 'b.schur@nodus-it.de',
            'status' => UserStatus::ACTIVE,
            'password' => Hash::make(12345),
        ]);
    }
}
