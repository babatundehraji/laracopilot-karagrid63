<?php

namespace Database\Seeders;

use App\Models\Transaction;
use App\Models\Order;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Database\Seeder;

class TransactionSeeder extends Seeder
{
    public function run(): void
    {
        // Get paid orders
        $paidOrders = Order::where('payment_status', 'paid')->with(['customer', 'vendor'])->get();

        foreach ($paidOrders as $order) {
            // 1. Customer debit (order payment)
            $customerBalance = Transaction::getUserBalance($order->customer_id);
            Transaction::create([
                'user_id' => $order->customer_id,
                'order_id' => $order->id,
                'type' => 'debit',
                'category' => 'order',
                'amount' => $order->total_amount,
                'currency' => $order->currency,
                'balance_after' => $customerBalance - $order->total_amount,
                'reference' => $order->order_reference,
                'meta' => [
                    'service_title' => $order->service_title,
                    'vendor_name' => $order->vendor->business_name,
                    'service_date' => $order->service_date->toDateString()
                ],
                'status' => 'completed',
                'created_at' => $order->paid_at,
                'updated_at' => $order->paid_at
            ]);

            // 2. Vendor credit (earning)
            $vendorUser = $order->vendor->user;
            $vendorBalance = Transaction::getUserBalance($vendorUser->id);
            $vendorEarning = $order->subtotal; // Vendor gets subtotal (before fees)
            
            Transaction::create([
                'user_id' => $vendorUser->id,
                'order_id' => $order->id,
                'type' => 'credit',
                'category' => 'earning',
                'amount' => $vendorEarning,
                'currency' => $order->currency,
                'balance_after' => $vendorBalance + $vendorEarning,
                'reference' => $order->order_reference,
                'meta' => [
                    'service_title' => $order->service_title,
                    'customer_name' => $order->customer->full_name,
                    'service_date' => $order->service_date->toDateString(),
                    'gross_amount' => $order->total_amount,
                    'platform_fee' => $order->platform_fee,
                    'net_earning' => $vendorEarning
                ],
                'status' => 'completed',
                'created_at' => $order->paid_at,
                'updated_at' => $order->paid_at
            ]);

            // 3. Platform credit (fee collection)
            $adminUser = User::where('role', 'admin')->first();
            if ($adminUser) {
                $platformBalance = Transaction::getUserBalance($adminUser->id);
                Transaction::create([
                    'user_id' => $adminUser->id,
                    'order_id' => $order->id,
                    'type' => 'credit',
                    'category' => 'fee',
                    'amount' => $order->platform_fee + $order->tax_amount,
                    'currency' => $order->currency,
                    'balance_after' => $platformBalance + $order->platform_fee + $order->tax_amount,
                    'reference' => $order->order_reference,
                    'meta' => [
                        'order_reference' => $order->order_reference,
                        'platform_fee' => $order->platform_fee,
                        'tax_amount' => $order->tax_amount,
                        'vendor_business' => $order->vendor->business_name
                    ],
                    'status' => 'completed',
                    'created_at' => $order->paid_at,
                    'updated_at' => $order->paid_at
                ]);
            }
        }

        // Handle refunded orders
        $refundedOrders = Order::where('payment_status', 'refunded')->with(['customer', 'vendor'])->get();

        foreach ($refundedOrders as $order) {
            // Customer credit (refund)
            $customerBalance = Transaction::getUserBalance($order->customer_id);
            Transaction::create([
                'user_id' => $order->customer_id,
                'order_id' => $order->id,
                'type' => 'credit',
                'category' => 'refund',
                'amount' => $order->total_amount,
                'currency' => $order->currency,
                'balance_after' => $customerBalance + $order->total_amount,
                'reference' => 'REF-' . $order->order_reference,
                'meta' => [
                    'original_order' => $order->order_reference,
                    'refund_reason' => $order->cancellation_reason ?? 'Order cancelled',
                    'refund_date' => $order->cancelled_at?->toDateString()
                ],
                'status' => 'completed',
                'created_at' => $order->cancelled_at ?? now(),
                'updated_at' => $order->cancelled_at ?? now()
            ]);

            // Vendor debit (refund deduction)
            $vendorUser = $order->vendor->user;
            $vendorBalance = Transaction::getUserBalance($vendorUser->id);
            Transaction::create([
                'user_id' => $vendorUser->id,
                'order_id' => $order->id,
                'type' => 'debit',
                'category' => 'refund',
                'amount' => $order->subtotal,
                'currency' => $order->currency,
                'balance_after' => $vendorBalance - $order->subtotal,
                'reference' => 'REF-' . $order->order_reference,
                'meta' => [
                    'original_order' => $order->order_reference,
                    'refund_reason' => $order->cancellation_reason ?? 'Order cancelled'
                ],
                'status' => 'completed',
                'created_at' => $order->cancelled_at ?? now(),
                'updated_at' => $order->cancelled_at ?? now()
            ]);
        }

        // Create some promotion transactions (vendors paying for ads)
        $vendors = Vendor::where('is_verified', true)->with('user')->limit(3)->get();
        
        foreach ($vendors as $vendor) {
            $promotionAmount = fake()->numberBetween(5000, 20000);
            $vendorBalance = Transaction::getUserBalance($vendor->user_id);
            
            Transaction::create([
                'user_id' => $vendor->user_id,
                'order_id' => null,
                'type' => 'debit',
                'category' => 'promotion',
                'amount' => $promotionAmount,
                'currency' => 'NGN',
                'balance_after' => $vendorBalance - $promotionAmount,
                'reference' => 'PROMO-' . strtoupper(uniqid()),
                'meta' => [
                    'promotion_type' => fake()->randomElement(['Featured Listing', 'Sponsored Ad', 'Homepage Banner']),
                    'duration_days' => fake()->randomElement([7, 14, 30]),
                    'start_date' => now()->toDateString()
                ],
                'status' => 'completed',
                'created_at' => now()->subDays(rand(1, 30))
            ]);
        }

        // Create some payout transactions (vendors withdrawing funds)
        foreach ($vendors as $vendor) {
            $vendorBalance = Transaction::getUserBalance($vendor->user_id);
            
            if ($vendorBalance > 50000) {
                $payoutAmount = fake()->numberBetween(30000, min(100000, $vendorBalance));
                
                Transaction::create([
                    'user_id' => $vendor->user_id,
                    'order_id' => null,
                    'type' => 'debit',
                    'category' => 'payout',
                    'amount' => $payoutAmount,
                    'currency' => 'NGN',
                    'balance_after' => $vendorBalance - $payoutAmount,
                    'reference' => 'PAYOUT-' . strtoupper(uniqid()),
                    'meta' => [
                        'bank_name' => fake()->randomElement(['GTBank', 'Access Bank', 'First Bank', 'UBA', 'Zenith Bank']),
                        'account_number' => '**********' . fake()->numberBetween(10, 99),
                        'payout_method' => 'bank_transfer',
                        'processing_fee' => 100
                    ],
                    'status' => 'completed',
                    'created_at' => now()->subDays(rand(1, 15))
                ]);
            }
        }

        // Create manual adjustment transaction
        $customerUser = User::where('role', 'customer')->first();
        if ($customerUser) {
            $balance = Transaction::getUserBalance($customerUser->id);
            Transaction::create([
                'user_id' => $customerUser->id,
                'order_id' => null,
                'type' => 'credit',
                'category' => 'adjustment',
                'amount' => 5000,
                'currency' => 'NGN',
                'balance_after' => $balance + 5000,
                'reference' => 'ADJ-' . strtoupper(uniqid()),
                'meta' => [
                    'reason' => 'Compensation for service delay',
                    'adjusted_by' => 'Admin',
                    'notes' => 'Customer goodwill gesture'
                ],
                'status' => 'completed',
                'created_at' => now()->subDays(5)
            ]);
        }
    }
}