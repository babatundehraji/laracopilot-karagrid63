<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // whose balance is affected
            $table->foreignId('order_id')->nullable()->constrained()->onDelete('set null');
            
            // Transaction classification
            $table->enum('type', ['credit', 'debit']);
            $table->enum('category', [
                'order',        // customer debit when paying
                'earning',      // vendor credit when earning
                'promotion',    // vendor debit when paying for ads
                'payout',       // vendor debit when admin pays out
                'refund',       // customer credit or vendor debit
                'fee',          // platform credit
                'adjustment'    // manual correction
            ]);
            
            // Financial details
            $table->decimal('amount', 15, 2);
            $table->string('currency', 3)->default('NGN');
            $table->decimal('balance_after', 15, 2)->nullable(); // optional convenience
            
            // Reference and metadata
            $table->string('reference', 191)->nullable(); // gateway ref, payout ref, etc.
            $table->json('meta')->nullable(); // any extra info: breakdown, notes
            
            // Status
            $table->enum('status', ['pending', 'completed', 'reversed'])->default('completed');
            
            $table->timestamps();
            
            // Indexes
            $table->index(['user_id', 'type']);
            $table->index(['user_id', 'category']);
            $table->index(['user_id', 'status']);
            $table->index(['order_id', 'category']);
            $table->index('reference');
            $table->index('created_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('transactions');
    }
};