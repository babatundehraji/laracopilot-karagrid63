<?php

namespace App\Http\Controllers\Api\Orders;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\Api\ProposeOrderEditRequest;
use App\Models\Order;
use App\Models\OrderEdit;
use App\Models\Vendor;
use App\Services\ActivityLogger;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VendorOrderController extends BaseController
{
    /**
     * Get all orders for authenticated vendor
     * GET /api/vendor/orders
     */
    public function index(Request $request)
    {
        try {
            $user = $request->user();

            // Get vendor profile
            $vendor = Vendor::where('user_id', $user->id)->first();

            if (!$vendor) {
                return $this->error('Vendor profile not found', 404);
            }

            $orders = Order::where('vendor_id', $vendor->id)
                ->with(['service', 'user'])
                ->orderBy('created_at', 'desc')
                ->paginate(20);

            return $this->success([
                'orders' => $orders->map(function ($order) {
                    return [
                        'id' => $order->id,
                        'order_number' => $order->order_number,
                        'service' => [
                            'id' => $order->service->id,
                            'title' => $order->service->title
                        ],
                        'customer' => [
                            'id' => $order->user->id,
                            'name' => $order->user->full_name
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
            Log::error('Failed to retrieve vendor orders', [
                'user_id' => $request->user()->id,
                'error' => $e->getMessage()
            ]);

            return $this->error('Failed to retrieve orders', 500);
        }
    }

    /**
     * Get single order details
     * GET /api/vendor/orders/{id}
     */
    public function show(Request $request, $id)
    {
        try {
            $user = $request->user();

            // Get vendor profile
            $vendor = Vendor::where('user_id', $user->id)->first();

            if (!$vendor) {
                return $this->error('Vendor profile not found', 404);
            }

            $order = Order::where('id', $id)
                ->where('vendor_id', $vendor->id)
                ->with(['service', 'user'])
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
                        'description' => $order->service->description
                    ],
                    'customer' => [
                        'id' => $order->user->id,
                        'name' => $order->user->full_name,
                        'email' => $order->user->email,
                        'phone' => $order->user->phone
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
            Log::error('Failed to retrieve vendor order details', [
                'user_id' => $request->user()->id,
                'order_id' => $id,
                'error' => $e->getMessage()
            ]);

            return $this->error('Failed to retrieve order details', 500);
        }
    }

    /**
     * Accept order
     * POST /api/vendor/orders/{id}/accept
     */
    public function accept(Request $request, $id)
    {
        try {
            $user = $request->user();

            DB::beginTransaction();

            // Get vendor profile
            $vendor = Vendor::where('user_id', $user->id)->first();

            if (!$vendor) {
                DB::rollBack();
                return $this->error('Vendor profile not found', 404);
            }

            $order = Order::where('id', $id)
                ->where('vendor_id', $vendor->id)
                ->with('user')
                ->first();

            if (!$order) {
                DB::rollBack();
                return $this->error('Order not found', 404);
            }

            if ($order->status !== 'pending') {
                DB::rollBack();
                return $this->error('Only pending orders can be accepted', 403);
            }

            $order->update(['status' => 'confirmed']);

            // Notify customer about order acceptance
            NotificationService::notifyUser(
                $order->user_id,
                'Order accepted',
                "Your order {$order->order_number} has been accepted by {$vendor->business_name}",
                'order',
                ['order_id' => $order->id],
                true // send email
            );

            DB::commit();

            ActivityLogger::log(
                $user->id,
                'order_accepted',
                "Vendor {$vendor->business_name} accepted order {$order->order_number}",
                'App\\Models\\Order',
                $order->id
            );

            Log::info('Order accepted', [
                'user_id' => $user->id,
                'vendor_id' => $vendor->id,
                'order_id' => $order->id
            ]);

            return $this->success([
                'order' => [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'status' => $order->status,
                    'updated_at' => $order->updated_at->toIso8601String()
                ]
            ], 'Order accepted successfully');
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to accept order', [
                'user_id' => $request->user()->id,
                'order_id' => $id,
                'error' => $e->getMessage()
            ]);

            return $this->error('Failed to accept order', 500);
        }
    }

    /**
     * Propose edit to order
     * POST /api/vendor/orders/{id}/propose-edit
     */
    public function proposeEdit(ProposeOrderEditRequest $request, $id)
    {
        try {
            $user = $request->user();

            DB::beginTransaction();

            // Get vendor profile
            $vendor = Vendor::where('user_id', $user->id)->first();

            if (!$vendor) {
                DB::rollBack();
                return $this->error('Vendor profile not found', 404);
            }

            $order = Order::where('id', $id)
                ->where('vendor_id', $vendor->id)
                ->first();

            if (!$order) {
                DB::rollBack();
                return $this->error('Order not found', 404);
            }

            if (!in_array($order->status, ['pending', 'confirmed'])) {
                DB::rollBack();
                return $this->error('Cannot propose edits for orders in current status', 403);
            }

            // Check if there's already a pending edit proposal
            $existingEdit = OrderEdit::where('order_id', $order->id)
                ->where('status', 'pending')
                ->first();

            if ($existingEdit) {
                DB::rollBack();
                return $this->error('There is already a pending edit proposal for this order', 409);
            }

            // Create order edit proposal
            $orderEdit = OrderEdit::create([
                'order_id' => $order->id,
                'proposed_by' => 'vendor',
                'new_price' => $request->new_price,
                'new_service_date' => $request->new_service_date,
                'new_service_time' => $request->new_service_time,
                'reason' => $request->reason,
                'status' => 'pending'
            ]);

            DB::commit();

            ActivityLogger::log(
                $user->id,
                'order_edit_proposed',
                "Vendor {$vendor->business_name} proposed edit for order {$order->order_number}",
                'App\\Models\\OrderEdit',
                $orderEdit->id
            );

            Log::info('Order edit proposed', [
                'user_id' => $user->id,
                'vendor_id' => $vendor->id,
                'order_id' => $order->id,
                'order_edit_id' => $orderEdit->id
            ]);

            return $this->success([
                'order_edit' => [
                    'id' => $orderEdit->id,
                    'order_id' => $order->id,
                    'new_price' => $orderEdit->new_price,
                    'new_service_date' => $orderEdit->new_service_date,
                    'new_service_time' => $orderEdit->new_service_time,
                    'reason' => $orderEdit->reason,
                    'status' => $orderEdit->status,
                    'created_at' => $orderEdit->created_at->toIso8601String()
                ]
            ], 'Edit proposal submitted successfully', 201);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to propose order edit', [
                'user_id' => $request->user()->id,
                'order_id' => $id,
                'error' => $e->getMessage()
            ]);

            return $this->error('Failed to propose edit', 500);
        }
    }
}