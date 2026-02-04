<?php

namespace Database\Seeders;

use App\Models\Subcategory;
use App\Models\Category;
use Illuminate\Database\Seeder;

class SubcategorySeeder extends Seeder
{
    public function run(): void
    {
        $subcategoriesData = [
            'Home Services' => [
                ['name' => 'Plumbing', 'description' => 'Pipe repair, installation, and maintenance'],
                ['name' => 'Electrical Work', 'description' => 'Wiring, installations, and electrical repairs'],
                ['name' => 'Carpentry', 'description' => 'Furniture making, repairs, and custom woodwork'],
                ['name' => 'Painting & Decorating', 'description' => 'Interior and exterior painting services'],
                ['name' => 'Cleaning Services', 'description' => 'Residential and commercial cleaning'],
                ['name' => 'HVAC Services', 'description' => 'Air conditioning and heating services'],
                ['name' => 'Roofing', 'description' => 'Roof repair, installation, and maintenance'],
                ['name' => 'Landscaping', 'description' => 'Garden design, lawn care, and landscaping'],
            ],
            'Beauty & Wellness' => [
                ['name' => 'Hair Styling', 'description' => 'Haircuts, coloring, and styling'],
                ['name' => 'Makeup Services', 'description' => 'Professional makeup application'],
                ['name' => 'Massage Therapy', 'description' => 'Therapeutic and relaxation massage'],
                ['name' => 'Spa Treatments', 'description' => 'Facial treatments and spa services'],
                ['name' => 'Nail Care', 'description' => 'Manicures, pedicures, and nail art'],
                ['name' => 'Barbing', 'description' => 'Professional barbing and grooming'],
            ],
            'Events & Entertainment' => [
                ['name' => 'Wedding Photography', 'description' => 'Professional wedding photography services'],
                ['name' => 'Event Planning', 'description' => 'Complete event planning and coordination'],
                ['name' => 'Videography', 'description' => 'Professional video recording and editing'],
                ['name' => 'DJ Services', 'description' => 'Music entertainment for events'],
                ['name' => 'Catering', 'description' => 'Food and beverage services for events'],
                ['name' => 'MC Services', 'description' => 'Master of ceremonies for events'],
            ],
            'Education & Tutoring' => [
                ['name' => 'Mathematics Tutoring', 'description' => 'Private math lessons and tutoring'],
                ['name' => 'English Tutoring', 'description' => 'English language instruction and tutoring'],
                ['name' => 'Science Tutoring', 'description' => 'Physics, Chemistry, Biology tutoring'],
                ['name' => 'Language Classes', 'description' => 'Foreign language instruction'],
                ['name' => 'Music Lessons', 'description' => 'Instrument and vocal training'],
                ['name' => 'Coding & Programming', 'description' => 'Programming and coding instruction'],
            ],
            'Health & Fitness' => [
                ['name' => 'Personal Training', 'description' => 'One-on-one fitness coaching'],
                ['name' => 'Yoga Instruction', 'description' => 'Yoga classes and private sessions'],
                ['name' => 'Nutrition Counseling', 'description' => 'Dietary guidance and meal planning'],
                ['name' => 'Physiotherapy', 'description' => 'Physical therapy and rehabilitation'],
            ],
            'Technology & IT' => [
                ['name' => 'Computer Repair', 'description' => 'Hardware and software troubleshooting'],
                ['name' => 'Web Development', 'description' => 'Website design and development'],
                ['name' => 'Mobile App Development', 'description' => 'iOS and Android app development'],
                ['name' => 'IT Support', 'description' => 'Technical support and IT consulting'],
                ['name' => 'Network Setup', 'description' => 'Network installation and configuration'],
            ],
            'Automotive' => [
                ['name' => 'Car Repair', 'description' => 'General automotive repair services'],
                ['name' => 'Car Detailing', 'description' => 'Interior and exterior car cleaning'],
                ['name' => 'Oil Change', 'description' => 'Oil and filter replacement services'],
                ['name' => 'Tire Services', 'description' => 'Tire replacement and alignment'],
                ['name' => 'AC Repair', 'description' => 'Automotive air conditioning repair'],
            ],
            'Professional Services' => [
                ['name' => 'Legal Services', 'description' => 'Legal consultation and representation'],
                ['name' => 'Accounting', 'description' => 'Bookkeeping and financial services'],
                ['name' => 'Business Consulting', 'description' => 'Strategy and business advisory'],
                ['name' => 'Tax Preparation', 'description' => 'Tax filing and planning services'],
            ],
            'Pet Services' => [
                ['name' => 'Pet Grooming', 'description' => 'Professional pet grooming services'],
                ['name' => 'Pet Training', 'description' => 'Obedience and behavior training'],
                ['name' => 'Pet Sitting', 'description' => 'Pet care and sitting services'],
                ['name' => 'Veterinary Services', 'description' => 'Medical care for pets'],
            ],
            'Delivery & Logistics' => [
                ['name' => 'Package Delivery', 'description' => 'Local and express delivery services'],
                ['name' => 'Moving Services', 'description' => 'Residential and commercial moving'],
                ['name' => 'Courier Services', 'description' => 'Document and parcel delivery'],
                ['name' => 'Freight Services', 'description' => 'Large item and freight transportation'],
            ],
        ];

        foreach ($subcategoriesData as $categoryName => $subcategories) {
            $category = Category::where('name', $categoryName)->first();
            
            if ($category) {
                foreach ($subcategories as $index => $subcategory) {
                    Subcategory::create([
                        'category_id' => $category->id,
                        'name' => $subcategory['name'],
                        'slug' => \Illuminate\Support\Str::slug($subcategory['name']),
                        'description' => $subcategory['description'],
                        'sort_order' => $index + 1,
                        'is_active' => true
                    ]);
                }
            }
        }
    }
}