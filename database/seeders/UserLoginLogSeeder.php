<?php

namespace Database\Seeders;

use App\Models\UserLoginLog;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserLoginLogSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();

        $browsers = [
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:121.0) Gecko/20100101 Firefox/121.0',
            'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1',
            'Mozilla/5.0 (iPad; CPU OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1',
            'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Safari/605.1.15',
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Edge/120.0.0.0 Safari/537.36'
        ];

        $ipAddresses = [
            '197.210.85.', // Nigeria
            '105.112.34.',  // Nigeria
            '41.58.234.',   // Nigeria
            '102.89.23.',   // Nigeria
            '192.168.1.',   // Local network
            '10.0.0.',      // Local network
        ];

        foreach ($users as $user) {
            // Create successful login logs (5-15 per user)
            $loginCount = fake()->numberBetween(5, 15);
            
            for ($i = 0; $i < $loginCount; $i++) {
                $daysAgo = fake()->numberBetween(0, 30);
                $hoursAgo = fake()->numberBetween(0, 23);
                $minutesAgo = fake()->numberBetween(0, 59);
                
                UserLoginLog::create([
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'ip_address' => fake()->randomElement($ipAddresses) . fake()->numberBetween(1, 254),
                    'user_agent' => fake()->randomElement($browsers),
                    'success' => true,
                    'logged_in_at' => now()->subDays($daysAgo)->subHours($hoursAgo)->subMinutes($minutesAgo),
                    'created_at' => now()->subDays($daysAgo)->subHours($hoursAgo)->subMinutes($minutesAgo),
                    'updated_at' => now()->subDays($daysAgo)->subHours($hoursAgo)->subMinutes($minutesAgo)
                ]);
            }
        }

        // Create failed login attempts for some users
        $customersWithFailedAttempts = User::where('role', 'customer')->limit(3)->get();

        foreach ($customersWithFailedAttempts as $user) {
            // Create 2-5 failed attempts
            $failedCount = fake()->numberBetween(2, 5);
            
            for ($i = 0; $i < $failedCount; $i++) {
                $daysAgo = fake()->numberBetween(1, 15);
                
                UserLoginLog::create([
                    'user_id' => null, // Failed login doesn't link to user
                    'email' => $user->email,
                    'ip_address' => fake()->randomElement($ipAddresses) . fake()->numberBetween(1, 254),
                    'user_agent' => fake()->randomElement($browsers),
                    'success' => false,
                    'logged_in_at' => now()->subDays($daysAgo)->subMinutes(fake()->numberBetween(1, 120)),
                    'created_at' => now()->subDays($daysAgo)->subMinutes(fake()->numberBetween(1, 120))
                ]);
            }
        }

        // Create failed attempts with non-existent emails
        $fakeEmails = [
            'hacker@example.com',
            'test@test.com',
            'admin@admin.com',
            'user@user.com',
            'wrong@email.com'
        ];

        foreach ($fakeEmails as $fakeEmail) {
            $attemptCount = fake()->numberBetween(1, 3);
            
            for ($i = 0; $i < $attemptCount; $i++) {
                UserLoginLog::create([
                    'user_id' => null,
                    'email' => $fakeEmail,
                    'ip_address' => fake()->randomElement($ipAddresses) . fake()->numberBetween(1, 254),
                    'user_agent' => fake()->randomElement($browsers),
                    'success' => false,
                    'logged_in_at' => now()->subDays(fake()->numberBetween(1, 10))
                ]);
            }
        }

        // Create suspicious activity (multiple failed attempts from same IP)
        $suspiciousIp = '197.210.85.123';
        
        for ($i = 0; $i < 5; $i++) {
            UserLoginLog::create([
                'user_id' => null,
                'email' => fake()->randomElement(['admin@sucheus.com', 'test@test.com', 'wrong@email.com']),
                'ip_address' => $suspiciousIp,
                'user_agent' => $browsers[0],
                'success' => false,
                'logged_in_at' => now()->subMinutes(60 - ($i * 10))
            ]);
        }

        // Create recent successful logins (today)
        $recentUsers = User::limit(5)->get();
        
        foreach ($recentUsers as $user) {
            UserLoginLog::create([
                'user_id' => $user->id,
                'email' => $user->email,
                'ip_address' => fake()->randomElement($ipAddresses) . fake()->numberBetween(1, 254),
                'user_agent' => fake()->randomElement($browsers),
                'success' => true,
                'logged_in_at' => now()->subHours(fake()->numberBetween(1, 8))
            ]);
        }
    }
}