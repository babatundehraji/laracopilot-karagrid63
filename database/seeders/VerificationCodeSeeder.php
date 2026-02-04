<?php

namespace Database\Seeders;

use App\Models\VerificationCode;
use App\Models\User;
use Illuminate\Database\Seeder;

class VerificationCodeSeeder extends Seeder
{
    public function run(): void
    {
        // Get unverified users
        $unverifiedUsers = User::whereNull('email_verified_at')->get();

        foreach ($unverifiedUsers as $user) {
            // Create active verification code
            VerificationCode::create([
                'email' => $user->email,
                'user_id' => $user->id,
                'type' => 'email_verification',
                'code' => str_pad(random_int(100000, 999999), 6, '0', STR_PAD_LEFT),
                'expires_at' => now()->addMinutes(15),
                'used_at' => null,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        // Create some expired codes (for testing cleanup)
        $expiredUsers = User::inRandomOrder()->limit(3)->get();

        foreach ($expiredUsers as $user) {
            VerificationCode::create([
                'email' => $user->email,
                'user_id' => $user->id,
                'type' => 'email_verification',
                'code' => str_pad(random_int(100000, 999999), 6, '0', STR_PAD_LEFT),
                'expires_at' => now()->subHours(2),
                'used_at' => null,
                'created_at' => now()->subHours(3),
                'updated_at' => now()->subHours(3)
            ]);
        }

        // Create some used codes (historical data)
        $verifiedUsers = User::whereNotNull('email_verified_at')->inRandomOrder()->limit(5)->get();

        foreach ($verifiedUsers as $user) {
            VerificationCode::create([
                'email' => $user->email,
                'user_id' => $user->id,
                'type' => 'email_verification',
                'code' => str_pad(random_int(100000, 999999), 6, '0', STR_PAD_LEFT),
                'expires_at' => $user->email_verified_at->addMinutes(15),
                'used_at' => $user->email_verified_at,
                'created_at' => $user->email_verified_at->subMinutes(5),
                'updated_at' => $user->email_verified_at
            ]);
        }

        // Create some password reset codes
        $usersForReset = User::inRandomOrder()->limit(2)->get();

        foreach ($usersForReset as $user) {
            // Active reset code
            VerificationCode::create([
                'email' => $user->email,
                'user_id' => $user->id,
                'type' => 'password_reset',
                'code' => str_pad(random_int(100000, 999999), 6, '0', STR_PAD_LEFT),
                'expires_at' => now()->addMinutes(15),
                'used_at' => null,
                'created_at' => now()->subMinutes(5),
                'updated_at' => now()->subMinutes(5)
            ]);
        }

        // Create some old used codes for cleanup testing
        $oldUsers = User::inRandomOrder()->limit(4)->get();

        foreach ($oldUsers as $user) {
            VerificationCode::create([
                'email' => $user->email,
                'user_id' => $user->id,
                'type' => fake()->randomElement(['email_verification', 'password_reset']),
                'code' => str_pad(random_int(100000, 999999), 6, '0', STR_PAD_LEFT),
                'expires_at' => now()->subDays(10),
                'used_at' => now()->subDays(10),
                'created_at' => now()->subDays(11),
                'updated_at' => now()->subDays(10)
            ]);
        }
    }
}