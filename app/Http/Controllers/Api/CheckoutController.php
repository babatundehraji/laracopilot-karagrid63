<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\CheckoutDirectRequest;
use App\Http\Requests\Api\CheckoutFromCartRequest;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Service;
use App\Services\ActivityLogger;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CheckoutController extends BaseController
{
    /**
     * Checkout from cart (multiple services)
     * POST /api/checkout/from-cart
     */
    public function fromCart(CheckoutFromCartRequest $request)
    {
        try {
            $user = $request->user();

            DB::beginTransaction();

            // Get cart items for user
            $cartItems = Cart::where('user_id', $user->id)
                ->with(['service.vendor.user'])
                ->get();

            if ($cartItems->isEmpty()) {
                DB::rollBack();
                return $this->error('Cart is empty', 400);
            }

            $orders = [];
            $totalAmount = 0;

            // Create order for each cart item
            foreach ($cartItems as $cartItem) {
                $service = $cartItem->service;

                if (!$service->is_active) {
                    continue; // Skip inactive services
                }

                $orderNumber = 'ORD-' . strtoupper(uniqid());

                $order = Order::create([
                    'order_number' => $orderNumber,
                    'user_id' => $user->id,
                    'service_id' => $service->id,
                    'vendor_id' => $service->vendor_id,
                    'price' => $service->price,
                    'status' => 'pending',
                    'service_date' => $request->service_date,
                    'service_time' => $request->service_time,
                    'location' => $request->location,
                    'notes' => $request->notes,
                    'payment_status' => 'paid'
                ]);

                $orders[] = $order;
                $totalAmount += $service->price;

                // Notify vendor about new order
                NotificationService::notifyUser(
                    $service->vendor->user_id,
                    'New order received',
                    "You have a new order from {$user->full_name}",
                    'order',
                    ['order_id' => $order->id],
                    true
                );

                // Notify vendor about payment
                NotificationService::notifyUser(
                    $service->vendor->user_id,
                    'Payment successful',
                    "Payment received for order {$order->order_number} from {$user->full_name}",
                    'payment',
                    ['order_id' => $order->id],
                    true
                );
            }

            // Clear cart
            Cart::where('user_id', $user->id)->delete();

            // Notify customer about payment
            foreach ($orders as $order) {
                NotificationService::notifyUser(
                    $user->id,
                    'Payment successful',
                    "Your payment for order {$order->order_number} was successful",
                    'payment',
                    ['order_id' => $order->id],
                    true
                );
            }

            DB::commit();

            // Log activity
            ActivityLogger::log(
                $user->id,
                'checkout_completed',
                "Customer {$user->full_name} completed checkout for {$cartItems->count()} services (Total: \${$totalAmount})",
                'App\\Models\\Order',
                $orders[0]->id ?? null
            );

            Log::info('Checkout from cart completed', [
                'user_id' => $user->id,
                'orders_count' => count($orders),
                'total_amount' => $totalAmount
            ]);

            return $this->success([
                'orders' => array_map(function ($order) {
                    return [
                        'id' => $order->id,
                        'order_number' => $order->order_number,
                        'price' => $order->price,
                        'status' => $order->status,
                        'payment_status' => $order->payment_status
                    ];
                }, $orders),
                'total_amount' => $totalAmount
            ], 'Checkout completed successfully', 201);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to checkout from cart', [
                'user_id' => $request->user()->id,
                'error' => $e->getMessage()
            ]);

            return $this->error('Failed to complete checkout', 500);
        }
    }

    /**
     * Direct checkout (single service)
     * POST /api/checkout/direct
     */
    public function direct(CheckoutDirectRequest $request)
    {
        try {
            $user = $request->user();

            DB::beginTransaction();

            // Find service
            $service = Service::with('vendor.user')->find($request->service_id);

            if (!$service) {
                DB::rollBack();
                return $this->error('Service not found', 404);
            }

            if (!$service->is_active) {
                DB::rollBack();
                return $this->error('Service is not available', 403);
            }

            // Generate order number
            $orderNumber = 'ORD-' . strtoupper(uniqid());

            // Create order
            $order = Order::create([
                'order_number' => $orderNumber,
                'user_id' => $user->id,
                'service_id' => $service->id,
                'vendor_id' => $service->vendor_id,
                'price' => $service->price,
                'status' => 'pending',
                'service_date' => $request->service_date,
                'service_time' => $request->service_time,
                'location' => $request->location,
                'notes' => $request->notes,
                'payment_status' => 'paid'
            ]);

            // Notify vendor about new order
            NotificationService::notifyUser(
                $service->vendor->user_id,
                'New order received',
                "You have a new order from {$user->full_name}",
                'order',
                ['order_id' => $order->id],
                true
            );

            // Notify customer about payment
            NotificationService::notifyUser(
                $user->id,
                'Payment successful',
                "Your payment for order {$order->order_number} was successful",
                'payment',
                ['order_id' => $order->id],
                true
            );

            // Notify vendor about payment
            NotificationService::notifyUser(
                $service->vendor->user_id,
                'Payment successful',
                "Payment received for order {$order->order_number} from {$user->full_name}",
                'payment',
                ['order_id' => $order->id],
                true
            );

            DB::commit();

            // Log activity
            ActivityLogger::log(
                $user->id,
                'checkout_completed',
                "Customer {$user->full_name} completed direct checkout for service {$service->title} (Order: {$orderNumber})",
                'App\\Models\\Order',
                $order->id
            );

            Log::info('Direct checkout completed', [
                'user_id' => $user->id,
                'order_id' => $order->id,
                'service_id' => $service->id
            ]);

            return $this->success([
                'order' => [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'service' => [
                        'id' => $service->id,
                        'title' => $service->title
                    ],
                    'price' => $order->price,
                    'status' => $order->status,
                    'payment_status' => $order->payment_status,
                    'created_at' => $order->created_at->toIso8601String()
                ]
            ], 'Checkout completed successfully', 201);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to complete direct checkout', [
                'user_id' => $request->user()->id,
                'service_id' => $request->service_id,
                'error' => $e->getMessage()
            ]);

            return $this->error('Failed to complete checkout', 500);
        }
    }
}