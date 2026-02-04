<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // Drop old columns if they exist
            $table->dropColumn(['name', 'phone', 'role', 'avatar', 'is_active']);
        });

        Schema::table('users', function (Blueprint $table) {
            // Name fields
            $table->string('first_name', 100)->after('id');
            $table->string('last_name', 100)->after('first_name');
            
            // Contact (phone after email)
            $table->string('phone', 20)->nullable()->unique()->after('email');
            
            // Profile
            $table->string('avatar_url', 255)->nullable()->after('password');
            $table->text('bio')->nullable()->after('avatar_url');
            
            // Role and status
            $table->enum('role', ['customer', 'admin'])->default('customer')->after('bio');
            $table->enum('status', ['active', 'suspended'])->default('active')->after('role');
            
            // Location (profile only, not FK)
            $table->string('country', 100)->nullable()->after('status');
            $table->string('state', 100)->nullable()->after('country');
            $table->string('city', 100)->nullable()->after('state');
            $table->string('timezone', 50)->nullable()->after('city');
            
            // Verification
            $table->timestamp('phone_verified_at')->nullable()->after('email_verified_at');
            $table->timestamp('last_login_at')->nullable()->after('phone_verified_at');
            
            // Preferences and metadata
            $table->json('notification_prefs')->nullable()->after('last_login_at');
            $table->json('meta')->nullable()->after('notification_prefs');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'first_name',
                'last_name',
                'phone',
                'avatar_url',
                'bio',
                'role',
                'status',
                'country',
                'state',
                'city',
                'timezone',
                'phone_verified_at',
                'last_login_at',
                'notification_prefs',
                'meta'
            ]);
            
            // Restore old columns
            $table->string('name')->after('id');
            $table->string('phone')->nullable()->after('email');
            $table->enum('role', ['customer', 'admin'])->default('customer')->after('password');
            $table->string('avatar')->nullable()->after('role');
            $table->boolean('is_active')->default(true)->after('remember_token');
        });
    }
};