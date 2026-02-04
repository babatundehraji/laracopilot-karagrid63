<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('order_edits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('edited_by_vendor_id')->constrained('vendors')->onDelete('cascade');
            
            // Edit details
            $table->json('old_data');
            $table->json('new_data');
            
            // Response tracking
            $table->enum('status', ['pending', 'accepted', 'rejected'])->default('pending');
            $table->foreignId('responded_by_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('responded_at')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index(['order_id', 'status']);
            $table->index('edited_by_vendor_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('order_edits');
    }
};