<?php

namespace App\Http\Controllers\Api\Disputes;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\Api\OpenDisputeRequest;
use App\Models\Dispute;
use App\Models\Order;
use App\Services\ActivityLogger;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CustomerDisputeController extends BaseController
{
    /**
     * Open dispute for an order
     * POST /api/orders/{order}/disputes
     */
    public function openDispute(OpenDisputeRequest $request, $orderId)
    {
        try {
            $user = $request->user();

            DB::beginTransaction();

            // Find order
            $order = Order::with(['vendor.user'])->find($orderId);

            if (!$order) {
                DB::rollBack();
                return $this->error('Order not found', 404);
            }

            // Check if order belongs to customer
            if ($order->user_id !== $user->id) {
                DB::rollBack();
                return $this->error('You are not authorized to dispute this order', 403);
            }

            // Check if order is already disputed
            $existingDispute = Dispute::where('order_id', $order->id)->first();
            if ($existingDispute) {
                DB::rollBack();
                return $this->error('A dispute has already been opened for this order', 409);
            }

            // Check order status - only allow disputes for confirmed or completed orders
            if (!in_array($order->status, ['confirmed', 'completed'])) {
                DB::rollBack();
                return $this->error('Disputes can only be opened for confirmed or completed orders', 403);
            }

            // Check if within allowed dispute period (21 days from order creation)
            $disputeDeadline = $order->created_at->copy()->addDays(21);
            if (now()->isAfter($disputeDeadline)) {
                DB::rollBack();
                return $this->error('Dispute period has expired. Disputes must be opened within 21 days of order creation', 403);
            }

            // Generate dispute number
            $disputeNumber = 'DSP-' . strtoupper(uniqid());

            // Create dispute
            $dispute = Dispute::create([
                'dispute_number' => $disputeNumber,
                'order_id' => $order->id,
                'customer_id' => $user->id,
                'vendor_id' => $order->vendor_id,
                'reason' => $request->reason,
                'reason_code' => $request->reason_code,
                'status' => 'pending'
            ]);

            // Update order status to disputed
            $order->update(['status' => 'disputed']);

            // Notify vendor about dispute
            NotificationService::notifyUser(
                $order->vendor->user_id,
                'Dispute opened',
                "A dispute has been opened for order {$order->order_number}",
                'dispute',
                [
                    'order_id' => $order->id,
                    'dispute_id' => $dispute->id
                ],
                true // send email
            );

            DB::commit();

            // Log activity
            ActivityLogger::log(
                $user->id,
                'dispute_opened',
                "Customer {$user->full_name} opened dispute {$disputeNumber} for order {$order->order_number}",
                'App\\Models\\Dispute',
                $dispute->id
            );

            Log::info('Dispute opened', [
                'user_id' => $user->id,
                'order_id' => $order->id,
                'dispute_id' => $dispute->id,
                'dispute_number' => $disputeNumber
            ]);

            return $this->success([
                'dispute' => [
                    'id' => $dispute->id,
                    'dispute_number' => $dispute->dispute_number,
                    'order' => [
                        'id' => $order->id,
                        'order_number' => $order->order_number
                    ],
                    'reason' => $dispute->reason,
                    'reason_code' => $dispute->reason_code,
                    'status' => $dispute->status,
                    'created_at' => $dispute->created_at->toIso8601String()
                ]
            ], 'Dispute opened successfully', 201);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to open dispute', [
                'user_id' => $request->user()->id,
                'order_id' => $orderId,
                'error' => $e->getMessage()
            ]);

            return $this->error('Failed to open dispute', 500);
        }
    }

    /**
     * Get all disputes for authenticated customer
     * GET /api/disputes
     */
    public function index(Request $request)
    {
        try {
            $user = $request->user();

            $disputes = Dispute::where('customer_id', $user->id)
                ->with(['order', 'vendor'])
                ->orderBy('created_at', 'desc')
                ->paginate(20);

            return $this->success([
                'disputes' => $disputes->map(function ($dispute) {
                    return [
                        'id' => $dispute->id,
                        'dispute_number' => $dispute->dispute_number,
                        'order' => [
                            'id' => $dispute->order->id,
                            'order_number' => $dispute->order->order_number,
                            'status' => $dispute->order->status
                        ],
                        'vendor' => [
                            'id' => $dispute->vendor->id,
                            'business_name' => $dispute->vendor->business_name
                        ],
                        'reason_code' => $dispute->reason_code,
                        'status' => $dispute->status,
                        'created_at' => $dispute->created_at->toIso8601String(),
                        'updated_at' => $dispute->updated_at->toIso8601String()
                    ];
                }),
                'pagination' => [
                    'current_page' => $disputes->currentPage(),
                    'per_page' => $disputes->perPage(),
                    'total' => $disputes->total(),
                    'last_page' => $disputes->lastPage()
                ]
            ], 'Disputes retrieved successfully');
        } catch (\Exception $e) {
            Log::error('Failed to retrieve customer disputes', [
                'user_id' => $request->user()->id,
                'error' => $e->getMessage()
            ]);

            return $this->error('Failed to retrieve disputes', 500);
        }
    }

    /**
     * Get single dispute details
     * GET /api/disputes/{id}
     */
    public function show(Request $request, $id)
    {
        try {
            $user = $request->user();

            $dispute = Dispute::where('id', $id)
                ->where('customer_id', $user->id)
                ->with(['order.service', 'customer', 'vendor'])
                ->first();

            if (!$dispute) {
                return $this->error('Dispute not found', 404);
            }

            return $this->success([
                'dispute' => [
                    'id' => $dispute->id,
                    'dispute_number' => $dispute->dispute_number,
                    'order' => [
                        'id' => $dispute->order->id,
                        'order_number' => $dispute->order->order_number,
                        'price' => $dispute->order->price,
                        'status' => $dispute->order->status,
                        'service' => [
                            'id' => $dispute->order->service->id,
                            'title' => $dispute->order->service->title
                        ]
                    ],
                    'customer' => [
                        'id' => $dispute->customer->id,
                        'name' => $dispute->customer->full_name,
                        'email' => $dispute->customer->email
                    ],
                    'vendor' => [
                        'id' => $dispute->vendor->id,
                        'business_name' => $dispute->vendor->business_name
                    ],
                    'reason' => $dispute->reason,
                    'reason_code' => $dispute->reason_code,
                    'status' => $dispute->status,
                    'resolution' => $dispute->resolution,
                    'resolved_at' => $dispute->resolved_at ? $dispute->resolved_at->toIso8601String() : null,
                    'created_at' => $dispute->created_at->toIso8601String(),
                    'updated_at' => $dispute->updated_at->toIso8601String()
                ]
            ], 'Dispute details retrieved successfully');
        } catch (\Exception $e) {
            Log::error('Failed to retrieve dispute details', [
                'user_id' => $request->user()->id,
                'dispute_id' => $id,
                'error' => $e->getMessage()
            ]);

            return $this->error('Failed to retrieve dispute details', 500);
        }
    }
}