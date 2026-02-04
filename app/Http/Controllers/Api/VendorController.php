<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Service;
use App\Models\Transaction;
use App\Models\Vendor;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class VendorController extends Controller
{
    /**
     * Get current user's vendor profile
     * GET /api/vendor/me
     */
    public function me(Request $request)
    {
        try {
            $user = $request->user();

            // Find vendor record for current user
            $vendor = Vendor::where('user_id', $user->id)->first();

            if (!$vendor) {
                return response()->json([
                    'success' => true,
                    'message' => 'No vendor profile found',
                    'data' => null
                ], 200);
            }

            return response()->json([
                'success' => true,
                'message' => 'Vendor profile retrieved',
                'data' => [
                    'vendor' => [
                        'id' => $vendor->id,
                        'user_id' => $vendor->user_id,
                        'business_name' => $vendor->business_name,
                        'business_type' => $vendor->business_type,
                        'tax_id' => $vendor->tax_id,
                        'country_id' => $vendor->country_id,
                        'state_id' => $vendor->state_id,
                        'city_id' => $vendor->city_id,
                        'address_line1' => $vendor->address_line1,
                        'address_line2' => $vendor->address_line2,
                        'postal_code' => $vendor->postal_code,
                        'phone_code' => $vendor->phone_code,
                        'phone' => $vendor->phone,
                        'full_phone' => $vendor->full_phone,
                        'payout_method' => $vendor->payout_method,
                        'payout_currency' => $vendor->payout_currency,
                        'bank_name' => $vendor->bank_name,
                        'bank_account_name' => $vendor->bank_account_name,
                        'bank_account_number' => $vendor->bank_account_number,
                        'bank_swift_code' => $vendor->bank_swift_code,
                        'status' => $vendor->status,
                        'rating' => $vendor->rating,
                        'total_reviews' => $vendor->total_reviews,
                        'verified' => $vendor->verified,
                        'featured' => $vendor->featured,
                        'created_at' => $vendor->created_at->toIso8601String(),
                        'updated_at' => $vendor->updated_at->toIso8601String()
                    ]
                ]
            ], 200);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve vendor profile', [
                'user_id' => $request->user()->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve vendor profile',
                'data' => null
            ], 500);
        }
    }

    /**
     * Apply as vendor or update application
     * POST /api/vendor/apply
     */
    public function apply(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'business_name' => 'required|string|max:191',
            'business_type' => 'nullable|string|max:100',
            'tax_id' => 'nullable|string|max:100',
            'country_id' => 'required|exists:countries,id',
            'state_id' => 'required|exists:states,id',
            'city_id' => 'required|exists:cities,id',
            'address_line1' => 'required|string|max:255',
            'address_line2' => 'nullable|string|max:255',
            'postal_code' => 'required|string|max:20',
            'phone_code' => 'nullable|string|max:10',
            'phone' => 'nullable|string|max:20',
            'payout_method' => 'required|in:bank_transfer,paypal,stripe,mobile_money',
            'payout_currency' => 'required|string|size:3',
            'bank_name' => 'nullable|string|max:191',
            'bank_account_name' => 'nullable|string|max:191',
            'bank_account_number' => 'nullable|string|max:50',
            'bank_swift_code' => 'nullable|string|max:20'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'data' => ['errors' => $validator->errors()]
            ], 422);
        }

        try {
            $user = $request->user();

            // Check if vendor already exists
            $vendor = Vendor::where('user_id', $user->id)->first();

            $data = [
                'user_id' => $user->id,
                'business_name' => $request->business_name,
                'business_type' => $request->business_type,
                'tax_id' => $request->tax_id,
                'country_id' => $request->country_id,
                'state_id' => $request->state_id,
                'city_id' => $request->city_id,
                'address_line1' => $request->address_line1,
                'address_line2' => $request->address_line2,
                'postal_code' => $request->postal_code,
                'phone_code' => $request->phone_code,
                'phone' => $request->phone,
                'payout_method' => $request->payout_method,
                'payout_currency' => $request->payout_currency,
                'bank_name' => $request->bank_name,
                'bank_account_name' => $request->bank_account_name,
                'bank_account_number' => $request->bank_account_number,
                'bank_swift_code' => $request->bank_swift_code
            ];

            if (!$vendor) {
                // Create new vendor application with pending status
                $data['status'] = 'pending';
                $data['verified'] = false;
                $data['featured'] = false;
                $data['rating'] = 0;
                $data['total_reviews'] = 0;

                $vendor = Vendor::create($data);

                // Log activity
                ActivityLogger::log(
                    $user->id,
                    'vendor_apply',
                    "Vendor application submitted for {$vendor->business_name}",
                    'App\\Models\\Vendor',
                    $vendor->id
                );

                Log::info('Vendor application created', [
                    'user_id' => $user->id,
                    'vendor_id' => $vendor->id,
                    'business_name' => $vendor->business_name
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Vendor application submitted successfully. Your application is pending review.',
                    'data' => [
                        'vendor' => [
                            'id' => $vendor->id,
                            'business_name' => $vendor->business_name,
                            'status' => $vendor->status,
                            'created_at' => $vendor->created_at->toIso8601String()
                        ]
                    ]
                ], 201);
            } else {
                // Update existing vendor (preserve status unless it's rejected)
                // If status is approved/suspended, keep it. If pending/rejected, allow update.
                if (in_array($vendor->status, ['approved', 'suspended'])) {
                    // Don't change status for approved or suspended vendors
                    unset($data['status']);
                } else {
                    // For pending or rejected, set back to pending on re-application
                    $data['status'] = 'pending';
                }

                $vendor->update($data);

                // Log activity
                ActivityLogger::log(
                    $user->id,
                    'vendor_apply',
                    "Vendor application updated for {$vendor->business_name}",
                    'App\\Models\\Vendor',
                    $vendor->id
                );

                Log::info('Vendor application updated', [
                    'user_id' => $user->id,
                    'vendor_id' => $vendor->id,
                    'business_name' => $vendor->business_name
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Vendor application updated successfully',
                    'data' => [
                        'vendor' => [
                            'id' => $vendor->id,
                            'business_name' => $vendor->business_name,
                            'status' => $vendor->status,
                            'updated_at' => $vendor->updated_at->toIso8601String()
                        ]
                    ]
                ], 200);
            }
        } catch (\Exception $e) {
            Log::error('Failed to apply as vendor', [
                'user_id' => $request->user()->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to submit vendor application',
                'data' => null
            ], 500);
        }
    }

    /**
     * Update vendor profile
     * PUT /api/vendor/profile
     */
    public function updateProfile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'business_name' => 'nullable|string|max:191',
            'business_type' => 'nullable|string|max:100',
            'tax_id' => 'nullable|string|max:100',
            'country_id' => 'nullable|exists:countries,id',
            'state_id' => 'nullable|exists:states,id',
            'city_id' => 'nullable|exists:cities,id',
            'address_line1' => 'nullable|string|max:255',
            'address_line2' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:20',
            'phone_code' => 'nullable|string|max:10',
            'phone' => 'nullable|string|max:20',
            'payout_method' => 'nullable|in:bank_transfer,paypal,stripe,mobile_money',
            'payout_currency' => 'nullable|string|size:3',
            'bank_name' => 'nullable|string|max:191',
            'bank_account_name' => 'nullable|string|max:191',
            'bank_account_number' => 'nullable|string|max:50',
            'bank_swift_code' => 'nullable|string|max:20'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'data' => ['errors' => $validator->errors()]
            ], 422);
        }

        try {
            $user = $request->user();

            // Find vendor record
            $vendor = Vendor::where('user_id', $user->id)->first();

            if (!$vendor) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vendor profile not found. Please apply as a vendor first.',
                    'data' => null
                ], 404);
            }

            // Only update provided fields
            $data = array_filter($request->only([
                'business_name',
                'business_type',
                'tax_id',
                'country_id',
                'state_id',
                'city_id',
                'address_line1',
                'address_line2',
                'postal_code',
                'phone_code',
                'phone',
                'payout_method',
                'payout_currency',
                'bank_name',
                'bank_account_name',
                'bank_account_number',
                'bank_swift_code'
            ]), function($value) {
                return !is_null($value);
            });

            if (empty($data)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No fields provided for update',
                    'data' => null
                ], 400);
            }

            // Update vendor (status is NOT changeable here)
            $vendor->update($data);

            // Log activity
            ActivityLogger::log(
                $user->id,
                'vendor_profile_update',
                "Vendor profile updated for {$vendor->business_name}",
                'App\\Models\\Vendor',
                $vendor->id
            );

            Log::info('Vendor profile updated', [
                'user_id' => $user->id,
                'vendor_id' => $vendor->id,
                'fields_updated' => array_keys($data)
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Vendor profile updated successfully',
                'data' => [
                    'vendor' => [
                        'id' => $vendor->id,
                        'business_name' => $vendor->business_name,
                        'business_type' => $vendor->business_type,
                        'status' => $vendor->status,
                        'payout_method' => $vendor->payout_method,
                        'payout_currency' => $vendor->payout_currency,
                        'updated_at' => $vendor->updated_at->toIso8601String()
                    ]
                ]
            ], 200);
        } catch (\Exception $e) {
            Log::error('Failed to update vendor profile', [
                'user_id' => $request->user()->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update vendor profile',
                'data' => null
            ], 500);
        }
    }

    /**
     * Get vendor dashboard summary
     * GET /api/vendor/dashboard
     */
    public function dashboard(Request $request)
    {
        try {
            $user = $request->user();

            // Find vendor record
            $vendor = Vendor::where('user_id', $user->id)->first();

            if (!$vendor) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vendor profile not found',
                    'data' => null
                ], 404);
            }

            // Count services
            $totalServices = Service::where('vendor_id', $vendor->id)->count();

            // Count orders where service belongs to this vendor
            $totalOrders = Order::whereHas('service', function($query) use ($vendor) {
                $query->where('vendor_id', $vendor->id);
            })->count();

            $activeOrders = Order::whereHas('service', function($query) use ($vendor) {
                $query->where('vendor_id', $vendor->id);
            })
            ->whereIn('status', ['pending', 'confirmed', 'in_progress'])
            ->count();

            $completedOrders = Order::whereHas('service', function($query) use ($vendor) {
                $query->where('vendor_id', $vendor->id);
            })
            ->where('status', 'completed')
            ->count();

            // Calculate total earnings (transactions where user_id is vendor's user and category is 'earning')
            $totalEarnings = Transaction::where('user_id', $user->id)
                ->where('category', 'earning')
                ->sum('amount');

            Log::info('Vendor dashboard accessed', [
                'user_id' => $user->id,
                'vendor_id' => $vendor->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Vendor dashboard data retrieved',
                'data' => [
                    'vendor' => [
                        'id' => $vendor->id,
                        'business_name' => $vendor->business_name,
                        'status' => $vendor->status,
                        'rating' => $vendor->rating,
                        'total_reviews' => $vendor->total_reviews
                    ],
                    'summary' => [
                        'total_services' => $totalServices,
                        'total_orders' => $totalOrders,
                        'active_orders' => $activeOrders,
                        'completed_orders' => $completedOrders,
                        'total_earnings' => round($totalEarnings, 2),
                        'currency' => $vendor->payout_currency ?? 'NGN'
                    ]
                ]
            ], 200);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve vendor dashboard', [
                'user_id' => $request->user()->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve dashboard data',
                'data' => null
            ], 500);
        }
    }
}