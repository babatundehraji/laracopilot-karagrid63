<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_reference', 50)->unique();
            
            // Parties involved
            $table->foreignId('customer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('vendor_id')->constrained('vendors')->onDelete('cascade');
            $table->foreignId('service_id')->constrained()->onDelete('cascade');
            
            // Service snapshot
            $table->string('service_title', 191);
            $table->enum('service_pricing_type', ['hourly', 'fixed']);
            $table->decimal('service_price', 10, 2);
            
            // Order status
            $table->enum('status', ['pending', 'edited', 'active', 'completed', 'disputed', 'refunded', 'cancelled'])->default('pending');
            
            // Service schedule
            $table->date('service_date');
            $table->time('start_time');
            $table->integer('hours')->nullable();
            $table->time('end_time')->nullable();
            
            // Location
            $table->enum('location_type', ['remote', 'onsite']);
            $table->string('address_line1', 191)->nullable();
            $table->string('address_line2', 191)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('state', 100)->nullable();
            $table->string('country', 100)->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            
            // Financial details
            $table->string('currency', 3)->default('NGN');
            $table->decimal('subtotal', 10, 2);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('platform_fee', 10, 2)->default(0);
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2);
            
            // Payment tracking
            $table->enum('payment_status', ['unpaid', 'paid', 'refunded', 'partially_refunded'])->default('unpaid');
            $table->timestamp('paid_at')->nullable();
            
            // Status timestamps
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->timestamp('disputed_at')->nullable();
            
            // Notes
            $table->text('customer_note')->nullable();
            $table->text('vendor_note')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index(['customer_id', 'status']);
            $table->index(['vendor_id', 'status']);
            $table->index(['service_id', 'status']);
            $table->index(['service_date', 'status']);
            $table->index('payment_status');
        });
    }

    public function down()
    {
        Schema::dropIfExists('orders');
    }
};