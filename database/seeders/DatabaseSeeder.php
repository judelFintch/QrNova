<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::updateOrCreate([
            'email' => config('auth.seed_user.email'),
        ], [
            'name' => config('auth.seed_user.name'),
            'password' => Hash::make(config('auth.seed_user.password')),
            'email_verified_at' => now(),
        ]);
    }
}
