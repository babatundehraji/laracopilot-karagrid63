<?php

namespace Database\Seeders;

use App\Models\Dispute;
use App\Models\Order;
use Illuminate\Database\Seeder;

class DisputeSeeder extends Seeder
{
    public function run(): void
    {
        // Get completed and disputed orders
        $completedOrders = Order::where('status', 'completed')
            ->with(['customer', 'vendor'])
            ->limit(3)
            ->get();

        $disputedOrder = Order::where('status', 'disputed')->with(['customer', 'vendor'])->first();

        // Create open dispute (recent)
        if ($completedOrders->count() > 0) {
            $order = $completedOrders->first();
            Dispute::create([
                'order_id' => $order->id,
                'customer_id' => $order->customer_id,
                'vendor_id' => $order->vendor_id,
                'opened_by_user_id' => $order->customer_id,
                'reason_code' => 'poor_quality',
                'reason' => 'The service quality was below expectations. The vendor arrived late and the work was not completed to the agreed standard. Several items were left incomplete and I had to hire another service provider to finish the job.',
                'status' => 'open',
                'opened_at' => now()->subDays(2)
            ]);
        }

        // Create under_review dispute
        if ($completedOrders->count() > 1) {
            $order = $completedOrders->get(1);
            Dispute::create([
                'order_id' => $order->id,
                'customer_id' => $order->customer_id,
                'vendor_id' => $order->vendor_id,
                'opened_by_user_id' => $order->customer_id,
                'reason_code' => 'no_show',
                'reason' => 'Vendor did not show up at the scheduled time and did not provide any communication. I waited for 2 hours and had to reschedule my entire day. This is completely unacceptable.',
                'status' => 'under_review',
                'opened_at' => now()->subDays(5)
            ]);
        }

        // Create resolved dispute (refund_customer)
        if ($completedOrders->count() > 2) {
            $order = $completedOrders->get(2);
            Dispute::create([
                'order_id' => $order->id,
                'customer_id' => $order->customer_id,
                'vendor_id' => $order->vendor_id,
                'opened_by_user_id' => $order->customer_id,
                'reason_code' => 'incomplete',
                'reason' => 'Service was only partially completed. Vendor said they would return to finish but never showed up. Multiple attempts to contact were unsuccessful.',
                'status' => 'resolved',
                'resolution' => 'refund_customer',
                'resolution_notes' => 'After reviewing evidence from both parties and attempting to contact the vendor without success, we have decided to issue a full refund to the customer. The vendor\'s account has been flagged for review.',
                'opened_at' => now()->subDays(15),
                'closed_at' => now()->subDays(8)
            ]);
        }

        // Handle the existing disputed order
        if ($disputedOrder) {
            Dispute::create([
                'order_id' => $disputedOrder->id,
                'customer_id' => $disputedOrder->customer_id,
                'vendor_id' => $disputedOrder->vendor_id,
                'opened_by_user_id' => $disputedOrder->customer_id,
                'reason_code' => 'damaged_property',
                'reason' => 'During the service, the vendor accidentally damaged my property. A ceramic vase was broken and the wall was scratched. The vendor acknowledged the damage but has not taken responsibility or offered compensation.',
                'status' => 'under_review',
                'opened_at' => $disputedOrder->disputed_at
            ]);
        }

        // Create closed dispute (release_vendor)
        $oldCompletedOrder = Order::where('status', 'completed')
            ->where('completed_at', '<', now()->subDays(20))
            ->with(['customer', 'vendor'])
            ->first();

        if ($oldCompletedOrder) {
            Dispute::create([
                'order_id' => $oldCompletedOrder->id,
                'customer_id' => $oldCompletedOrder->customer_id,
                'vendor_id' => $oldCompletedOrder->vendor_id,
                'opened_by_user_id' => $oldCompletedOrder->customer_id,
                'reason_code' => 'price_discrepancy',
                'reason' => 'The final price charged was higher than the quoted price. I was expecting to pay the amount shown in the booking but was charged extra without prior notice.',
                'status' => 'closed',
                'resolution' => 'release_vendor',
                'resolution_notes' => 'After review, the price increase was due to additional work requested by the customer during the service. The vendor provided documentation of the customer\'s approval for the extra work. Payment released to vendor.',
                'opened_at' => now()->subDays(25),
                'closed_at' => now()->subDays(18)
            ]);
        }

        // Create resolved dispute (partial refund)
        $anotherCompletedOrder = Order::where('status', 'completed')
            ->whereNotIn('id', [
                $completedOrders->pluck('id')->toArray(),
                $oldCompletedOrder?->id
            ])
            ->with(['customer', 'vendor'])
            ->first();

        if ($anotherCompletedOrder) {
            Dispute::create([
                'order_id' => $anotherCompletedOrder->id,
                'customer_id' => $anotherCompletedOrder->customer_id,
                'vendor_id' => $anotherCompletedOrder->vendor_id,
                'opened_by_user_id' => $anotherCompletedOrder->customer_id,
                'reason_code' => 'late_arrival',
                'reason' => 'Vendor arrived 3 hours late without prior communication. This caused significant inconvenience and I had to cancel other appointments. The service itself was okay but the delay was unacceptable.',
                'status' => 'resolved',
                'resolution' => 'partial',
                'resolution_notes' => 'Vendor acknowledged the late arrival and apologized. Customer and vendor agreed to a 30% refund as compensation for the inconvenience and time lost. Both parties accepted this resolution.',
                'opened_at' => now()->subDays(12),
                'closed_at' => now()->subDays(6)
            ]);
        }
    }
}