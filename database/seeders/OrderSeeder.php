<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\User;
use App\Models\Vendor;
use App\Models\Service;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $customers = User::where('role', 'customer')->get();
        $services = Service::where('status', 'approved')->with('vendor')->get();

        if ($customers->isEmpty() || $services->isEmpty()) {
            return;
        }

        // Create completed orders (past dates)
        for ($i = 0; $i < 15; $i++) {
            $service = $services->random();
            $customer = $customers->random();
            $serviceDate = Carbon::now()->subDays(rand(1, 60));
            
            $hours = $service->pricing_type === 'hourly' ? rand(2, 6) : null;
            $subtotal = $service->pricing_type === 'hourly' ? ($service->price * $hours) : $service->price;
            $platformFee = $subtotal * 0.10; // 10% platform fee
            $taxAmount = ($subtotal + $platformFee) * 0.075; // 7.5% VAT
            $totalAmount = $subtotal + $platformFee + $taxAmount;

            Order::create([
                'customer_id' => $customer->id,
                'vendor_id' => $service->vendor_id,
                'service_id' => $service->id,
                'service_title' => $service->title,
                'service_pricing_type' => $service->pricing_type,
                'service_price' => $service->price,
                'status' => 'completed',
                'service_date' => $serviceDate->toDateString(),
                'start_time' => '09:00:00',
                'hours' => $hours,
                'end_time' => $hours ? Carbon::parse('09:00:00')->addHours($hours)->format('H:i:s') : '17:00:00',
                'location_type' => $service->is_remote && fake()->boolean(30) ? 'remote' : 'onsite',
                'address_line1' => $service->is_onsite ? fake()->streetAddress() : null,
                'city' => $service->is_onsite ? $service->serviceCity?->name : null,
                'state' => $service->is_onsite ? $service->serviceState?->name : null,
                'country' => $service->is_onsite ? $service->serviceCountry?->name : null,
                'latitude' => $service->is_onsite ? $service->latitude : null,
                'longitude' => $service->is_onsite ? $service->longitude : null,
                'currency' => 'NGN',
                'subtotal' => $subtotal,
                'discount_amount' => 0,
                'platform_fee' => $platformFee,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
                'payment_status' => 'paid',
                'paid_at' => $serviceDate->subDays(1),
                'completed_at' => $serviceDate->addHours(rand(3, 8)),
                'customer_note' => fake()->boolean(40) ? fake()->sentence(8) : null,
                'vendor_note' => fake()->boolean(30) ? fake()->sentence(6) : null
            ]);
        }

        // Create active orders (upcoming dates)
        for ($i = 0; $i < 10; $i++) {
            $service = $services->random();
            $customer = $customers->random();
            $serviceDate = Carbon::now()->addDays(rand(1, 30));
            
            $hours = $service->pricing_type === 'hourly' ? rand(2, 6) : null;
            $subtotal = $service->pricing_type === 'hourly' ? ($service->price * $hours) : $service->price;
            $platformFee = $subtotal * 0.10;
            $taxAmount = ($subtotal + $platformFee) * 0.075;
            $totalAmount = $subtotal + $platformFee + $taxAmount;

            Order::create([
                'customer_id' => $customer->id,
                'vendor_id' => $service->vendor_id,
                'service_id' => $service->id,
                'service_title' => $service->title,
                'service_pricing_type' => $service->pricing_type,
                'service_price' => $service->price,
                'status' => 'active',
                'service_date' => $serviceDate->toDateString(),
                'start_time' => fake()->randomElement(['08:00:00', '09:00:00', '10:00:00', '14:00:00']),
                'hours' => $hours,
                'end_time' => $hours ? Carbon::parse('09:00:00')->addHours($hours)->format('H:i:s') : null,
                'location_type' => $service->is_remote && fake()->boolean(30) ? 'remote' : 'onsite',
                'address_line1' => $service->is_onsite ? fake()->streetAddress() : null,
                'city' => $service->is_onsite ? $service->serviceCity?->name : null,
                'state' => $service->is_onsite ? $service->serviceState?->name : null,
                'country' => $service->is_onsite ? $service->serviceCountry?->name : null,
                'latitude' => $service->is_onsite ? $service->latitude : null,
                'longitude' => $service->is_onsite ? $service->longitude : null,
                'currency' => 'NGN',
                'subtotal' => $subtotal,
                'discount_amount' => 0,
                'platform_fee' => $platformFee,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
                'payment_status' => 'paid',
                'paid_at' => now()->subDays(rand(1, 5)),
                'customer_note' => fake()->boolean(50) ? fake()->sentence(10) : null
            ]);
        }

        // Create pending orders
        for ($i = 0; $i < 8; $i++) {
            $service = $services->random();
            $customer = $customers->random();
            $serviceDate = Carbon::now()->addDays(rand(3, 20));
            
            $hours = $service->pricing_type === 'hourly' ? rand(2, 6) : null;
            $subtotal = $service->pricing_type === 'hourly' ? ($service->price * $hours) : $service->price;
            $platformFee = $subtotal * 0.10;
            $taxAmount = ($subtotal + $platformFee) * 0.075;
            $totalAmount = $subtotal + $platformFee + $taxAmount;

            Order::create([
                'customer_id' => $customer->id,
                'vendor_id' => $service->vendor_id,
                'service_id' => $service->id,
                'service_title' => $service->title,
                'service_pricing_type' => $service->pricing_type,
                'service_price' => $service->price,
                'status' => 'pending',
                'service_date' => $serviceDate->toDateString(),
                'start_time' => fake()->randomElement(['09:00:00', '10:00:00', '14:00:00', '15:00:00']),
                'hours' => $hours,
                'end_time' => $hours ? Carbon::parse('09:00:00')->addHours($hours)->format('H:i:s') : null,
                'location_type' => $service->is_remote && fake()->boolean(30) ? 'remote' : 'onsite',
                'address_line1' => $service->is_onsite ? fake()->streetAddress() : null,
                'city' => $service->is_onsite ? $service->serviceCity?->name : null,
                'state' => $service->is_onsite ? $service->serviceState?->name : null,
                'country' => $service->is_onsite ? $service->serviceCountry?->name : null,
                'currency' => 'NGN',
                'subtotal' => $subtotal,
                'discount_amount' => 0,
                'platform_fee' => $platformFee,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
                'payment_status' => 'unpaid',
                'customer_note' => fake()->boolean(60) ? fake()->sentence(12) : null
            ]);
        }

        // Create cancelled orders
        for ($i = 0; $i < 5; $i++) {
            $service = $services->random();
            $customer = $customers->random();
            $serviceDate = Carbon::now()->addDays(rand(5, 15));
            
            $hours = $service->pricing_type === 'hourly' ? rand(2, 6) : null;
            $subtotal = $service->pricing_type === 'hourly' ? ($service->price * $hours) : $service->price;
            $platformFee = $subtotal * 0.10;
            $taxAmount = ($subtotal + $platformFee) * 0.075;
            $totalAmount = $subtotal + $platformFee + $taxAmount;

            $cancellationReasons = [
                'Customer request - Change of plans',
                'Vendor unavailable on selected date',
                'Customer found alternative service provider',
                'Payment issues',
                'Service location changed'
            ];

            Order::create([
                'customer_id' => $customer->id,
                'vendor_id' => $service->vendor_id,
                'service_id' => $service->id,
                'service_title' => $service->title,
                'service_pricing_type' => $service->pricing_type,
                'service_price' => $service->price,
                'status' => 'cancelled',
                'service_date' => $serviceDate->toDateString(),
                'start_time' => '10:00:00',
                'hours' => $hours,
                'location_type' => 'onsite',
                'address_line1' => fake()->streetAddress(),
                'city' => $service->serviceCity?->name,
                'state' => $service->serviceState?->name,
                'country' => $service->serviceCountry?->name,
                'currency' => 'NGN',
                'subtotal' => $subtotal,
                'platform_fee' => $platformFee,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
                'payment_status' => fake()->randomElement(['unpaid', 'refunded']),
                'cancelled_at' => now()->subDays(rand(1, 4)),
                'cancellation_reason' => fake()->randomElement($cancellationReasons)
            ]);
        }

        // Create disputed order
        $service = $services->random();
        $customer = $customers->random();
        $serviceDate = Carbon::now()->subDays(rand(5, 20));
        
        $hours = 4;
        $subtotal = $service->price * $hours;
        $platformFee = $subtotal * 0.10;
        $taxAmount = ($subtotal + $platformFee) * 0.075;
        $totalAmount = $subtotal + $platformFee + $taxAmount;

        Order::create([
            'customer_id' => $customer->id,
            'vendor_id' => $service->vendor_id,
            'service_id' => $service->id,
            'service_title' => $service->title,
            'service_pricing_type' => 'hourly',
            'service_price' => $service->price,
            'status' => 'disputed',
            'service_date' => $serviceDate->toDateString(),
            'start_time' => '10:00:00',
            'hours' => $hours,
            'end_time' => '14:00:00',
            'location_type' => 'onsite',
            'address_line1' => fake()->streetAddress(),
            'city' => $service->serviceCity?->name,
            'state' => $service->serviceState?->name,
            'country' => $service->serviceCountry?->name,
            'currency' => 'NGN',
            'subtotal' => $subtotal,
            'platform_fee' => $platformFee,
            'tax_amount' => $taxAmount,
            'total_amount' => $totalAmount,
            'payment_status' => 'paid',
            'paid_at' => $serviceDate->subDay(),
            'disputed_at' => $serviceDate->addDays(2),
            'customer_note' => 'Service quality was not as expected. Issues with completion.',
            'vendor_note' => 'Service was completed as agreed. Customer expectations were unclear.'
        ]);
    }
}