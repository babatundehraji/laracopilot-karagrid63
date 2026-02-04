<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\AddToCartRequest;
use App\Models\Cart;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CartController extends BaseController
{
    /**
     * Get all cart items for authenticated user
     * GET /api/cart
     */
    public function index(Request $request)
    {
        try {
            $user = $request->user();

            $cartItems = Cart::where('user_id', $user->id)
                ->with(['service.vendor.user'])
                ->orderBy('created_at', 'desc')
                ->get();

            return $this->success([
                'cart_items' => $cartItems->map(function ($item) {
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
                'total_items' => $cartItems->count()
            ], 'Cart retrieved successfully');
        } catch (\Exception $e) {
            Log::error('Failed to retrieve cart', [
                'user_id' => $request->user()->id,
                'error' => $e->getMessage()
            ]);

            return $this->error('Failed to retrieve cart', 500);
        }
    }

    /**
     * Add service to cart
     * POST /api/cart
     */
    public function store(AddToCartRequest $request)
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
                return $this->error('This service is not available for purchase', 403);
            }

            if ($service->status === 'inactive') {
                return $this->error('This service is currently inactive', 403);
            }

            // Check if item already exists in cart
            $existingCartItem = Cart::where('user_id', $user->id)
                ->where('service_id', $service->id)
                ->first();

            if ($existingCartItem) {
                // Item already in cart - return success with message
                return $this->success([
                    'cart_item' => [
                        'id' => $existingCartItem->id,
                        'service_id' => $service->id,
                        'added_at' => $existingCartItem->created_at->toIso8601String()
                    ]
                ], 'Service already in cart');
            }

            // Create cart item
            $cartItem = Cart::create([
                'user_id' => $user->id,
                'service_id' => $service->id
            ]);

            Log::info('Service added to cart', [
                'user_id' => $user->id,
                'service_id' => $service->id,
                'cart_item_id' => $cartItem->id
            ]);

            // Get updated cart count
            $cartCount = Cart::where('user_id', $user->id)->count();

            return $this->success([
                'cart_item' => [
                    'id' => $cartItem->id,
                    'service' => [
                        'id' => $service->id,
                        'title' => $service->title,
                        'price' => $service->price,
                        'price_type' => $service->price_type
                    ],
                    'added_at' => $cartItem->created_at->toIso8601String()
                ],
                'total_items' => $cartCount
            ], 'Service added to cart successfully', 201);
        } catch (\Exception $e) {
            Log::error('Failed to add service to cart', [
                'user_id' => $request->user()->id,
                'service_id' => $request->service_id,
                'error' => $e->getMessage()
            ]);

            return $this->error('Failed to add service to cart', 500);
        }
    }

    /**
     * Remove service from cart
     * DELETE /api/cart/{service}
     */
    public function destroy(Request $request, $serviceId)
    {
        try {
            $user = $request->user();

            // Find cart item
            $cartItem = Cart::where('user_id', $user->id)
                ->where('service_id', $serviceId)
                ->first();

            if ($cartItem) {
                $cartItem->delete();

                Log::info('Service removed from cart', [
                    'user_id' => $user->id,
                    'service_id' => $serviceId,
                    'cart_item_id' => $cartItem->id
                ]);
            }

            // Get updated cart count
            $cartCount = Cart::where('user_id', $user->id)->count();

            // Return success regardless of whether item was found
            return $this->success([
                'total_items' => $cartCount
            ], 'Service removed from cart successfully');
        } catch (\Exception $e) {
            Log::error('Failed to remove service from cart', [
                'user_id' => $request->user()->id,
                'service_id' => $serviceId,
                'error' => $e->getMessage()
            ]);

            return $this->error('Failed to remove service from cart', 500);
        }
    }
}