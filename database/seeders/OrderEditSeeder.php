<?php

namespace Database\Seeders;

use App\Models\OrderEdit;
use App\Models\Order;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class OrderEditSeeder extends Seeder
{
    public function run(): void
    {
        // Get some pending and active orders
        $editableOrders = Order::whereIn('status', ['pending', 'active'])
            ->with(['vendor', 'customer'])
            ->limit(6)
            ->get();

        foreach ($editableOrders as $index => $order) {
            // Create different types of edits
            if ($index % 3 === 0) {
                // Pending edit - date change
                OrderEdit::create([
                    'order_id' => $order->id,
                    'edited_by_vendor_id' => $order->vendor_id,
                    'old_data' => [
                        'service_date' => $order->service_date->toDateString(),
                        'start_time' => $order->start_time
                    ],
                    'new_data' => [
                        'service_date' => Carbon::parse($order->service_date)->addDays(2)->toDateString(),
                        'start_time' => '14:00:00'
                    ],
                    'status' => 'pending'
                ]);
                
                // Update order status to edited
                $order->update(['status' => 'edited']);
            } elseif ($index % 3 === 1) {
                // Accepted edit - time change
                OrderEdit::create([
                    'order_id' => $order->id,
                    'edited_by_vendor_id' => $order->vendor_id,
                    'old_data' => [
                        'start_time' => $order->start_time,
                        'hours' => $order->hours
                    ],
                    'new_data' => [
                        'start_time' => '11:00:00',
                        'hours' => $order->hours ? $order->hours + 1 : null
                    ],
                    'status' => 'accepted',
                    'responded_by_user_id' => $order->customer_id,
                    'responded_at' => now()->subDays(rand(1, 3))
                ]);
                
                // Apply the accepted changes
                $order->update([
                    'start_time' => '11:00:00',
                    'hours' => $order->hours ? $order->hours + 1 : null,
                    'status' => 'active'
                ]);
            } else {
                // Rejected edit - price change attempt
                OrderEdit::create([
                    'order_id' => $order->id,
                    'edited_by_vendor_id' => $order->vendor_id,
                    'old_data' => [
                        'service_price' => $order->service_price,
                        'hours' => $order->hours,
                        'subtotal' => $order->subtotal,
                        'total_amount' => $order->total_amount
                    ],
                    'new_data' => [
                        'service_price' => $order->service_price * 1.2,
                        'hours' => $order->hours ? $order->hours + 2 : null,
                        'subtotal' => $order->subtotal * 1.3,
                        'total_amount' => $order->total_amount * 1.3
                    ],
                    'status' => 'rejected',
                    'responded_by_user_id' => $order->customer_id,
                    'responded_at' => now()->subDays(rand(1, 2))
                ]);
            }
        }

        // Create a pending edit with location change
        $onsiteOrder = Order::where('location_type', 'onsite')
            ->where('status', 'pending')
            ->first();

        if ($onsiteOrder) {
            OrderEdit::create([
                'order_id' => $onsiteOrder->id,
                'edited_by_vendor_id' => $onsiteOrder->vendor_id,
                'old_data' => [
                    'address_line1' => $onsiteOrder->address_line1,
                    'city' => $onsiteOrder->city
                ],
                'new_data' => [
                    'address_line1' => '456 New Location Street',
                    'city' => 'Lekki'
                ],
                'status' => 'pending'
            ]);
            
            $onsiteOrder->update(['status' => 'edited']);
        }
    }
}