<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\Service;
use App\Models\ServiceReview;
use App\Models\User;
use Illuminate\Database\Seeder;

class ServiceReviewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Seeding service reviews...');

        // Get completed orders
        $completedOrders = Order::where('status', 'completed')
            ->with(['service', 'user'])
            ->get();

        if ($completedOrders->isEmpty()) {
            $this->command->warn('No completed orders found. Please run OrderSeeder first.');
            return;
        }

        $reviewComments = [
            5 => [
                'Excellent service! Highly recommended.',
                'Amazing experience from start to finish.',
                'Very professional and exceeded expectations.',
                'Outstanding quality of work. Will definitely hire again.',
                'Perfect! Exactly what I needed.',
                'Exceptional service. Worth every penny.',
                'Best service I\'ve used in years. Truly impressed!',
                'Fantastic work! Completed ahead of schedule.',
            ],
            4 => [
                'Great service, very satisfied overall.',
                'Good work with minor room for improvement.',
                'Professional and reliable. Would recommend.',
                'Very good experience. Would use again.',
                'Quality service at a fair price.',
                'Solid work. Met all my requirements.',
                'Good communication and quality results.',
            ],
            3 => [
                'Service was okay, met basic expectations.',
                'Average experience. Nothing special.',
                'Decent service but could be better.',
                'Fair work. A few issues but resolved.',
                'Acceptable service for the price.',
                'Met expectations but didn\'t exceed them.',
            ],
            2 => [
                'Below expectations. Several issues.',
                'Not great. Had to follow up multiple times.',
                'Service was lacking in several areas.',
                'Disappointed with the quality.',
                'Too many problems. Not satisfied.',
            ],
            1 => [
                'Very poor service. Would not recommend.',
                'Terrible experience. Avoid.',
                'Complete waste of money and time.',
                'Unprofessional and low quality work.',
            ]
        ];

        $reviewedOrderIds = [];
        $reviewCount = 0;

        // Review 60% of completed orders with weighted ratings (skewed positive)
        $ordersToReview = $completedOrders->random(min((int)($completedOrders->count() * 0.6), $completedOrders->count()));

        foreach ($ordersToReview as $order) {
            if (in_array($order->id, $reviewedOrderIds)) {
                continue;
            }

            // Weighted rating distribution (realistic - mostly positive)
            // 5 stars: 40%, 4 stars: 35%, 3 stars: 15%, 2 stars: 7%, 1 star: 3%
            $rand = rand(1, 100);
            if ($rand <= 40) {
                $rating = 5;
            } elseif ($rand <= 75) {
                $rating = 4;
            } elseif ($rand <= 90) {
                $rating = 3;
            } elseif ($rand <= 97) {
                $rating = 2;
            } else {
                $rating = 1;
            }

            // Select random comment for rating (80% chance to include comment)
            $comment = null;
            if (rand(1, 100) <= 80) {
                $comments = $reviewComments[$rating];
                $comment = $comments[array_rand($comments)];
            }

            ServiceReview::create([
                'service_id' => $order->service_id,
                'vendor_id' => $order->service->vendor_id,
                'user_id' => $order->user_id,
                'order_id' => $order->id,
                'rating' => $rating,
                'comment' => $comment,
                'status' => 'visible'
            ]);

            $reviewedOrderIds[] = $order->id;
            $reviewCount++;
        }

        // Recalculate ratings for all services
        $this->command->info('Recalculating service ratings...');
        Service::all()->each(function ($service) {
            ServiceReview::recalculateServiceRating($service->id);
        });

        // Recalculate ratings for all vendors
        $this->command->info('Recalculating vendor ratings...');
        \App\Models\Vendor::all()->each(function ($vendor) {
            ServiceReview::recalculateVendorRating($vendor->id);
        });

        $this->command->info("Seeded {$reviewCount} service reviews with calculated ratings.");
    }
}