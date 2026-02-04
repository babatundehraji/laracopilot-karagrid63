<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\AddToWishlistRequest;
use App\Models\Service;
use App\Models\Wishlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WishlistController extends BaseController
{
    /**
     * Get all wishlist items for authenticated user
     * GET /api/wishlist
     */
    public function index(Request $request)
    {
        try {
            $user = $request->user();

            $wishlistItems = Wishlist::where('user_id', $user->id)
                ->with(['service.vendor.user'])
                ->orderBy('created_at', 'desc')
                ->get();

            return $this->success([
                'wishlist_items' => $wishlistItems->map(function ($item) {
                    $service = $item->service;
                    
                    if (!$service) {
                        return null;
                    }

                    return [
                        'id' => $item->id,
                        'service' => [
                            'id' => $service->id,
                            'title' => $service->title,
                            'price' => $service->price,
                            'price_type' => $service->price_type,
                            'status' => $service->status,
                            'rating' => $service->rating,
                            'main_image_url' => $service->image_urls && count($service->image_urls) > 0 
                                ? $service->image_urls[0] 
                                : null,
                            'vendor' => [
                                'id' => $service->vendor->id,
                                'business_name' => $service->vendor->business_name,
                                'rating' => $service->vendor->rating,
                                'verified' => $service->vendor->verified
                            ]
                        ],
                        'added_at' => $item->created_at->toIso8601String()
                    ];
                })->filter()->values(),
                'total_items' => $wishlistItems->count()
            ], 'Wishlist retrieved successfully');
        } catch (\Exception $e) {
            Log::error('Failed to retrieve wishlist', [
                'user_id' => $request->user()->id,
                'error' => $e->getMessage()
            ]);

            return $this->error('Failed to retrieve wishlist', 500);
        }
    }

    /**
     * Add service to wishlist
     * POST /api/wishlist
     */
    public function store(AddToWishlistRequest $request)
    {
        try {
            $user = $request->user();

            // Check if service exists
            $service = Service::find($request->service_id);

            if (!$service) {
                return $this->error('Service not found', 404);
            }

            // Check if service is approved and active
            if ($service->status !== 'approved') {
                return $this->error('This service is not available', 403);
            }

            if ($service->status === 'inactive') {
                return $this->error('This service is currently inactive', 403);
            }

            // Check if item already exists in wishlist
            $existingWishlistItem = Wishlist::where('user_id', $user->id)
                ->where('service_id', $service->id)
                ->first();

            if ($existingWishlistItem) {
                // Item already in wishlist - return success with message
                return $this->success([
                    'wishlist_item' => [
                        'id' => $existingWishlistItem->id,
                        'service_id' => $service->id,
                        'added_at' => $existingWishlistItem->created_at->toIso8601String()
                    ]
                ], 'Service already in wishlist');
            }

            // Create wishlist item
            $wishlistItem = Wishlist::create([
                'user_id' => $user->id,
                'service_id' => $service->id
            ]);

            Log::info('Service added to wishlist', [
                'user_id' => $user->id,
                'service_id' => $service->id,
                'wishlist_item_id' => $wishlistItem->id
            ]);

            // Get updated wishlist count
            $wishlistCount = Wishlist::where('user_id', $user->id)->count();

            return $this->success([
                'wishlist_item' => [
                    'id' => $wishlistItem->id,
                    'service' => [
                        'id' => $service->id,
                        'title' => $service->title,
                        'price' => $service->price,
                        'price_type' => $service->price_type
                    ],
                    'added_at' => $wishlistItem->created_at->toIso8601String()
                ],
                'total_items' => $wishlistCount
            ], 'Service added to wishlist successfully', 201);
        } catch (\Exception $e) {
            Log::error('Failed to add service to wishlist', [
                'user_id' => $request->user()->id,
                'service_id' => $request->service_id,
                'error' => $e->getMessage()
            ]);

            return $this->error('Failed to add service to wishlist', 500);
        }
    }

    /**
     * Remove service from wishlist
     * DELETE /api/wishlist/{service}
     */
    public function destroy(Request $request, $serviceId)
    {
        try {
            $user = $request->user();

            // Find wishlist item
            $wishlistItem = Wishlist::where('user_id', $user->id)
                ->where('service_id', $serviceId)
                ->first();

            if ($wishlistItem) {
                $wishlistItem->delete();

                Log::info('Service removed from wishlist', [
                    'user_id' => $user->id,
                    'service_id' => $serviceId,
                    'wishlist_item_id' => $wishlistItem->id
                ]);
            }

            // Get updated wishlist count
            $wishlistCount = Wishlist::where('user_id', $user->id)->count();

            // Return success regardless of whether item was found
            return $this->success([
                'total_items' => $wishlistCount
            ], 'Service removed from wishlist successfully');
        } catch (\Exception $e) {
            Log::error('Failed to remove service from wishlist', [
                'user_id' => $request->user()->id,
                'service_id' => $serviceId,
                'error' => $e->getMessage()
            ]);

            return $this->error('Failed to remove service from wishlist', 500);
        }
    }
}