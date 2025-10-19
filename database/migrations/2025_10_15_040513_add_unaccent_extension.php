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
        // Habilita a extensão unaccent apenas para PostgreSQL
        // SQLite não tem esta extensão, mas testes funcionam sem ela
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('CREATE EXTENSION IF NOT EXISTS unaccent;');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove a extensão unaccent apenas do PostgreSQL
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('DROP EXTENSION IF EXISTS unaccent;');
        }
    }
};
