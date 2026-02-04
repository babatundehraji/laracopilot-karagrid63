<?php

namespace Database\Seeders;

use App\Models\ServicePromotion;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Seeder;

class ServicePromotionSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('role', 'admin')->first();
        $services = Service::where('status', 'approved')->get();

        if ($services->isEmpty()) {
            return;
        }

        // Create featured promotions (4 services)
        for ($i = 0; $i < 4; $i++) {
            $service = $services->random();
            
            ServicePromotion::create([
                'service_id' => $service->id,
                'type' => 'featured',
                'label' => 'Featured Service',
                'original_price' => null,
                'promo_price' => null,
                'starts_at' => now()->subDays(rand(1, 5)),
                'ends_at' => now()->addDays(rand(20, 40)),
                'priority' => rand(5, 10),
                'is_active' => true,
                'created_by' => $admin?->id
            ]);
        }

        // Create deal promotions with price discounts (6 services)
        for ($i = 0; $i < 6; $i++) {
            $service = $services->random();
            $originalPrice = $service->price;
            $discountPercent = fake()->randomElement([10, 15, 20, 25, 30]);
            $promoPrice = $originalPrice - ($originalPrice * $discountPercent / 100);
            
            $labels = [
                '🔥 Hot Deal',
                '⚡ Flash Sale',
                '💰 Special Offer',
                '🎉 Limited Time',
                '⭐ Best Value',
                '🎁 Exclusive Deal'
            ];

            ServicePromotion::create([
                'service_id' => $service->id,
                'type' => 'deal',
                'label' => fake()->randomElement($labels),
                'original_price' => $originalPrice,
                'promo_price' => round($promoPrice, 2),
                'starts_at' => now()->subDays(rand(1, 3)),
                'ends_at' => now()->addDays(rand(7, 14)),
                'priority' => rand(8, 15),
                'is_active' => true,
                'created_by' => $admin?->id
            ]);
        }

        // Create sponsored promotions (5 services)
        for ($i = 0; $i < 5; $i++) {
            $service = $services->random();
            
            ServicePromotion::create([
                'service_id' => $service->id,
                'type' => 'sponsored',
                'label' => 'Sponsored',
                'original_price' => null,
                'promo_price' => null,
                'starts_at' => now()->subDays(rand(1, 7)),
                'ends_at' => now()->addDays(rand(15, 30)),
                'priority' => rand(1, 5),
                'is_active' => true,
                'created_by' => $admin?->id
            ]);
        }

        // Create ending soon deal (high urgency)
        $service = $services->random();
        $originalPrice = $service->price;
        $promoPrice = $originalPrice - ($originalPrice * 0.35);

        ServicePromotion::create([
            'service_id' => $service->id,
            'type' => 'deal',
            'label' => '⏰ Ending Soon - 35% OFF',
            'original_price' => $originalPrice,
            'promo_price' => round($promoPrice, 2),
            'starts_at' => now()->subDays(6),
            'ends_at' => now()->addHours(18),
            'priority' => 20,
            'is_active' => true,
            'created_by' => $admin?->id
        ]);

        // Create scheduled promotion (not yet active)
        $service = $services->random();
        $originalPrice = $service->price;
        $promoPrice = $originalPrice - ($originalPrice * 0.25);

        ServicePromotion::create([
            'service_id' => $service->id,
            'type' => 'deal',
            'label' => '🎊 Upcoming Deal - 25% OFF',
            'original_price' => $originalPrice,
            'promo_price' => round($promoPrice, 2),
            'starts_at' => now()->addDays(5),
            'ends_at' => now()->addDays(19),
            'priority' => 12,
            'is_active' => true,
            'created_by' => $admin?->id
        ]);

        // Create expired promotion
        $service = $services->random();
        $originalPrice = $service->price;
        $promoPrice = $originalPrice - ($originalPrice * 0.20);

        ServicePromotion::create([
            'service_id' => $service->id,
            'type' => 'deal',
            'label' => 'Weekend Special',
            'original_price' => $originalPrice,
            'promo_price' => round($promoPrice, 2),
            'starts_at' => now()->subDays(10),
            'ends_at' => now()->subDays(3),
            'priority' => 10,
            'is_active' => true,
            'created_by' => $admin?->id
        ]);

        // Create inactive promotion
        $service = $services->random();

        ServicePromotion::create([
            'service_id' => $service->id,
            'type' => 'featured',
            'label' => 'Featured Service',
            'original_price' => null,
            'promo_price' => null,
            'starts_at' => now(),
            'ends_at' => now()->addMonth(),
            'priority' => 5,
            'is_active' => false,
            'created_by' => $admin?->id
        ]);
    }
}