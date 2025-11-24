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
        Schema::create('system_logs', function (Blueprint $table) {
            $table->id();
            $table->timestamp('created_at')->useCurrent();
            $table->string('action', 50)->index(); // LOGIN, LOGOUT, ADD, EDIT, DELETE, ERROR, INFO, WARNING
            $table->text('comment');
            $table->string('user', 100)->index();
            $table->string('ip', 45)->nullable(); // Supports IPv4 and IPv6
            $table->json('context')->nullable(); // Additional context data
            
            // Index for faster queries
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_logs');
    }
};
