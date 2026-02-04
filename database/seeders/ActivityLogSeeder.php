<?php

namespace Database\Seeders;

use App\Models\ActivityLog;
use App\Models\User;
use App\Models\Order;
use App\Models\Service;
use App\Models\Vendor;
use Illuminate\Database\Seeder;

class ActivityLogSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('role', 'admin')->first();
        $customers = User::where('role', 'customer')->get();
        $vendors = User::where('role', 'vendor')->get();

        // User registration activities
        foreach ($customers as $customer) {
            ActivityLog::create([
                'user_id' => $customer->id,
                'action' => 'user_registered',
                'description' => "User {$customer->full_name} registered as a customer",
                'subject_type' => 'User',
                'subject_id' => $customer->id,
                'ip_address' => '197.210.85.' . fake()->numberBetween(1, 254),
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'created_at' => $customer->created_at
            ]);

            ActivityLog::create([
                'user_id' => $customer->id,
                'action' => 'email_verified',
                'description' => "Email verified for {$customer->email}",
                'subject_type' => 'User',
                'subject_id' => $customer->id,
                'ip_address' => '197.210.85.' . fake()->numberBetween(1, 254),
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'created_at' => $customer->email_verified_at ?? $customer->created_at->addHour()
            ]);
        }

        // Vendor registration and verification
        foreach ($vendors as $vendor) {
            ActivityLog::create([
                'user_id' => $vendor->id,
                'action' => 'vendor_registered',
                'description' => "Vendor {$vendor->full_name} registered",
                'subject_type' => 'User',
                'subject_id' => $vendor->id,
                'ip_address' => '197.210.85.' . fake()->numberBetween(1, 254),
                'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36',
                'created_at' => $vendor->created_at
            ]);

            $vendorProfile = Vendor::where('user_id', $vendor->id)->first();
            if ($vendorProfile && $vendorProfile->is_verified) {
                ActivityLog::create([
                    'user_id' => $admin?->id,
                    'action' => 'vendor_verified',
                    'description' => "Admin verified vendor: {$vendorProfile->business_name}",
                    'subject_type' => 'Vendor',
                    'subject_id' => $vendorProfile->id,
                    'ip_address' => '197.210.85.' . fake()->numberBetween(1, 254),
                    'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                    'created_at' => $vendorProfile->verified_at ?? $vendor->created_at->addDay()
                ]);
            }
        }

        // Service activities
        $services = Service::all();
        
        foreach ($services->take(10) as $service) {
            // Service creation
            ActivityLog::create([
                'user_id' => $service->vendor->user_id,
                'action' => 'service_created',
                'description' => "Created service: {$service->title}",
                'subject_type' => 'Service',
                'subject_id' => $service->id,
                'ip_address' => '197.210.85.' . fake()->numberBetween(1, 254),
                'user_agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15',
                'created_at' => $service->created_at
            ]);

            // Service approval (for approved services)
            if ($service->status === 'approved') {
                ActivityLog::create([
                    'user_id' => $admin?->id,
                    'action' => 'service_approved',
                    'description' => "Admin approved service: {$service->title}",
                    'subject_type' => 'Service',
                    'subject_id' => $service->id,
                    'ip_address' => '197.210.85.' . fake()->numberBetween(1, 254),
                    'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                    'created_at' => $service->approved_at ?? $service->created_at->addDay()
                ]);
            }
        }

        // Order activities
        $orders = Order::all();

        foreach ($orders as $order) {
            // Order creation
            ActivityLog::create([
                'user_id' => $order->customer_id,
                'action' => 'order_created',
                'description' => "Customer created order #{$order->order_reference} for {$order->service_title}",
                'subject_type' => 'Order',
                'subject_id' => $order->id,
                'ip_address' => '197.210.85.' . fake()->numberBetween(1, 254),
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'created_at' => $order->created_at
            ]);

            // Payment activities
            if ($order->is_paid) {
                ActivityLog::create([
                    'user_id' => $order->customer_id,
                    'action' => 'payment_initiated',
                    'description' => "Payment initiated for order #{$order->order_reference}",
                    'subject_type' => 'Order',
                    'subject_id' => $order->id,
                    'ip_address' => '197.210.85.' . fake()->numberBetween(1, 254),
                    'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                    'created_at' => $order->paid_at->subMinutes(2)
                ]);

                ActivityLog::create([
                    'user_id' => $order->customer_id,
                    'action' => 'payment_completed',
                    'description' => "Payment of {$order->formatted_total} completed for order #{$order->order_reference}",
                    'subject_type' => 'Order',
                    'subject_id' => $order->id,
                    'ip_address' => '197.210.85.' . fake()->numberBetween(1, 254),
                    'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                    'created_at' => $order->paid_at
                ]);
            }

            // Order completion
            if ($order->is_completed) {
                ActivityLog::create([
                    'user_id' => $order->vendor->user_id,
                    'action' => 'order_completed',
                    'description' => "Vendor marked order #{$order->order_reference} as completed",
                    'subject_type' => 'Order',
                    'subject_id' => $order->id,
                    'ip_address' => '197.210.85.' . fake()->numberBetween(1, 254),
                    'user_agent' => 'Mozilla/5.0 (iPad; CPU OS 17_0 like Mac OS X) AppleWebKit/605.1.15',
                    'created_at' => $order->completed_at
                ]);
            }

            // Order cancellation
            if ($order->is_cancelled) {
                ActivityLog::create([
                    'user_id' => $order->customer_id,
                    'action' => 'order_cancelled',
                    'description' => "Order #{$order->order_reference} cancelled. Reason: {$order->cancellation_reason}",
                    'subject_type' => 'Order',
                    'subject_id' => $order->id,
                    'ip_address' => '197.210.85.' . fake()->numberBetween(1, 254),
                    'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                    'created_at' => $order->cancelled_at
                ]);

                if ($order->is_refunded) {
                    ActivityLog::create([
                        'user_id' => $admin?->id,
                        'action' => 'payment_refunded',
                        'description' => "Refund of {$order->formatted_total} processed for order #{$order->order_reference}",
                        'subject_type' => 'Order',
                        'subject_id' => $order->id,
                        'ip_address' => '197.210.85.' . fake()->numberBetween(1, 254),
                        'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                        'created_at' => $order->cancelled_at->addHour()
                    ]);
                }
            }

            // Disputed orders
            if ($order->is_disputed) {
                ActivityLog::create([
                    'user_id' => $order->customer_id,
                    'action' => 'dispute_opened',
                    'description' => "Dispute opened for order #{$order->order_reference}",
                    'subject_type' => 'Order',
                    'subject_id' => $order->id,
                    'ip_address' => '197.210.85.' . fake()->numberBetween(1, 254),
                    'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                    'created_at' => $order->disputed_at
                ]);
            }
        }

        // Admin activities
        if ($admin) {
            ActivityLog::create([
                'user_id' => $admin->id,
                'action' => 'banner_created',
                'description' => 'Admin created homepage banner',
                'subject_type' => 'Banner',
                'subject_id' => 1,
                'ip_address' => '197.210.85.' . fake()->numberBetween(1, 254),
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'created_at' => now()->subDays(5)
            ]);

            ActivityLog::create([
                'user_id' => $admin->id,
                'action' => 'promotion_created',
                'description' => 'Admin created service promotion campaign',
                'subject_type' => 'ServicePromotion',
                'subject_id' => 1,
                'ip_address' => '197.210.85.' . fake()->numberBetween(1, 254),
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'created_at' => now()->subDays(3)
            ]);

            ActivityLog::create([
                'user_id' => $admin->id,
                'action' => 'category_created',
                'description' => 'Admin created new service category',
                'subject_type' => 'Category',
                'subject_id' => 1,
                'ip_address' => '197.210.85.' . fake()->numberBetween(1, 254),
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'created_at' => now()->subMonths(2)
            ]);
        }

        // System activities
        ActivityLog::create([
            'user_id' => null,
            'action' => 'system_maintenance',
            'description' => 'Scheduled system maintenance completed',
            'subject_type' => null,
            'subject_id' => null,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'System/Cron',
            'created_at' => now()->subDays(7)
        ]);
    }
}