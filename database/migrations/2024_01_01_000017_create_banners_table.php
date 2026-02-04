<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('banners', function (Blueprint $table) {
            $table->id();
            $table->string('title', 191);
            $table->string('subtitle', 255)->nullable();
            $table->string('image_url', 255);
            
            // Call to action
            $table->string('cta_label', 100)->nullable();
            $table->string('cta_url', 255)->nullable();
            
            // Placement and visibility
            $table->string('placement', 50)->default('home');
            $table->boolean('is_active')->default(true);
            
            // Scheduling
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            
            // Tracking
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            
            $table->timestamps();
            
            // Indexes
            $table->index(['placement', 'is_active']);
            $table->index(['starts_at', 'ends_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('banners');
    }
};