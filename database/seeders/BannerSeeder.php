<?php

namespace Database\Seeders;

use App\Models\Banner;
use App\Models\User;
use Illuminate\Database\Seeder;

class BannerSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('role', 'admin')->first();

        // Active home banner - Welcome/Hero
        Banner::create([
            'title' => 'Find Professional Services Near You',
            'subtitle' => 'Connect with verified service providers for all your needs. Book instantly, pay securely.',
            'image_url' => 'https://images.unsplash.com/photo-1581578731548-c64695cc6952?w=1200',
            'cta_label' => 'Browse Services',
            'cta_url' => '/services',
            'placement' => 'home',
            'is_active' => true,
            'starts_at' => now()->subDays(5),
            'ends_at' => now()->addMonths(3),
            'created_by' => $admin?->id
        ]);

        // Active home banner - Seasonal promotion
        Banner::create([
            'title' => 'New Year Special Offers',
            'subtitle' => 'Save up to 30% on featured services. Limited time only!',
            'image_url' => 'https://images.unsplash.com/photo-1467043153537-a4fba2cd39ef?w=1200',
            'cta_label' => 'View Deals',
            'cta_url' => '/promotions',
            'placement' => 'home',
            'is_active' => true,
            'starts_at' => now()->subDays(3),
            'ends_at' => now()->addDays(27),
            'created_by' => $admin?->id
        ]);

        // Active home banner - Category focus
        Banner::create([
            'title' => 'Home Improvement Experts',
            'subtitle' => 'Transform your space with professional plumbing, electrical, and renovation services.',
            'image_url' => 'https://images.unsplash.com/photo-1504917595217-d4dc5ebe6122?w=1200',
            'cta_label' => 'Explore Services',
            'cta_url' => '/categories/home-services',
            'placement' => 'home',
            'is_active' => true,
            'starts_at' => now()->subWeek(),
            'ends_at' => now()->addMonth(),
            'created_by' => $admin?->id
        ]);

        // Scheduled banner (not yet active)
        Banner::create([
            'title' => 'Valentine\'s Day Services',
            'subtitle' => 'Special packages for event planning, catering, and photography.',
            'image_url' => 'https://images.unsplash.com/photo-1518199266791-5375a83190b7?w=1200',
            'cta_label' => 'Book Now',
            'cta_url' => '/services/events',
            'placement' => 'home',
            'is_active' => true,
            'starts_at' => now()->addDays(15),
            'ends_at' => now()->addDays(45),
            'created_by' => $admin?->id
        ]);

        // Expired banner
        Banner::create([
            'title' => 'Black Friday Deals',
            'subtitle' => 'Massive discounts on all services - Don\'t miss out!',
            'image_url' => 'https://images.unsplash.com/photo-1607083206968-13611e3d76db?w=1200',
            'cta_label' => 'Shop Now',
            'cta_url' => '/black-friday',
            'placement' => 'home',
            'is_active' => true,
            'starts_at' => now()->subDays(60),
            'ends_at' => now()->subDays(30),
            'created_by' => $admin?->id
        ]);

        // Inactive banner
        Banner::create([
            'title' => 'Become a Service Provider',
            'subtitle' => 'Join our platform and grow your business. Sign up today!',
            'image_url' => 'https://images.unsplash.com/photo-1556761175-b413da4baf72?w=1200',
            'cta_label' => 'Register Now',
            'cta_url' => '/vendor/register',
            'placement' => 'home',
            'is_active' => false,
            'starts_at' => null,
            'ends_at' => null,
            'created_by' => $admin?->id
        ]);

        // Discover page banner
        Banner::create([
            'title' => 'Discover Top-Rated Services',
            'subtitle' => 'Browse services by category, location, and ratings.',
            'image_url' => 'https://images.unsplash.com/photo-1521737711867-e3b97375f902?w=1200',
            'cta_label' => 'Start Exploring',
            'cta_url' => '/categories',
            'placement' => 'discover',
            'is_active' => true,
            'starts_at' => now()->subDays(10),
            'ends_at' => now()->addMonths(2),
            'created_by' => $admin?->id
        ]);
    }
}