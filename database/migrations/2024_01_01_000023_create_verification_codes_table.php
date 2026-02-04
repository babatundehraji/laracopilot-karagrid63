<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('verification_codes', function (Blueprint $table) {
            $table->id();
            $table->string('email', 191)->index();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->enum('type', ['email_verification', 'password_reset']);
            $table->string('code', 10);
            $table->timestamp('expires_at');
            $table->timestamp('used_at')->nullable();
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['email', 'type']);
            $table->index(['code', 'type']);
            $table->index('expires_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('verification_codes');
    }
};