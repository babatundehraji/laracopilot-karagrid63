<?php

namespace App\Http\Controllers\Api\Orders;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\Api\CreateOrderRequest;
use App\Http\Requests\Api\RespondOrderEditRequest;
use App\Models\Order;
use App\Models\Service;
use App\Models\Vendor;
use App\Services\ActivityLogger;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CustomerOrderController extends BaseController
{
    /**
     * Get all orders for authenticated customer
     * GET /api/orders
     */
    public function index(Request $request)
    {
        try {
            $user = $request->user();

            $orders = Order::where('user_id', $user->id)
                ->with(['service', 'vendor'])
                ->orderBy('created_at', 'desc')
                ->paginate(20);

            return $this->success([
                'orders' => $orders->map(function ($order) {
                    return [
                        'id' => $order->id,
                        'order_number' => $order->order_number,
                        'service' => [
                            'id' => $order->service->id,
                            'title' => $order->service->title,
                            'price' => $order->service->price
                        ],
                        'vendor' => [
                            'id' => $order->vendor->id,
                            'business_name' => $order->vendor->business_name
                        ],
                        'price' => $order->price,
                        'status' => $order->status,
                        'service_date' => $order->service_date,
                        'created_at' => $order->created_at->toIso8601String()
                    ];
                }),
                'pagination' => [
                    'current_page' => $orders->currentPage(),
                    'per_page' => $orders->perPage(),
                    'total' => $orders->total(),
                    'last_page' => $orders->lastPage()
                ]
            ], 'Orders retrieved successfully');
        } catch (\Exception $e) {
            Log::error('Failed to retrieve customer orders', [
                'user_id' => $request->user()->id,
                'error' => $e->getMessage()
            ]);

            return $this->error('Failed to retrieve orders', 500);
        }
    }

    /**
     * Get single order details
     * GET /api/orders/{id}
     */
    public function show(Request $request, $id)
    {
        try {
            $user = $request->user();

            $order = Order::where('id', $id)
                ->where('user_id', $user->id)
                ->with(['service', 'vendor.user'])
                ->first();

            if (!$order) {
                return $this->error('Order not found', 404);
            }

            return $this->success([
                'order' => [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'service' => [
                        'id' => $order->service->id,
                        'title' => $order->service->title,
                        'description' => $order->service->description,
                        'price' => $order->service->price
                    ],
                    'vendor' => [
                        'id' => $order->vendor->id,
                        'business_name' => $order->vendor->business_name,
                        'contact_email' => $order->vendor->user->email,
                        'contact_phone' => $order->vendor->user->phone
                    ],
                    'price' => $order->price,
                    'status' => $order->status,
                    'service_date' => $order->service_date,
                    'service_time' => $order->service_time,
                    'location' => $order->location,
                    'notes' => $order->notes,
                    'payment_status' => $order->payment_status,
                    'created_at' => $order->created_at->toIso8601String(),
                    'updated_at' => $order->updated_at->toIso8601String()
                ]
            ], 'Order details retrieved successfully');
        } catch (\Exception $e) {
            Log::error('Failed to retrieve order details', [
                'user_id' => $request->user()->id,
                'order_id' => $id,
                'error' => $e->getMessage()
            ]);

            return $this->error('Failed to retrieve order details', 500);
        }
    }

    /**
     * Create new order
     * POST /api/orders
     */
    public function store(CreateOrderRequest $request)
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
                'payment_status' => 'pending'
            ]);

            // Notify vendor about new order
            NotificationService::notifyUser(
                $service->vendor->user_id,
                'New order received',
                "You have a new order from {$user->full_name}",
                'order',
                ['order_id' => $order->id],
                true // send email
            );

            DB::commit();

            // Log activity
            ActivityLogger::log(
                $user->id,
                'order_created',
                "Customer {$user->full_name} created order {$orderNumber} for service {$service->title}",
                'App\\Models\\Order',
                $order->id
            );

            Log::info('Order created', [
                'user_id' => $user->id,
                'order_id' => $order->id,
                'order_number' => $orderNumber,
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
                    'service_date' => $order->service_date,
                    'service_time' => $order->service_time,
                    'created_at' => $order->created_at->toIso8601String()
                ]
            ], 'Order created successfully', 201);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to create order', [
                'user_id' => $request->user()->id,
                'service_id' => $request->service_id,
                'error' => $e->getMessage()
            ]);

            return $this->error('Failed to create order', 500);
        }
    }

    /**
     * Respond to vendor's edit proposal
     * POST /api/orders/{id}/respond-edit
     */
    public function respondEdit(RespondOrderEditRequest $request, $id)
    {
        try {
            $user = $request->user();

            DB::beginTransaction();

            $order = Order::where('id', $id)
                ->where('user_id', $user->id)
                ->with('orderEdit')
                ->first();

            if (!$order) {
                DB::rollBack();
                return $this->error('Order not found', 404);
            }

            if (!$order->orderEdit) {
                DB::rollBack();
                return $this->error('No edit proposal found for this order', 404);
            }

            if ($order->orderEdit->status !== 'pending') {
                DB::rollBack();
                return $this->error('Edit proposal has already been responded to', 409);
            }

            $response = $request->response; // 'accepted' or 'rejected'

            // Update order edit status
            $order->orderEdit->update([
                'status' => $response,
                'customer_response_at' => now()
            ]);

            if ($response === 'accepted') {
                // Apply changes to order
                $order->update([
                    'price' => $order->orderEdit->new_price ?? $order->price,
                    'service_date' => $order->orderEdit->new_service_date ?? $order->service_date,
                    'service_time' => $order->orderEdit->new_service_time ?? $order->service_time,
                    'status' => 'confirmed'
                ]);

                $message = 'Edit proposal accepted and order updated';
            } else {
                $message = 'Edit proposal rejected';
            }

            DB::commit();

            ActivityLogger::log(
                $user->id,
                'order_edit_responded',
                "Customer {$user->full_name} {$response} edit proposal for order {$order->order_number}",
                'App\\Models\\Order',
                $order->id
            );

            Log::info('Order edit responded', [
                'user_id' => $user->id,
                'order_id' => $order->id,
                'response' => $response
            ]);

            return $this->success([
                'order' => [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'price' => $order->price,
                    'status' => $order->status,
                    'service_date' => $order->service_date,
                    'service_time' => $order->service_time
                ]
            ], $message);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to respond to order edit', [
                'user_id' => $request->user()->id,
                'order_id' => $id,
                'error' => $e->getMessage()
            ]);

            return $this->error('Failed to respond to edit proposal', 500);
        }
    }

    /**
     * Mark order as completed
     * POST /api/orders/{id}/complete
     */
    public function complete(Request $request, $id)
    {
        try {
            $user = $request->user();

            DB::beginTransaction();

            $order = Order::where('id', $id)
                ->where('user_id', $user->id)
                ->first();

            if (!$order) {
                DB::rollBack();
                return $this->error('Order not found', 404);
            }

            if ($order->status !== 'confirmed') {
                DB::rollBack();
                return $this->error('Only confirmed orders can be marked as completed', 403);
            }

            $order->update([
                'status' => 'completed',
                'completed_at' => now()
            ]);

            DB::commit();

            ActivityLogger::log(
                $user->id,
                'order_completed',
                "Customer {$user->full_name} marked order {$order->order_number} as completed",
                'App\\Models\\Order',
                $order->id
            );

            Log::info('Order completed', [
                'user_id' => $user->id,
                'order_id' => $order->id
            ]);

            return $this->success([
                'order' => [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'status' => $order->status,
                    'completed_at' => $order->completed_at->toIso8601String()
                ]
            ], 'Order marked as completed');
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to complete order', [
                'user_id' => $request->user()->id,
                'order_id' => $id,
                'error' => $e->getMessage()
            ]);

            return $this->error('Failed to complete order', 500);
        }
    }
}