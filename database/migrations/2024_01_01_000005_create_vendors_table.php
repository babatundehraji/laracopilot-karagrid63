<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('vendors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->onDelete('cascade');
            
            // Business information
            $table->string('business_name', 191);
            $table->string('business_type', 100)->nullable();
            $table->string('tax_id', 100)->nullable();
            $table->string('registration_number', 100)->nullable();
            $table->string('website_url', 191)->nullable();
            $table->string('support_email', 191)->nullable();
            $table->string('support_phone', 20)->nullable();
            
            // Service location (vendor's base)
            $table->foreignId('country_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('state_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('city_id')->nullable()->constrained()->onDelete('set null');
            $table->string('address_line1', 191)->nullable();
            $table->string('address_line2', 191)->nullable();
            $table->string('postal_code', 20)->nullable();
            
            // Documents & compliance
            $table->json('documents')->nullable();
            
            // Payout details
            $table->enum('payout_method', ['bank_transfer', 'paypal', 'stripe', 'manual'])->default('bank_transfer');
            $table->string('payout_currency', 3)->default('USD');
            $table->string('bank_name', 191)->nullable();
            $table->string('bank_account_name', 191)->nullable();
            $table->string('bank_account_number', 100)->nullable();
            $table->string('bank_routing_number', 100)->nullable();
            $table->string('bank_swift_code', 50)->nullable();
            $table->string('paypal_email', 191)->nullable();
            $table->string('stripe_account_id', 191)->nullable();
            
            // Ratings and verification
            $table->decimal('rating', 3, 2)->default(0);
            $table->integer('total_reviews')->default(0);
            $table->boolean('is_verified')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamp('verified_at')->nullable();
            
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('vendors');
    }
};