<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('service_promotions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained()->onDelete('cascade');
            
            // Promotion type
            $table->enum('type', ['sponsored', 'featured', 'deal']);
            $table->string('label', 100)->nullable();
            
            // Pricing
            $table->decimal('original_price', 15, 2)->nullable();
            $table->decimal('promo_price', 15, 2)->nullable();
            
            // Scheduling
            $table->timestamp('starts_at');
            $table->timestamp('ends_at');
            
            // Display priority
            $table->integer('priority')->default(0);
            $table->boolean('is_active')->default(true);
            
            // Tracking
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            
            $table->timestamps();
            
            // Indexes
            $table->index(['service_id', 'type']);
            $table->index(['type', 'is_active']);
            $table->index(['starts_at', 'ends_at']);
            $table->index('priority');
        });
    }

    public function down()
    {
        Schema::dropIfExists('service_promotions');
    }
};