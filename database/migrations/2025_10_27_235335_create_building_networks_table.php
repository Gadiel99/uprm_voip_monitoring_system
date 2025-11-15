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
       Schema::create('building_networks', function (Blueprint $table) {
            $table->unsignedBigInteger('building_id');
            $table->unsignedBigInteger('network_id');
            
            $table->foreign('building_id')
                  ->references('building_id')
                  ->on('buildings')
                  ->onDelete('cascade');
                  
            $table->foreign('network_id')
                  ->references('network_id')
                  ->on('networks')
                  ->onDelete('cascade');
                  
            $table->primary(['building_id', 'network_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('building_networks');
    }
};
