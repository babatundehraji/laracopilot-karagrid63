<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('disputes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('customer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('vendor_id')->constrained('vendors')->onDelete('cascade');
            $table->foreignId('opened_by_user_id')->constrained('users')->onDelete('cascade');
            
            // Dispute details
            $table->string('reason_code', 100)->nullable(); // no_show, poor_quality, incomplete, etc.
            $table->text('reason');
            
            // Status tracking
            $table->enum('status', ['open', 'under_review', 'resolved', 'closed'])->default('open');
            
            // Resolution
            $table->enum('resolution', ['refund_customer', 'release_vendor', 'partial', 'none'])->nullable();
            $table->text('resolution_notes')->nullable();
            
            // Timestamps
            $table->timestamp('opened_at');
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index(['order_id', 'status']);
            $table->index(['customer_id', 'status']);
            $table->index(['vendor_id', 'status']);
            $table->index('status');
        });
    }

    public function down()
    {
        Schema::dropIfExists('disputes');
    }
};