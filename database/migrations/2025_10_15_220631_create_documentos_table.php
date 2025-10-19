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
        Schema::create('documentos', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Relacionamento polimórfico com UUID
            $table->uuidMorphs('documentavel'); // cria documentavel_type e documentavel_id como UUID

            // Informações do arquivo
            $table->string('nome_original'); // nome original do arquivo
            $table->string('nome_arquivo'); // nome único gerado (hash)
            $table->string('tipo'); // mime type (application/pdf, image/jpeg, etc)
            $table->integer('tamanho'); // tamanho em bytes

            // Categorização
            $table->string('categoria')->nullable(); // CPF, CNPJ, RG, Escritura, Foto, Outros

            $table->timestamps();

            // Índices para performance
            $table->index(['documentavel_type', 'documentavel_id'], 'idx_documentos_morph');
            $table->index('categoria', 'idx_documentos_categoria');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documentos');
    }
};
