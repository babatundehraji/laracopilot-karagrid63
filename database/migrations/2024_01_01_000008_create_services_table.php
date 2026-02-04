<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->foreignId('subcategory_id')->nullable()->constrained()->onDelete('set null');
            
            // Basic information
            $table->string('title', 191);
            $table->string('slug', 191)->unique();
            $table->string('short_description', 255)->nullable();
            $table->text('description');
            
            // Pricing
            $table->enum('pricing_type', ['hourly', 'fixed']);
            $table->decimal('price', 10, 2);
            $table->integer('min_hours')->nullable();
            $table->integer('max_hours')->nullable();
            
            // Service delivery
            $table->boolean('is_remote')->default(false);
            $table->boolean('is_onsite')->default(true);
            
            // Service location (where service is offered)
            $table->foreignId('service_country_id')->nullable()->constrained('countries')->onDelete('set null');
            $table->foreignId('service_state_id')->nullable()->constrained('states')->onDelete('set null');
            $table->foreignId('service_city_id')->nullable()->constrained('cities')->onDelete('set null');
            $table->string('address_line1', 191)->nullable();
            $table->string('address_line2', 191)->nullable();
            $table->string('postal_code', 20)->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            
            // Media
            $table->string('main_image_url', 255)->nullable();
            $table->json('gallery_images')->nullable();
            
            // Promotion and ratings
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_sponsored')->default(false);
            $table->decimal('average_rating', 3, 2)->nullable();
            $table->integer('review_count')->default(0);
            
            // Status
            $table->enum('status', ['pending', 'approved', 'rejected', 'inactive'])->default('pending');
            
            $table->timestamps();
            
            // Indexes
            $table->index(['vendor_id', 'status']);
            $table->index(['category_id', 'status']);
            $table->index(['subcategory_id', 'status']);
            $table->index(['is_featured', 'status']);
            $table->index(['is_sponsored', 'status']);
            $table->index('average_rating');
        });
    }

    public function down()
    {
        Schema::dropIfExists('services');
    }
};