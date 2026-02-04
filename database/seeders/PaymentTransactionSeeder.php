<?php

namespace Database\Seeders;

use App\Models\PaymentTransaction;
use App\Models\Order;
use Illuminate\Database\Seeder;

class PaymentTransactionSeeder extends Seeder
{
    public function run(): void
    {
        // Get paid orders
        $paidOrders = Order::where('payment_status', 'paid')->with('customer')->get();

        foreach ($paidOrders as $order) {
            // Successful payment transaction
            PaymentTransaction::create([
                'order_id' => $order->id,
                'user_id' => $order->customer_id,
                'provider' => fake()->randomElement(['paystack', 'flutterwave', 'stripe']),
                'provider_payment_id' => 'pi_' . strtoupper(uniqid()),
                'provider_charge_id' => 'ch_' . strtoupper(uniqid()),
                'amount' => $order->total_amount,
                'currency' => $order->currency,
                'status' => 'succeeded',
                'raw_request' => [
                    'amount' => $order->total_amount * 100, // in kobo/cents
                    'currency' => strtolower($order->currency),
                    'email' => $order->customer->email,
                    'reference' => $order->order_reference
                ],
                'raw_response' => [
                    'status' => true,
                    'message' => 'Payment successful',
                    'data' => [
                        'id' => rand(1000000, 9999999),
                        'amount' => $order->total_amount * 100,
                        'currency' => strtoupper($order->currency),
                        'status' => 'success',
                        'paid_at' => now()->toISOString()
                    ]
                ],
                'paid_at' => $order->paid_at,
                'created_at' => $order->paid_at,
                'updated_at' => $order->paid_at
            ]);
        }

        // Get refunded orders
        $refundedOrders = Order::where('payment_status', 'refunded')->with('customer')->get();

        foreach ($refundedOrders as $order) {
            // Original successful payment
            $originalPayment = PaymentTransaction::create([
                'order_id' => $order->id,
                'user_id' => $order->customer_id,
                'provider' => 'paystack',
                'provider_payment_id' => 'pi_' . strtoupper(uniqid()),
                'provider_charge_id' => 'ch_' . strtoupper(uniqid()),
                'amount' => $order->total_amount,
                'currency' => $order->currency,
                'status' => 'succeeded',
                'paid_at' => $order->created_at->addHour(),
                'created_at' => $order->created_at->addHour(),
                'updated_at' => $order->created_at->addHour()
            ]);

            // Refund transaction
            $originalPayment->update([
                'status' => 'refunded',
                'refunded_at' => $order->cancelled_at ?? now(),
                'raw_response' => [
                    'status' => true,
                    'message' => 'Refund successful',
                    'data' => [
                        'refund_id' => 'rf_' . strtoupper(uniqid()),
                        'amount' => $order->total_amount * 100,
                        'status' => 'refunded'
                    ]
                ]
            ]);
        }

        // Create some failed payment attempts
        $unpaidOrders = Order::where('payment_status', 'unpaid')->with('customer')->limit(5)->get();

        foreach ($unpaidOrders as $order) {
            $errorCodes = ['card_declined', 'insufficient_funds', 'expired_card', 'invalid_cvv', 'bank_timeout'];
            $errorMessages = [
                'card_declined' => 'Your card was declined by the issuing bank',
                'insufficient_funds' => 'Insufficient funds in account',
                'expired_card' => 'The card has expired',
                'invalid_cvv' => 'Invalid CVV/security code provided',
                'bank_timeout' => 'Bank connection timeout, please try again'
            ];

            $errorCode = fake()->randomElement($errorCodes);

            PaymentTransaction::create([
                'order_id' => $order->id,
                'user_id' => $order->customer_id,
                'provider' => fake()->randomElement(['paystack', 'flutterwave']),
                'provider_payment_id' => 'pi_' . strtoupper(uniqid()),
                'amount' => $order->total_amount,
                'currency' => $order->currency,
                'status' => 'failed',
                'error_code' => $errorCode,
                'error_message' => $errorMessages[$errorCode],
                'raw_request' => [
                    'amount' => $order->total_amount * 100,
                    'currency' => strtolower($order->currency),
                    'email' => $order->customer->email
                ],
                'raw_response' => [
                    'status' => false,
                    'message' => $errorMessages[$errorCode],
                    'data' => null
                ],
                'created_at' => $order->created_at->addMinutes(5)
            ]);
        }

        // Create some pending payments
        $pendingOrders = Order::where('payment_status', 'unpaid')->with('customer')->skip(5)->limit(3)->get();

        foreach ($pendingOrders as $order) {
            PaymentTransaction::create([
                'order_id' => $order->id,
                'user_id' => $order->customer_id,
                'provider' => 'paystack',
                'provider_payment_id' => 'pi_' . strtoupper(uniqid()),
                'amount' => $order->total_amount,
                'currency' => $order->currency,
                'status' => 'pending',
                'raw_request' => [
                    'amount' => $order->total_amount * 100,
                    'currency' => strtolower($order->currency),
                    'email' => $order->customer->email
                ],
                'created_at' => now()->subMinutes(rand(5, 30))
            ]);
        }
    }
}