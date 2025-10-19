<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Habilita a extensão uuid-ossp apenas para PostgreSQL
        // SQLite já tem suporte nativo a UUID
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('CREATE EXTENSION IF NOT EXISTS "uuid-ossp"');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove a extensão apenas do PostgreSQL
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('DROP EXTENSION IF EXISTS "uuid-ossp"');
        }
    }
};
