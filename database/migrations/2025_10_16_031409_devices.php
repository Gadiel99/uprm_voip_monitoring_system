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
            $table->id();
            $table->string('device_id')->unique();
            $table->string('owner')->nullable();
            $table->string('ip_address');
            $table->string('last_registered')->nullable();
            $table->string('status')->nullable();
            $table->string('building')->nullable();
            $table->string('call_started')->nullable();
            $table->string('call_ended')->nullable();
            $table->timestamps();
        });
        //
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('devices');
    }
};
