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
        Schema::table('buildings', function (Blueprint $table) {
            $table->decimal('map_x', 8, 4)->nullable()->after('name'); // % from left (0-100)
            $table->decimal('map_y', 8, 4)->nullable()->after('map_x'); // % from top (0-100)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('buildings', function (Blueprint $table) {
            $table->dropColumn(['map_x', 'map_y']);
        });
    }
};
