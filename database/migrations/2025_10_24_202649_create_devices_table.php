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
            $table->string('ip_address')->unique();
            $table->string('mac_address');
            $table->json('extensions')->nullable();
            $table->string('status')->default('offline');
             $table->unsignedBigInteger('building_id')->nullable(); // Changed to unsignedBigInteger
            $table->timestamps();
            
            // Manually define foreign key to reference building_id
            $table->foreign('building_id')
                  ->references('building_id') // Reference building_id, not id
                  ->on('buildings')
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
