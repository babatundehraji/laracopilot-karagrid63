<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('banners', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable()->after('button_text')->constrained()->nullOnDelete();
            $table->foreignId('subcategory_id')->nullable()->after('category_id')->constrained()->nullOnDelete();
            $table->decimal('min_price', 10, 2)->nullable()->after('subcategory_id');
            $table->decimal('max_price', 10, 2)->nullable()->after('min_price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('banners', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropForeign(['subcategory_id']);
            $table->dropColumn(['category_id', 'subcategory_id', 'min_price', 'max_price']);
        });
    }
};