<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Normalize any legacy super admin roles to 'admin'
        DB::table('users')
            ->whereIn('role', ['super_admin','superadmin'])
            ->update(['role' => 'admin']);
    }

    public function down(): void
    {
        // No reliable way to restore who used to be super admin; leaving as no-op
    }
};
