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
        Schema::create('propriedades', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nome');
            $table->string('municipio');
            $table->string('uf', 2);
            $table->string('inscricao_estadual');
            $table->decimal('area_total', 10, 2);
            $table->foreignUuid('produtor_id')->constrained('produtores_rurais')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('propriedades');
    }
};
