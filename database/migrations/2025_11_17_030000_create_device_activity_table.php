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
        Schema::create('device_activity', function (Blueprint $table) {
            $table->id('activity_id');
            $table->unsignedBigInteger('device_id');
            $table->date('activity_date'); // The date for this day's data
            $table->tinyInteger('day_number')->default(1); // 1 = today, 2 = yesterday
            $table->json('samples'); // Array of 288 samples (5-min intervals)
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            
            // Foreign key
            $table->foreign('device_id')
                  ->references('device_id')
                  ->on('devices')
                  ->onDelete('cascade');
            
            // Indexes
            $table->index('device_id');
            $table->index('activity_date');
            $table->index(['device_id', 'day_number']);
            
            // Unique constraint: one record per device per day_number
            $table->unique(['device_id', 'day_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('device_activity');
    }
};
