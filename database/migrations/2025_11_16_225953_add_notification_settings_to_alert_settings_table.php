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
        Schema::table('alert_settings', function (Blueprint $table) {
            $table->boolean('email_notifications_enabled')->default(true)->after('is_active');
            $table->boolean('push_notifications_enabled')->default(false)->after('email_notifications_enabled');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('alert_settings', function (Blueprint $table) {
            $table->dropColumn(['email_notifications_enabled', 'push_notifications_enabled']);
        });
    }
};
