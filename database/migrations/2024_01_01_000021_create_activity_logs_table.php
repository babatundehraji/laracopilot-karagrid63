<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            
            // Action details
            $table->string('action', 100);
            $table->text('description')->nullable();
            
            // Polymorphic subject (what was acted upon)
            $table->string('subject_type', 100)->nullable();
            $table->bigInteger('subject_id')->nullable();
            
            // Request details
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index(['user_id', 'action']);
            $table->index(['subject_type', 'subject_id']);
            $table->index('action');
            $table->index('ip_address');
            $table->index('created_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('activity_logs');
    }
};