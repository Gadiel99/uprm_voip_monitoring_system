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
        Schema::create('devices', function (Blueprint $table) {
            $table->id('device_id');
            $table->string('mac_address')->unique(); // MAC address is the primary identifier
            $table->string('ip_address')->nullable(); // IP can be null if device is offline
            $table->string('status')->default('offline'); // online/offline status
            $table->boolean('is_critical')->default(false);
            $table->unsignedBigInteger('network_id')->nullable(); // Network can be null if offline
            $table->timestamps();

            $table->foreign('network_id')
                  ->references('network_id') // Reference network_id, not id
                  ->on('networks')
                  ->onDelete('cascade');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('devices');
    }
};
