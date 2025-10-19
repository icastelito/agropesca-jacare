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
        // Habilita a extensão unaccent do PostgreSQL
        // Permite comparações sem acentos nativamente
        DB::statement('CREATE EXTENSION IF NOT EXISTS unaccent;');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove a extensão unaccent
        DB::statement('DROP EXTENSION IF EXISTS unaccent;');
    }
};
