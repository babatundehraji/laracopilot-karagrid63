<?php

namespace Database\Seeders;

use App\Models\Service;
use App\Models\Vendor;
use App\Models\Category;
use App\Models\Subcategory;
use App\Models\Country;
use App\Models\State;
use App\Models\City;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        $vendors = Vendor::where('is_verified', true)->get();
        $nigeria = Country::where('iso2', 'NG')->first();
        $lagos = State::where('code', 'LA')->first();
        $lagosCity = City::where('name', 'Ikeja')->first();
        $fct = State::where('code', 'FC')->first();
        $abujaCity = City::where('name', 'Abuja Municipal')->first();

        if ($vendors->isEmpty()) {
            return;
        }

        $servicesData = [
            // Home Services
            [
                'category' => 'Home Services',
                'subcategory' => 'Plumbing',
                'title' => 'Emergency Plumbing Repair',
                'short_description' => '24/7 emergency plumbing services for residential and commercial properties',
                'description' => 'Professional emergency plumbing services available round the clock. We handle pipe bursts, leaks, blocked drains, and all plumbing emergencies with quick response time and quality workmanship.',
                'pricing_type' => 'hourly',
                'price' => 5000,
                'min_hours' => 2,
                'max_hours' => 8,
                'is_remote' => false,
                'is_onsite' => true,
                'is_featured' => true,
                'status' => 'approved'
            ],
            [
                'category' => 'Home Services',
                'subcategory' => 'Electrical Work',
                'title' => 'Residential Electrical Installation',
                'short_description' => 'Complete electrical wiring and installation for homes',
                'description' => 'Expert electrical installation services including wiring, lighting fixtures, outlets, circuit breakers, and electrical panel upgrades. Licensed and insured electricians.',
                'pricing_type' => 'fixed',
                'price' => 25000,
                'min_hours' => null,
                'max_hours' => null,
                'is_remote' => false,
                'is_onsite' => true,
                'is_featured' => false,
                'status' => 'approved'
            ],
            [
                'category' => 'Home Services',
                'subcategory' => 'Cleaning Services',
                'title' => 'Deep House Cleaning',
                'short_description' => 'Thorough deep cleaning for homes and apartments',
                'description' => 'Comprehensive deep cleaning service covering all rooms, kitchen, bathrooms, windows, and floors. We use eco-friendly products and professional equipment.',
                'pricing_type' => 'fixed',
                'price' => 15000,
                'min_hours' => null,
                'max_hours' => null,
                'is_remote' => false,
                'is_onsite' => true,
                'is_featured' => true,
                'status' => 'approved'
            ],
            [
                'category' => 'Home Services',
                'subcategory' => 'Painting & Decorating',
                'title' => 'Interior Painting Service',
                'short_description' => 'Professional interior painting for homes and offices',
                'description' => 'High-quality interior painting services with premium paints. Includes wall preparation, priming, and two coats of paint. Free color consultation.',
                'pricing_type' => 'fixed',
                'price' => 45000,
                'min_hours' => null,
                'max_hours' => null,
                'is_remote' => false,
                'is_onsite' => true,
                'is_featured' => false,
                'status' => 'approved'
            ],

            // Beauty & Wellness
            [
                'category' => 'Beauty & Wellness',
                'subcategory' => 'Hair Styling',
                'title' => 'Bridal Hair & Makeup Package',
                'short_description' => 'Complete bridal hair styling and makeup service',
                'description' => 'Professional bridal hair and makeup package including trial session, wedding day styling, and touch-up kit. Experienced with all hair types and skin tones.',
                'pricing_type' => 'fixed',
                'price' => 35000,
                'min_hours' => null,
                'max_hours' => null,
                'is_remote' => false,
                'is_onsite' => true,
                'is_featured' => true,
                'status' => 'approved'
            ],
            [
                'category' => 'Beauty & Wellness',
                'subcategory' => 'Massage Therapy',
                'title' => 'Therapeutic Full Body Massage',
                'short_description' => 'Relaxing full body massage therapy session',
                'description' => 'Professional therapeutic massage service for stress relief and muscle relaxation. Available in 60-minute or 90-minute sessions.',
                'pricing_type' => 'hourly',
                'price' => 8000,
                'min_hours' => 1,
                'max_hours' => 2,
                'is_remote' => false,
                'is_onsite' => true,
                'is_featured' => false,
                'status' => 'approved'
            ],

            // Events & Entertainment
            [
                'category' => 'Events & Entertainment',
                'subcategory' => 'Wedding Photography',
                'title' => 'Premium Wedding Photography Package',
                'short_description' => 'Full-day wedding photography with 2 photographers',
                'description' => 'Comprehensive wedding photography package including pre-wedding shoot, full day coverage, 2 professional photographers, 500+ edited photos, online gallery, and premium photo album.',
                'pricing_type' => 'fixed',
                'price' => 150000,
                'min_hours' => null,
                'max_hours' => null,
                'is_remote' => false,
                'is_onsite' => true,
                'is_featured' => true,
                'is_sponsored' => true,
                'status' => 'approved'
            ],
            [
                'category' => 'Events & Entertainment',
                'subcategory' => 'Event Planning',
                'title' => 'Corporate Event Planning',
                'short_description' => 'Full-service corporate event planning and management',
                'description' => 'Professional event planning for corporate events, conferences, product launches, and company celebrations. Includes venue sourcing, vendor management, and on-site coordination.',
                'pricing_type' => 'fixed',
                'price' => 200000,
                'min_hours' => null,
                'max_hours' => null,
                'is_remote' => true,
                'is_onsite' => true,
                'is_featured' => true,
                'status' => 'approved'
            ],

            // Education & Tutoring
            [
                'category' => 'Education & Tutoring',
                'subcategory' => 'Mathematics Tutoring',
                'title' => 'WAEC/JAMB Mathematics Tutoring',
                'short_description' => 'Expert mathematics tutoring for WAEC and JAMB preparation',
                'description' => 'Experienced mathematics tutor specializing in WAEC and JAMB preparation. Personalized lesson plans, practice questions, and exam strategies.',
                'pricing_type' => 'hourly',
                'price' => 3000,
                'min_hours' => 2,
                'max_hours' => 4,
                'is_remote' => true,
                'is_onsite' => true,
                'is_featured' => false,
                'status' => 'approved'
            ],
            [
                'category' => 'Education & Tutoring',
                'subcategory' => 'Coding & Programming',
                'title' => 'Web Development Bootcamp',
                'short_description' => 'Intensive web development training program',
                'description' => 'Comprehensive web development bootcamp covering HTML, CSS, JavaScript, and React. Includes hands-on projects and portfolio building.',
                'pricing_type' => 'fixed',
                'price' => 80000,
                'min_hours' => null,
                'max_hours' => null,
                'is_remote' => true,
                'is_onsite' => false,
                'is_featured' => true,
                'status' => 'approved'
            ],

            // Technology & IT
            [
                'category' => 'Technology & IT',
                'subcategory' => 'Computer Repair',
                'title' => 'Laptop Repair & Upgrade Service',
                'short_description' => 'Professional laptop repair and hardware upgrade',
                'description' => 'Expert laptop repair services including hardware replacement, software troubleshooting, virus removal, and performance upgrades. Same-day service available.',
                'pricing_type' => 'hourly',
                'price' => 4000,
                'min_hours' => 1,
                'max_hours' => 6,
                'is_remote' => false,
                'is_onsite' => true,
                'is_featured' => false,
                'status' => 'approved'
            ],
            [
                'category' => 'Technology & IT',
                'subcategory' => 'Web Development',
                'title' => 'Custom Website Development',
                'short_description' => 'Professional custom website design and development',
                'description' => 'Full-stack web development service for business websites, e-commerce stores, and web applications. Includes responsive design, SEO optimization, and 3 months free maintenance.',
                'pricing_type' => 'fixed',
                'price' => 250000,
                'min_hours' => null,
                'max_hours' => null,
                'is_remote' => true,
                'is_onsite' => false,
                'is_featured' => true,
                'is_sponsored' => true,
                'status' => 'approved'
            ],

            // Health & Fitness
            [
                'category' => 'Health & Fitness',
                'subcategory' => 'Personal Training',
                'title' => 'Personal Fitness Training',
                'short_description' => 'One-on-one personal training sessions',
                'description' => 'Certified personal trainer offering customized workout plans, nutrition guidance, and motivation. Available for home visits or gym sessions.',
                'pricing_type' => 'hourly',
                'price' => 6000,
                'min_hours' => 1,
                'max_hours' => 2,
                'is_remote' => false,
                'is_onsite' => true,
                'is_featured' => false,
                'status' => 'approved'
            ],

            // Automotive
            [
                'category' => 'Automotive',
                'subcategory' => 'Car Detailing',
                'title' => 'Premium Car Detailing Service',
                'short_description' => 'Complete interior and exterior car detailing',
                'description' => 'Professional car detailing including exterior wash, wax, polish, interior deep cleaning, leather conditioning, and engine bay cleaning.',
                'pricing_type' => 'fixed',
                'price' => 20000,
                'min_hours' => null,
                'max_hours' => null,
                'is_remote' => false,
                'is_onsite' => true,
                'is_featured' => false,
                'status' => 'approved'
            ],

            // Pet Services
            [
                'category' => 'Pet Services',
                'subcategory' => 'Pet Grooming',
                'title' => 'Professional Dog Grooming',
                'short_description' => 'Complete grooming service for all dog breeds',
                'description' => 'Professional dog grooming including bath, haircut, nail trimming, ear cleaning, and teeth brushing. Experienced with all breeds.',
                'pricing_type' => 'fixed',
                'price' => 8000,
                'min_hours' => null,
                'max_hours' => null,
                'is_remote' => false,
                'is_onsite' => true,
                'is_featured' => false,
                'status' => 'approved'
            ],
        ];

        foreach ($servicesData as $index => $serviceData) {
            $category = Category::where('name', $serviceData['category'])->first();
            $subcategory = Subcategory::where('name', $serviceData['subcategory'])->first();
            
            if (!$category) continue;

            // Rotate through verified vendors
            $vendor = $vendors[$index % $vendors->count()];

            Service::create([
                'vendor_id' => $vendor->id,
                'category_id' => $category->id,
                'subcategory_id' => $subcategory?->id,
                'title' => $serviceData['title'],
                'short_description' => $serviceData['short_description'],
                'description' => $serviceData['description'],
                'pricing_type' => $serviceData['pricing_type'],
                'price' => $serviceData['price'],
                'min_hours' => $serviceData['min_hours'] ?? null,
                'max_hours' => $serviceData['max_hours'] ?? null,
                'is_remote' => $serviceData['is_remote'],
                'is_onsite' => $serviceData['is_onsite'],
                'service_country_id' => $nigeria?->id,
                'service_state_id' => ($index % 2 === 0) ? $lagos?->id : $fct?->id,
                'service_city_id' => ($index % 2 === 0) ? $lagosCity?->id : $abujaCity?->id,
                'address_line1' => fake()->streetAddress(),
                'postal_code' => fake()->postcode(),
                'latitude' => $index % 2 === 0 ? 6.5244 : 9.0765,
                'longitude' => $index % 2 === 0 ? 3.3792 : 7.3986,
                'main_image_url' => 'https://via.placeholder.com/800x600?text=' . urlencode($serviceData['title']),
                'gallery_images' => [
                    'https://via.placeholder.com/800x600?text=Image1',
                    'https://via.placeholder.com/800x600?text=Image2',
                    'https://via.placeholder.com/800x600?text=Image3'
                ],
                'is_featured' => $serviceData['is_featured'] ?? false,
                'is_sponsored' => $serviceData['is_sponsored'] ?? false,
                'average_rating' => fake()->randomFloat(2, 3.5, 5.0),
                'review_count' => fake()->numberBetween(5, 150),
                'status' => $serviceData['status']
            ]);
        }

        // Create some pending services
        for ($i = 0; $i < 5; $i++) {
            $category = Category::inRandomOrder()->first();
            $subcategory = $category->subcategories()->inRandomOrder()->first();
            $vendor = $vendors->random();

            Service::create([
                'vendor_id' => $vendor->id,
                'category_id' => $category->id,
                'subcategory_id' => $subcategory?->id,
                'title' => fake()->sentence(4),
                'short_description' => fake()->sentence(10),
                'description' => fake()->paragraphs(3, true),
                'pricing_type' => fake()->randomElement(['hourly', 'fixed']),
                'price' => fake()->numberBetween(5000, 100000),
                'min_hours' => fake()->boolean(50) ? fake()->numberBetween(1, 4) : null,
                'max_hours' => fake()->boolean(50) ? fake()->numberBetween(4, 8) : null,
                'is_remote' => fake()->boolean(40),
                'is_onsite' => fake()->boolean(80),
                'service_country_id' => $nigeria?->id,
                'service_state_id' => State::where('country_id', $nigeria?->id)->inRandomOrder()->first()?->id,
                'service_city_id' => $lagosCity?->id,
                'address_line1' => fake()->streetAddress(),
                'postal_code' => fake()->postcode(),
                'average_rating' => null,
                'review_count' => 0,
                'status' => 'pending'
            ]);
        }
    }
}