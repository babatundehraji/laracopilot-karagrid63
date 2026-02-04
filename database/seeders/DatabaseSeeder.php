<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            CountrySeeder::class,
            StateSeeder::class,
            CitySeeder::class,
            UserSeeder::class,
            CategorySeeder::class,
            SubcategorySeeder::class,
            ServiceSeeder::class,
            ServiceAvailabilitySeeder::class,
            OrderSeeder::class,
            OrderEditSeeder::class,
            PaymentTransactionSeeder::class,
            TransactionSeeder::class,
            DisputeSeeder::class,
            ConversationSeeder::class,
            MessageSeeder::class,
            BannerSeeder::class,
            ServicePromotionSeeder::class,
            NotificationSeeder::class,
            UserLoginLogSeeder::class,
            ActivityLogSeeder::class,
            CartSeeder::class,
            VerificationCodeSeeder::class,
        ]);
    }
}