<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('user_login_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            
            // Login attempt details
            $table->string('email', 191)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            
            // Success/failure
            $table->boolean('success')->default(false);
            
            // Timestamp
            $table->timestamp('logged_in_at');
            $table->timestamps();
            
            // Indexes
            $table->index(['user_id', 'success']);
            $table->index(['email', 'success']);
            $table->index('ip_address');
            $table->index('logged_in_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_login_logs');
    }
};