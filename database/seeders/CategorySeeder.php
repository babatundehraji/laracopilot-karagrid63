<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Home Services',
                'slug' => 'home-services',
                'description' => 'Professional home maintenance, repair, and improvement services',
                'icon' => 'home',
                'sort_order' => 1
            ],
            [
                'name' => 'Beauty & Wellness',
                'slug' => 'beauty-wellness',
                'description' => 'Beauty treatments, spa services, and wellness care',
                'icon' => 'spa',
                'sort_order' => 2
            ],
            [
                'name' => 'Events & Entertainment',
                'slug' => 'events-entertainment',
                'description' => 'Event planning, photography, videography, and entertainment services',
                'icon' => 'celebration',
                'sort_order' => 3
            ],
            [
                'name' => 'Education & Tutoring',
                'slug' => 'education-tutoring',
                'description' => 'Private tutoring, language classes, and educational services',
                'icon' => 'school',
                'sort_order' => 4
            ],
            [
                'name' => 'Health & Fitness',
                'slug' => 'health-fitness',
                'description' => 'Personal training, nutrition counseling, and fitness coaching',
                'icon' => 'fitness_center',
                'sort_order' => 5
            ],
            [
                'name' => 'Technology & IT',
                'slug' => 'technology-it',
                'description' => 'IT support, computer repair, software development, and tech consulting',
                'icon' => 'computer',
                'sort_order' => 6
            ],
            [
                'name' => 'Automotive',
                'slug' => 'automotive',
                'description' => 'Car maintenance, repair, detailing, and automotive services',
                'icon' => 'directions_car',
                'sort_order' => 7
            ],
            [
                'name' => 'Professional Services',
                'slug' => 'professional-services',
                'description' => 'Legal, accounting, consulting, and business services',
                'icon' => 'business_center',
                'sort_order' => 8
            ],
            [
                'name' => 'Pet Services',
                'slug' => 'pet-services',
                'description' => 'Pet grooming, training, sitting, and veterinary services',
                'icon' => 'pets',
                'sort_order' => 9
            ],
            [
                'name' => 'Delivery & Logistics',
                'slug' => 'delivery-logistics',
                'description' => 'Package delivery, moving services, and logistics solutions',
                'icon' => 'local_shipping',
                'sort_order' => 10
            ],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}