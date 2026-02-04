<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\SendMessageRequest;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Order;
use App\Models\Vendor;
use App\Services\ActivityLogger;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderConversationController extends BaseController
{
    /**
     * Check if authenticated user is authorized for this order
     * Returns: ['authorized' => bool, 'is_customer' => bool, 'is_vendor' => bool, 'vendor_id' => int|null]
     */
    private function checkOrderAuthorization(Order $order, $userId)
    {
        // Check if user is customer
        $isCustomer = $order->user_id === $userId;

        // Check if user is vendor
        $vendor = Vendor::where('user_id', $userId)->first();
        $isVendor = $vendor && $order->vendor_id === $vendor->id;

        return [
            'authorized' => $isCustomer || $isVendor,
            'is_customer' => $isCustomer,
            'is_vendor' => $isVendor,
            'vendor_id' => $vendor ? $vendor->id : null
        ];
    }

    /**
     * Get or create conversation for order
     */
    private function getOrCreateConversation(Order $order)
    {
        $conversation = Conversation::where('order_id', $order->id)->first();

        if (!$conversation) {
            $conversation = Conversation::create([
                'order_id' => $order->id,
                'customer_id' => $order->user_id,
                'vendor_id' => $order->vendor_id
            ]);
        }

        return $conversation;
    }

    /**
     * Get conversation for order
     * GET /api/orders/{order}/conversation
     */
    public function conversation(Request $request, $orderId)
    {
        try {
            $user = $request->user();

            // Find order
            $order = Order::with(['user', 'vendor.user'])->find($orderId);

            if (!$order) {
                return $this->error('Order not found', 404);
            }

            // Check authorization
            $auth = $this->checkOrderAuthorization($order, $user->id);

            if (!$auth['authorized']) {
                return $this->error('You are not authorized to access this conversation', 403);
            }

            // Get or create conversation
            $conversation = $this->getOrCreateConversation($order);

            // Get last 20 messages
            $messages = Message::where('conversation_id', $conversation->id)
                ->with('sender')
                ->orderBy('created_at', 'desc')
                ->limit(20)
                ->get();

            return $this->success([
                'conversation' => [
                    'id' => $conversation->id,
                    'order' => [
                        'id' => $order->id,
                        'order_number' => $order->order_number,
                        'status' => $order->status
                    ],
                    'customer' => [
                        'id' => $order->user->id,
                        'name' => $order->user->full_name
                    ],
                    'vendor' => [
                        'id' => $order->vendor->id,
                        'business_name' => $order->vendor->business_name
                    ],
                    'created_at' => $conversation->created_at->toIso8601String(),
                    'updated_at' => $conversation->updated_at->toIso8601String()
                ],
                'messages' => $messages->reverse()->values()->map(function ($message) {
                    return [
                        'id' => $message->id,
                        'sender' => [
                            'id' => $message->sender->id,
                            'name' => $message->sender->full_name
                        ],
                        'message' => $message->message,
                        'is_read' => $message->is_read,
                        'created_at' => $message->created_at->toIso8601String()
                    ];
                })
            ], 'Conversation retrieved successfully');
        } catch (\Exception $e) {
            Log::error('Failed to retrieve conversation', [
                'user_id' => $request->user()->id,
                'order_id' => $orderId,
                'error' => $e->getMessage()
            ]);

            return $this->error('Failed to retrieve conversation', 500);
        }
    }

    /**
     * Get paginated messages for order
     * GET /api/orders/{order}/messages
     */
    public function messages(Request $request, $orderId)
    {
        try {
            $user = $request->user();

            // Find order
            $order = Order::find($orderId);

            if (!$order) {
                return $this->error('Order not found', 404);
            }

            // Check authorization
            $auth = $this->checkOrderAuthorization($order, $user->id);

            if (!$auth['authorized']) {
                return $this->error('You are not authorized to access these messages', 403);
            }

            // Get or create conversation
            $conversation = $this->getOrCreateConversation($order);

            // Get paginated messages
            $messages = Message::where('conversation_id', $conversation->id)
                ->with('sender')
                ->orderBy('created_at', 'asc')
                ->paginate(50);

            return $this->success([
                'messages' => $messages->map(function ($message) {
                    return [
                        'id' => $message->id,
                        'sender' => [
                            'id' => $message->sender->id,
                            'name' => $message->sender->full_name
                        ],
                        'message' => $message->message,
                        'is_read' => $message->is_read,
                        'created_at' => $message->created_at->toIso8601String()
                    ];
                }),
                'pagination' => [
                    'current_page' => $messages->currentPage(),
                    'per_page' => $messages->perPage(),
                    'total' => $messages->total(),
                    'last_page' => $messages->lastPage()
                ]
            ], 'Messages retrieved successfully');
        } catch (\Exception $e) {
            Log::error('Failed to retrieve messages', [
                'user_id' => $request->user()->id,
                'order_id' => $orderId,
                'error' => $e->getMessage()
            ]);

            return $this->error('Failed to retrieve messages', 500);
        }
    }

    /**
     * Send message in order conversation
     * POST /api/orders/{order}/messages
     */
    public function sendMessage(SendMessageRequest $request, $orderId)
    {
        try {
            $user = $request->user();

            DB::beginTransaction();

            // Find order
            $order = Order::with(['user', 'vendor.user'])->find($orderId);

            if (!$order) {
                DB::rollBack();
                return $this->error('Order not found', 404);
            }

            // Check authorization
            $auth = $this->checkOrderAuthorization($order, $user->id);

            if (!$auth['authorized']) {
                DB::rollBack();
                return $this->error('You are not authorized to send messages in this conversation', 403);
            }

            // Get or create conversation
            $conversation = $this->getOrCreateConversation($order);

            // Create message
            $message = Message::create([
                'conversation_id' => $conversation->id,
                'sender_id' => $user->id,
                'message' => $request->message,
                'is_read' => false
            ]);

            // Update conversation timestamp
            $conversation->touch();

            // Determine recipient (other party in conversation)
            $recipientId = $auth['is_customer'] ? $order->vendor->user_id : $order->user_id;
            $recipientType = $auth['is_customer'] ? 'vendor' : 'customer';

            // Notify recipient about new message
            NotificationService::notifyUser(
                $recipientId,
                'New message',
                "You have a new message about order {$order->order_number}",
                'message',
                [
                    'order_id' => $order->id,
                    'message_id' => $message->id
                ],
                false // don't send email for messages
            );

            DB::commit();

            // Log activity
            ActivityLogger::log(
                $user->id,
                'message_sent',
                "User {$user->full_name} sent message to {$recipientType} in order {$order->order_number}",
                'App\\Models\\Message',
                $message->id
            );

            Log::info('Message sent', [
                'user_id' => $user->id,
                'order_id' => $order->id,
                'conversation_id' => $conversation->id,
                'message_id' => $message->id
            ]);

            return $this->success([
                'message' => [
                    'id' => $message->id,
                    'sender' => [
                        'id' => $user->id,
                        'name' => $user->full_name
                    ],
                    'message' => $message->message,
                    'is_read' => $message->is_read,
                    'created_at' => $message->created_at->toIso8601String()
                ]
            ], 'Message sent successfully', 201);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to send message', [
                'user_id' => $request->user()->id,
                'order_id' => $orderId,
                'error' => $e->getMessage()
            ]);

            return $this->error('Failed to send message', 500);
        }
    }
}