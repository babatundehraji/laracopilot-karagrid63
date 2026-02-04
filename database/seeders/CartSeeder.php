<?php

namespace Database\Seeders;

use App\Models\Cart;
use App\Models\User;
use App\Models\Service;
use Illuminate\Database\Seeder;

class CartSeeder extends Seeder
{
    public function run(): void
    {
        $customers = User::where('role', 'customer')->get();
        $services = Service::where('status', 'approved')
            ->where('is_active', true)
            ->get();

        if ($customers->isEmpty() || $services->isEmpty()) {
            return;
        }

        // Create cart items for 60% of customers
        $customersWithCarts = $customers->random(intval($customers->count() * 0.6));

        foreach ($customersWithCarts as $customer) {
            // Each customer has 1-4 items in cart
            $itemCount = fake()->numberBetween(1, 4);
            $selectedServices = $services->random(min($itemCount, $services->count()));

            foreach ($selectedServices as $service) {
                // Vary the created_at to simulate realistic cart behavior
                $daysAgo = fake()->numberBetween(0, 20);
                $hoursAgo = fake()->numberBetween(0, 23);
                $createdAt = now()->subDays($daysAgo)->subHours($hoursAgo);

                Cart::create([
                    'user_id' => $customer->id,
                    'service_id' => $service->id,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt
                ]);
            }
        }

        // Create some stale cart items (older than 30 days)
        $customersForStaleItems = $customers->random(min(3, $customers->count()));

        foreach ($customersForStaleItems as $customer) {
            $staleService = $services->random();
            $daysAgo = fake()->numberBetween(31, 60);

            // Check if not already in cart
            if (!Cart::isInCart($customer->id, $staleService->id)) {
                Cart::create([
                    'user_id' => $customer->id,
                    'service_id' => $staleService->id,
                    'created_at' => now()->subDays($daysAgo),
                    'updated_at' => now()->subDays($daysAgo)
                ]);
            }
        }

        // Create recent cart items (last 3 days)
        $customersForRecentItems = $customers->random(min(5, $customers->count()));

        foreach ($customersForRecentItems as $customer) {
            $recentServices = $services->random(min(2, $services->count()));

            foreach ($recentServices as $service) {
                $hoursAgo = fake()->numberBetween(1, 72); // Last 3 days

                // Check if not already in cart
                if (!Cart::isInCart($customer->id, $service->id)) {
                    Cart::create([
                        'user_id' => $customer->id,
                        'service_id' => $service->id,
                        'created_at' => now()->subHours($hoursAgo),
                        'updated_at' => now()->subHours($hoursAgo)
                    ]);
                }
            }
        }
    }
}