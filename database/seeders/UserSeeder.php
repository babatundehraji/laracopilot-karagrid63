<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Vendor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Admin user
        User::create([
            'first_name' => 'Admin',
            'last_name' => 'User',
            'email' => 'admin@sucheus.com',
            'phone_code' => '+234',
            'phone' => '8012345678',
            'password' => Hash::make('password'),
            'avatar_url' => 'https://ui-avatars.com/api/?name=Admin+User&size=200&background=6366f1&color=fff',
            'bio' => 'System administrator with full access to platform management.',
            'role' => 'admin',
            'status' => 'active',
            'country' => 'Nigeria',
            'state' => 'Lagos',
            'city' => 'Ikeja',
            'timezone' => 'Africa/Lagos',
            'email_verified_at' => now()->subMonths(6),
            'phone_verified_at' => now()->subMonths(6),
            'last_login_at' => now()->subHours(2),
            'notification_prefs' => [
                'email_orders' => true,
                'email_disputes' => true,
                'email_system' => true,
                'sms_orders' => false,
                'push_orders' => true
            ],
            'meta' => [
                'onboarding_completed' => true,
                'tour_completed' => true
            ],
            'created_at' => now()->subMonths(6)
        ]);

        // Customer users (10 customers)
        $customerNames = [
            ['John', 'Doe'],
            ['Jane', 'Smith'],
            ['Michael', 'Johnson'],
            ['Sarah', 'Williams'],
            ['David', 'Brown'],
            ['Emily', 'Jones'],
            ['Chris', 'Miller'],
            ['Lisa', 'Davis'],
            ['Robert', 'Wilson'],
            ['Jennifer', 'Taylor']
        ];

        $nigeriaCities = [
            ['Lagos', 'Lagos'],
            ['Lagos', 'Lekki'],
            ['Lagos', 'Victoria Island'],
            ['Abuja', 'Abuja'],
            ['Rivers', 'Port Harcourt'],
            ['Oyo', 'Ibadan'],
            ['Kano', 'Kano'],
            ['Kaduna', 'Kaduna'],
            ['Enugu', 'Enugu'],
            ['Edo', 'Benin City']
        ];

        foreach ($customerNames as $index => $name) {
            $firstName = $name[0];
            $lastName = $name[1];
            $email = strtolower($firstName) . '.' . strtolower($lastName) . '@example.com';
            $location = $nigeriaCities[$index];

            $daysAgo = fake()->numberBetween(1, 180);
            $isVerified = fake()->boolean(80); // 80% verified
            $hasPhone = fake()->boolean(90); // 90% have phone
            $hasAvatar = fake()->boolean(60); // 60% have avatar

            User::create([
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $email,
                'phone_code' => $hasPhone ? '+234' : null,
                'phone' => $hasPhone ? '80' . fake()->numberBetween(10000000, 99999999) : null,
                'password' => Hash::make('password'),
                'avatar_url' => $hasAvatar ? "https://i.pravatar.cc/200?u={$email}" : null,
                'bio' => fake()->boolean(40) ? fake()->sentence(12) : null,
                'role' => 'customer',
                'status' => 'active',
                'country' => 'Nigeria',
                'state' => $location[0],
                'city' => $location[1],
                'timezone' => 'Africa/Lagos',
                'email_verified_at' => $isVerified ? now()->subDays($daysAgo) : null,
                'phone_verified_at' => $isVerified && $hasPhone ? now()->subDays($daysAgo) : null,
                'last_login_at' => fake()->boolean(70) ? now()->subDays(fake()->numberBetween(0, 30)) : null,
                'notification_prefs' => [
                    'email_orders' => fake()->boolean(90),
                    'email_promotions' => fake()->boolean(60),
                    'sms_orders' => fake()->boolean(40),
                    'push_orders' => fake()->boolean(80)
                ],
                'meta' => [
                    'onboarding_completed' => fake()->boolean(85),
                    'preferred_payment' => fake()->randomElement(['card', 'bank_transfer', 'wallet'])
                ],
                'created_at' => now()->subDays($daysAgo),
                'updated_at' => now()->subDays(fake()->numberBetween(0, $daysAgo))
            ]);
        }

        // Vendor users (5 vendors with vendor profiles)
        $vendorData = [
            [
                'first_name' => 'Samuel',
                'last_name' => 'Okafor',
                'email' => 'samuel.vendor@sucheus.com',
                'business_name' => 'ProFix Services',
                'business_type' => 'individual',
                'location' => ['Lagos', 'Ikeja']
            ],
            [
                'first_name' => 'Ngozi',
                'last_name' => 'Adeyemi',
                'email' => 'ngozi.vendor@sucheus.com',
                'business_name' => 'Elite Home Services',
                'business_type' => 'company',
                'location' => ['Lagos', 'Victoria Island']
            ],
            [
                'first_name' => 'Chinedu',
                'last_name' => 'Eze',
                'email' => 'chinedu.vendor@sucheus.com',
                'business_name' => 'QuickFix Nigeria',
                'business_type' => 'company',
                'location' => ['Abuja', 'Abuja']
            ],
            [
                'first_name' => 'Aisha',
                'last_name' => 'Mohammed',
                'email' => 'aisha.vendor@sucheus.com',
                'business_name' => 'Northern Tech Solutions',
                'business_type' => 'company',
                'location' => ['Kano', 'Kano']
            ],
            [
                'first_name' => 'Tunde',
                'last_name' => 'Bakare',
                'email' => 'tunde.vendor@sucheus.com',
                'business_name' => 'TBakare Services',
                'business_type' => 'individual',
                'location' => ['Oyo', 'Ibadan']
            ]
        ];

        foreach ($vendorData as $vendor) {
            $daysAgo = fake()->numberBetween(30, 365);

            $user = User::create([
                'first_name' => $vendor['first_name'],
                'last_name' => $vendor['last_name'],
                'email' => $vendor['email'],
                'phone_code' => '+234',
                'phone' => '80' . fake()->numberBetween(10000000, 99999999),
                'password' => Hash::make('password'),
                'avatar_url' => "https://i.pravatar.cc/200?u={$vendor['email']}",
                'bio' => 'Professional service provider with years of experience in the industry.',
                'role' => 'vendor',
                'status' => 'active',
                'country' => 'Nigeria',
                'state' => $vendor['location'][0],
                'city' => $vendor['location'][1],
                'timezone' => 'Africa/Lagos',
                'email_verified_at' => now()->subDays($daysAgo),
                'phone_verified_at' => now()->subDays($daysAgo),
                'last_login_at' => now()->subDays(fake()->numberBetween(0, 7)),
                'notification_prefs' => [
                    'email_orders' => true,
                    'email_messages' => true,
                    'sms_orders' => true,
                    'push_orders' => true
                ],
                'meta' => [
                    'onboarding_completed' => true,
                    'business_verified' => true,
                    'stripe_connected' => fake()->boolean(60)
                ],
                'created_at' => now()->subDays($daysAgo),
                'updated_at' => now()->subDays(fake()->numberBetween(0, 7))
            ]);

            // Create vendor profile
            Vendor::create([
                'user_id' => $user->id,
                'business_name' => $vendor['business_name'],
                'business_type' => $vendor['business_type'],
                'business_address' => fake()->streetAddress(),
                'business_phone' => $user->full_phone,
                'business_email' => $user->email,
                'tax_id' => 'TIN' . fake()->numberBetween(10000000, 99999999),
                'registration_number' => $vendor['business_type'] === 'company' ? 'RC' . fake()->numberBetween(100000, 999999) : null,
                'years_in_business' => fake()->numberBetween(1, 15),
                'service_radius' => fake()->numberBetween(10, 50),
                'is_verified' => true,
                'verified_at' => now()->subDays($daysAgo - 1),
                'rating' => fake()->randomFloat(1, 4.0, 5.0),
                'total_reviews' => fake()->numberBetween(10, 150),
                'bank_name' => fake()->randomElement(['GTBank', 'Access Bank', 'Zenith Bank', 'First Bank', 'UBA']),
                'account_number' => fake()->numerify('##########'),
                'account_name' => $vendor['business_name'],
                'payout_schedule' => 'weekly',
                'commission_rate' => 10.00,
                'preferences' => [
                    'auto_accept_orders' => fake()->boolean(30),
                    'instant_booking' => fake()->boolean(70),
                    'max_concurrent_orders' => fake()->numberBetween(5, 20)
                ],
                'documents' => [
                    'id_card' => 'documents/vendors/id_card_' . $user->id . '.pdf',
                    'business_license' => 'documents/vendors/license_' . $user->id . '.pdf'
                ],
                'created_at' => now()->subDays($daysAgo),
                'updated_at' => now()->subDays(fake()->numberBetween(0, 7))
            ]);
        }

        // Add 1 suspended customer
        User::create([
            'first_name' => 'Suspended',
            'last_name' => 'User',
            'email' => 'suspended@example.com',
            'phone_code' => '+234',
            'phone' => '8099999999',
            'password' => Hash::make('password'),
            'role' => 'customer',
            'status' => 'suspended',
            'country' => 'Nigeria',
            'state' => 'Lagos',
            'city' => 'Lagos',
            'timezone' => 'Africa/Lagos',
            'email_verified_at' => now()->subMonths(3),
            'phone_verified_at' => now()->subMonths(3),
            'last_login_at' => now()->subMonths(1),
            'notification_prefs' => [
                'email_orders' => false,
                'email_promotions' => false
            ],
            'meta' => [
                'suspension_reason' => 'Multiple policy violations',
                'suspended_at' => now()->subWeek()->toDateTimeString()
            ],
            'created_at' => now()->subMonths(6)
        ]);

        // Add 1 unverified customer
        User::create([
            'first_name' => 'Unverified',
            'last_name' => 'Customer',
            'email' => 'unverified@example.com',
            'phone_code' => '+234',
            'phone' => '8088888888',
            'password' => Hash::make('password'),
            'role' => 'customer',
            'status' => 'active',
            'country' => 'Nigeria',
            'state' => 'Lagos',
            'city' => 'Ikeja',
            'timezone' => 'Africa/Lagos',
            'email_verified_at' => null,
            'phone_verified_at' => null,
            'last_login_at' => now()->subDays(2),
            'notification_prefs' => [
                'email_orders' => true
            ],
            'meta' => [
                'onboarding_completed' => false
            ],
            'created_at' => now()->subDays(5)
        ]);
    }
}