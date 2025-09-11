<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::create([
            'name' => config('custom.frontend_user'),
            'email' => config('custom.frontend_email'),
            'email_verified_at' => now(),
            'password' => Hash::make(config('custom.frontend_password')),
        ]);

        UserProfile::create([
            'user_id' => $user->id,
            'surname' => config('custom.frontend_user'),
            'name' => config('custom.frontend_user'),
        ]);
    }
}
