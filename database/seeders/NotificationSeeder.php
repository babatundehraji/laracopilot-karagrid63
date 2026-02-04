<?php

namespace Database\Seeders;

use App\Models\Notification;
use App\Models\User;
use App\Models\Order;
use App\Models\Dispute;
use Illuminate\Database\Seeder;

class NotificationSeeder extends Seeder
{
    public function run(): void
    {
        $customers = User::where('role', 'customer')->limit(3)->get();
        $vendors = User::where('role', 'vendor')->limit(3)->get();

        if ($customers->isEmpty()) {
            return;
        }

        foreach ($customers as $customer) {
            // Order confirmation notification
            $order = Order::where('customer_id', $customer->id)->first();
            if ($order) {
                Notification::create([
                    'user_id' => $customer->id,
                    'title' => 'Order Confirmed',
                    'body' => "Your order #{$order->order_reference} has been confirmed. The service is scheduled for {$order->formatted_service_date}.",
                    'type' => 'order',
                    'data' => [
                        'order_id' => $order->id,
                        'order_reference' => $order->order_reference,
                        'service_date' => $order->service_date->toDateString()
                    ],
                    'is_read' => false,
                    'created_at' => $order->created_at->addMinutes(5)
                ]);

                // Payment success notification
                if ($order->is_paid) {
                    Notification::create([
                        'user_id' => $customer->id,
                        'title' => 'Payment Successful',
                        'body' => "Payment of {$order->formatted_total} for order #{$order->order_reference} was successful. Your service is confirmed.",
                        'type' => 'payment',
                        'data' => [
                            'order_id' => $order->id,
                            'amount' => $order->total_amount,
                            'currency' => $order->currency
                        ],
                        'is_read' => true,
                        'read_at' => $order->paid_at->addMinutes(10),
                        'created_at' => $order->paid_at
                    ]);
                }
            }

            // Welcome notification
            Notification::create([
                'user_id' => $customer->id,
                'title' => 'Welcome to Sucheus! 🎉',
                'body' => 'Thank you for joining Sucheus. Discover verified service providers for all your needs. Browse categories and book your first service today!',
                'type' => 'system',
                'data' => [
                    'url' => '/services'
                ],
                'is_read' => true,
                'read_at' => $customer->created_at->addHour(),
                'created_at' => $customer->created_at
            ]);

            // Promotion notification
            Notification::create([
                'user_id' => $customer->id,
                'title' => '🔥 Special Offers Available',
                'body' => 'Save up to 30% on featured services this week! Check out our limited-time deals and book your service now.',
                'type' => 'promotion',
                'data' => [
                    'url' => '/promotions'
                ],
                'is_read' => fake()->boolean(60),
                'read_at' => fake()->boolean(60) ? now()->subDays(1) : null,
                'created_at' => now()->subDays(2)
            ]);

            // Service reminder
            $upcomingOrder = Order::where('customer_id', $customer->id)
                ->where('service_date', '>=', now()->toDateString())
                ->first();

            if ($upcomingOrder) {
                Notification::create([
                    'user_id' => $customer->id,
                    'title' => 'Service Reminder',
                    'body' => "Reminder: Your service is scheduled for tomorrow at {$upcomingOrder->time_range}. The vendor will contact you shortly.",
                    'type' => 'order',
                    'data' => [
                        'order_id' => $upcomingOrder->id,
                        'service_date' => $upcomingOrder->service_date->toDateString()
                    ],
                    'is_read' => false,
                    'created_at' => now()->subHours(5)
                ]);
            }

            // Message notification
            Notification::create([
                'user_id' => $customer->id,
                'title' => 'New Message',
                'body' => 'You have a new message from your service provider. Check your inbox to view the message.',
                'type' => 'message',
                'data' => [
                    'conversation_id' => 1
                ],
                'is_read' => false,
                'created_at' => now()->subMinutes(30)
            ]);
        }

        // Vendor notifications
        foreach ($vendors as $vendor) {
            $vendorOrders = Order::whereHas('vendor', function($q) use ($vendor) {
                $q->where('user_id', $vendor->id);
            })->get();

            if ($vendorOrders->isNotEmpty()) {
                $order = $vendorOrders->first();

                // New booking notification
                Notification::create([
                    'user_id' => $vendor->id,
                    'title' => 'New Booking Received',
                    'body' => "You have a new booking for {$order->service_title}. Service date: {$order->formatted_service_date}.",
                    'type' => 'order',
                    'data' => [
                        'order_id' => $order->id,
                        'order_reference' => $order->order_reference
                    ],
                    'is_read' => true,
                    'read_at' => $order->created_at->addMinutes(15),
                    'created_at' => $order->created_at->addMinutes(5)
                ]);

                // Payment received notification
                if ($order->is_paid) {
                    Notification::create([
                        'user_id' => $vendor->id,
                        'title' => 'Payment Received',
                        'body' => "Payment of {$order->formatted_subtotal} has been received for order #{$order->order_reference}. Funds will be available after service completion.",
                        'type' => 'payment',
                        'data' => [
                            'order_id' => $order->id,
                            'amount' => $order->subtotal
                        ],
                        'is_read' => true,
                        'read_at' => $order->paid_at->addHour(),
                        'created_at' => $order->paid_at->addMinutes(10)
                    ]);
                }
            }

            // Service approved notification
            Notification::create([
                'user_id' => $vendor->id,
                'title' => 'Service Approved ✅',
                'body' => 'Your service listing has been approved and is now live on the platform. Customers can now discover and book your service.',
                'type' => 'admin',
                'data' => [
                    'url' => '/vendor/services'
                ],
                'is_read' => true,
                'read_at' => $vendor->created_at->addDays(2),
                'created_at' => $vendor->created_at->addDay()
            ]);

            // Review request
            Notification::create([
                'user_id' => $vendor->id,
                'title' => 'Update Your Profile',
                'body' => 'Complete your vendor profile to increase your visibility. Add photos, update your bio, and showcase your expertise.',
                'type' => 'system',
                'data' => [
                    'url' => '/vendor/profile'
                ],
                'is_read' => false,
                'created_at' => now()->subDays(1)
            ]);
        }

        // Dispute notifications
        $dispute = Dispute::with(['customer', 'vendor.user'])->first();
        if ($dispute) {
            // Customer notification
            Notification::create([
                'user_id' => $dispute->customer_id,
                'title' => 'Dispute Under Review',
                'body' => "Your dispute for order #{$dispute->order->order_reference} is now under review. Our team will investigate and respond within 48 hours.",
                'type' => 'dispute',
                'data' => [
                    'dispute_id' => $dispute->id,
                    'order_id' => $dispute->order_id
                ],
                'is_read' => true,
                'read_at' => $dispute->opened_at->addHours(2),
                'created_at' => $dispute->opened_at->addMinutes(30)
            ]);

            // Vendor notification
            Notification::create([
                'user_id' => $dispute->vendor->user_id,
                'title' => 'Dispute Filed',
                'body' => "A customer has filed a dispute for order #{$dispute->order->order_reference}. Please provide your response and any supporting evidence.",
                'type' => 'dispute',
                'data' => [
                    'dispute_id' => $dispute->id,
                    'order_id' => $dispute->order_id
                ],
                'is_read' => false,
                'created_at' => $dispute->opened_at->addMinutes(30)
            ]);
        }

        // Admin notifications
        $admin = User::where('role', 'admin')->first();
        if ($admin) {
            Notification::create([
                'user_id' => $admin->id,
                'title' => 'New Vendor Registration',
                'body' => 'A new vendor has registered and is awaiting verification. Please review their profile and documentation.',
                'type' => 'admin',
                'data' => [
                    'url' => '/admin/vendors/pending'
                ],
                'is_read' => false,
                'created_at' => now()->subHours(3)
            ]);

            Notification::create([
                'user_id' => $admin->id,
                'title' => 'Platform Statistics',
                'body' => 'Weekly report: 150 new orders, 25 new users, ₦2.5M in transactions. View detailed analytics in the dashboard.',
                'type' => 'system',
                'data' => [
                    'url' => '/admin/analytics'
                ],
                'is_read' => true,
                'read_at' => now()->subDays(1),
                'created_at' => now()->subDays(2)
            ]);
        }
    }
}