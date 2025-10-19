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
        // Adicionar data_cadastro em propriedades
        Schema::table('propriedades', function (Blueprint $table) {
            $table->date('data_cadastro')->nullable()->after('area_total');
        });

        // Adicionar data_cadastro em unidades_producao
        Schema::table('unidades_producao', function (Blueprint $table) {
            $table->date('data_cadastro')->nullable()->after('area_total_ha');
        });

        // Adicionar data_cadastro em rebanhos
        Schema::table('rebanhos', function (Blueprint $table) {
            $table->date('data_cadastro')->nullable()->after('quantidade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('propriedades', function (Blueprint $table) {
            $table->dropColumn('data_cadastro');
        });

        Schema::table('unidades_producao', function (Blueprint $table) {
            $table->dropColumn('data_cadastro');
        });

        Schema::table('rebanhos', function (Blueprint $table) {
            $table->dropColumn('data_cadastro');
        });
    }
};
