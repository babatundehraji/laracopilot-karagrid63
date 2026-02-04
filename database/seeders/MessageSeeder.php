<?php

namespace Database\Seeders;

use App\Models\Message;
use App\Models\Conversation;
use Illuminate\Database\Seeder;

class MessageSeeder extends Seeder
{
    public function run(): void
    {
        $conversations = Conversation::with(['order.customer', 'order.vendor.user'])->get();

        foreach ($conversations as $conversation) {
            $customer = $conversation->order->customer;
            $vendor = $conversation->order->vendor->user;
            $messageCount = fake()->numberBetween(3, 12);

            // Create realistic conversation flow
            $messageSets = [
                // Pre-service conversation
                [
                    ['sender' => 'customer', 'body' => 'Hi, I just booked your service for ' . $conversation->order->formatted_service_date . '. Can you confirm the time works for you?'],
                    ['sender' => 'vendor', 'body' => 'Hello! Yes, I can confirm the booking for ' . $conversation->order->time_range . '. Looking forward to working with you.'],
                    ['sender' => 'customer', 'body' => 'Great! Do I need to prepare anything before you arrive?'],
                    ['sender' => 'vendor', 'body' => 'Just make sure the area is accessible. I\'ll bring all the necessary equipment. See you then!'],
                ],
                // During service conversation
                [
                    ['sender' => 'vendor', 'body' => 'I\'m on my way! Should arrive in about 15 minutes.'],
                    ['sender' => 'customer', 'body' => 'Perfect, I\'m ready for you.'],
                    ['sender' => 'vendor', 'body' => 'I\'ve arrived and parked outside. Coming to the door now.'],
                    ['sender' => 'customer', 'body' => 'Great, I\'ll open the door now.'],
                ],
                // Post-service conversation
                [
                    ['sender' => 'vendor', 'body' => 'Service completed! Everything is done as discussed. Please let me know if you need anything else.'],
                    ['sender' => 'customer', 'body' => 'Thank you! Everything looks great. Really satisfied with the work.'],
                    ['sender' => 'vendor', 'body' => 'Glad to hear that! If you need this service again in the future, feel free to reach out. Have a great day!'],
                    ['sender' => 'customer', 'body' => 'Will do! Thanks again.'],
                ],
                // Question and answer
                [
                    ['sender' => 'customer', 'body' => 'Quick question - will this service include cleaning up afterwards?'],
                    ['sender' => 'vendor', 'body' => 'Absolutely! I always clean up and remove any debris when I\'m done.'],
                    ['sender' => 'customer', 'body' => 'Perfect, that\'s what I wanted to hear. Thanks!'],
                ],
                // Rescheduling conversation
                [
                    ['sender' => 'customer', 'body' => 'Hi, I need to reschedule our appointment. Something urgent came up. Are you available the following day?'],
                    ['sender' => 'vendor', 'body' => 'No problem! Let me check my schedule. Yes, I can do the next day at the same time.'],
                    ['sender' => 'customer', 'body' => 'That would be perfect. Sorry for the inconvenience!'],
                    ['sender' => 'vendor', 'body' => 'No worries at all. I\'ve noted the change. See you tomorrow!'],
                ],
                // Location clarification
                [
                    ['sender' => 'vendor', 'body' => 'Hello! I have your address but want to confirm - is there parking available nearby?'],
                    ['sender' => 'customer', 'body' => 'Yes, there\'s street parking right in front of the building. Usually spaces available.'],
                    ['sender' => 'vendor', 'body' => 'Great! And what\'s the best way to access the property?'],
                    ['sender' => 'customer', 'body' => 'Just come to the main entrance. I\'ll meet you there.'],
                ],
            ];

            $selectedSet = fake()->randomElement($messageSets);
            $baseTime = $conversation->order->created_at;

            foreach ($selectedSet as $index => $messageData) {
                $senderId = $messageData['sender'] === 'customer' ? $customer->id : $vendor->id;
                
                // Space messages out realistically
                $minutesOffset = $index * fake()->numberBetween(5, 45);
                $createdAt = $baseTime->copy()->addMinutes($minutesOffset);
                
                // Randomly add attachments to some messages
                $attachments = null;
                if (fake()->boolean(15)) {
                    $attachments = [
                        'https://example.com/attachments/' . uniqid() . '.jpg',
                        'https://example.com/attachments/' . uniqid() . '.pdf'
                    ];
                }

                // Most recent messages are unread, older ones are read
                $isRead = $index < count($selectedSet) - 2 || fake()->boolean(70);

                Message::create([
                    'conversation_id' => $conversation->id,
                    'sender_id' => $senderId,
                    'body' => $messageData['body'],
                    'attachments' => $attachments,
                    'is_read' => $isRead,
                    'read_at' => $isRead ? $createdAt->copy()->addMinutes(fake()->numberBetween(2, 30)) : null,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt
                ]);
            }
        }
    }
}