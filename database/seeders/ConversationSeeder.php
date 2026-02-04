<?php

namespace Database\Seeders;

use App\Models\Conversation;
use App\Models\Order;
use Illuminate\Database\Seeder;

class ConversationSeeder extends Seeder
{
    public function run(): void
    {
        // Get orders that should have conversations
        $orders = Order::with(['customer', 'vendor.user'])
            ->whereIn('status', ['pending', 'active', 'completed', 'disputed'])
            ->limit(15)
            ->get();

        foreach ($orders as $order) {
            // Randomly decide who starts the conversation
            $startedBy = fake()->boolean(70) ? $order->customer_id : $order->vendor->user_id;
            
            $conversation = Conversation::create([
                'order_id' => $order->id,
                'started_by_user_id' => $startedBy,
                'last_message_at' => null // Will be updated when messages are created
            ]);
        }
    }
}