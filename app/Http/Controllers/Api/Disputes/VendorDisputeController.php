<?php

namespace App\Http\Controllers\Api\Disputes;

use App\Http\Controllers\Api\BaseController;
use App\Models\Dispute;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class VendorDisputeController extends BaseController
{
    /**
     * Get all disputes for authenticated vendor
     * GET /api/vendor/disputes
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

            $disputes = Dispute::where('vendor_id', $vendor->id)
                ->with(['order', 'customer'])
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
                        'customer' => [
                            'id' => $dispute->customer->id,
                            'name' => $dispute->customer->full_name
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
            Log::error('Failed to retrieve vendor disputes', [
                'user_id' => $request->user()->id,
                'error' => $e->getMessage()
            ]);

            return $this->error('Failed to retrieve disputes', 500);
        }
    }

    /**
     * Get single dispute details
     * GET /api/vendor/disputes/{id}
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

            $dispute = Dispute::where('id', $id)
                ->where('vendor_id', $vendor->id)
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
                        'email' => $dispute->customer->email,
                        'phone' => $dispute->customer->phone
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
            Log::error('Failed to retrieve vendor dispute details', [
                'user_id' => $request->user()->id,
                'dispute_id' => $id,
                'error' => $e->getMessage()
            ]);

            return $this->error('Failed to retrieve dispute details', 500);
        }
    }
}