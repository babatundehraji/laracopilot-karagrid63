<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // payer (usually customer)
            
            // Payment gateway details
            $table->enum('provider', ['stripe', 'paypal', 'flutterwave', 'paystack'])->default('paystack');
            $table->string('provider_payment_id', 191); // payment_intent / charge id
            $table->string('provider_charge_id', 191)->nullable();
            
            // Financial details
            $table->decimal('amount', 15, 2);
            $table->string('currency', 3)->default('NGN');
            
            // Status tracking
            $table->enum('status', ['pending', 'succeeded', 'failed', 'refunded'])->default('pending');
            
            // Error tracking
            $table->string('error_code', 100)->nullable();
            $table->text('error_message')->nullable();
            
            // Gateway data
            $table->json('raw_request')->nullable();
            $table->json('raw_response')->nullable();
            
            // Timestamps
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('refunded_at')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index(['order_id', 'status']);
            $table->index(['user_id', 'status']);
            $table->index('provider_payment_id');
            $table->index(['provider', 'status']);
            $table->index('created_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('payment_transactions');
    }
};