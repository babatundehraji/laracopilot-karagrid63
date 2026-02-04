<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('cities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('state_id')->constrained()->onDelete('cascade');
            $table->string('name', 150);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['state_id', 'is_active']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('cities');
    }
};