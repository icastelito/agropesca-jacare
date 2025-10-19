<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Adiciona índices para otimizar consultas frequentes
     */
    public function up(): void
    {
        // Índices para produtores_rurais
        Schema::table('produtores_rurais', function (Blueprint $table) {
            // Índice para busca por nome (usado em filtros)
            $table->index('nome', 'idx_produtores_nome');

            // Índice único para CPF/CNPJ (já existe constraint, mas adiciona índice explícito)
            // O unique já cria índice automaticamente, então não precisa duplicar

            // Índice para busca por email
            $table->index('email', 'idx_produtores_email');

            // Índice para ordenação por data de cadastro
            $table->index('data_cadastro', 'idx_produtores_data_cadastro');

            // Índice composto para consultas que filtram e ordenam
            $table->index(['created_at', 'nome'], 'idx_produtores_created_nome');
        });

        // Índices para propriedades
        Schema::table('propriedades', function (Blueprint $table) {
            // Índice para busca por nome
            $table->index('nome', 'idx_propriedades_nome');

            // Índice para busca por município
            $table->index('municipio', 'idx_propriedades_municipio');

            // Índice para busca por UF
            $table->index('uf', 'idx_propriedades_uf');

            // Índice composto para busca por município + UF (comum em relatórios)
            $table->index(['municipio', 'uf'], 'idx_propriedades_municipio_uf');

            // Índice para foreign key produtor_id (se não existir automaticamente)
            // Foreign keys geralmente já criam índices, mas vamos garantir
            $table->index('produtor_id', 'idx_propriedades_produtor_id');

            // Índice composto para consultas que filtram por produtor e ordenam
            $table->index(['produtor_id', 'created_at'], 'idx_propriedades_produtor_created');
        });

        // Índices para unidades_producao
        Schema::table('unidades_producao', function (Blueprint $table) {
            // Índice para busca por cultura (usado em relatórios)
            $table->index('nome_cultura', 'idx_unidades_cultura');

            // Índice para foreign key propriedade_id
            $table->index('propriedade_id', 'idx_unidades_propriedade_id');

            // Índice composto para agregações por cultura e propriedade
            $table->index(['propriedade_id', 'nome_cultura'], 'idx_unidades_prop_cultura');

            // Índice para ordenação por área
            $table->index('area_total_ha', 'idx_unidades_area');

            // Índice composto para relatórios de hectares por cultura
            $table->index(['nome_cultura', 'area_total_ha'], 'idx_unidades_cultura_area');
        });

        // Índices para rebanhos
        Schema::table('rebanhos', function (Blueprint $table) {
            // Índice para busca por espécie (usado em relatórios)
            $table->index('especie', 'idx_rebanhos_especie');

            // Índice para foreign key propriedade_id
            $table->index('propriedade_id', 'idx_rebanhos_propriedade_id');

            // Índice composto para agregações por espécie e propriedade
            $table->index(['propriedade_id', 'especie'], 'idx_rebanhos_prop_especie');

            // Índice para ordenação por quantidade
            $table->index('quantidade', 'idx_rebanhos_quantidade');

            // Índice composto para relatórios de animais por espécie
            $table->index(['especie', 'quantidade'], 'idx_rebanhos_especie_qtd');
        });

        // Índices adicionais para otimizar joins e relacionamentos
        Schema::table('propriedades', function (Blueprint $table) {
            // Índice composto para joins frequentes em relatórios
            $table->index(['produtor_id', 'municipio', 'uf'], 'idx_propriedades_produtor_local');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remover índices de produtores_rurais
        Schema::table('produtores_rurais', function (Blueprint $table) {
            $table->dropIndex('idx_produtores_nome');
            $table->dropIndex('idx_produtores_email');
            $table->dropIndex('idx_produtores_data_cadastro');
            $table->dropIndex('idx_produtores_created_nome');
        });

        // Remover índices de propriedades
        Schema::table('propriedades', function (Blueprint $table) {
            $table->dropIndex('idx_propriedades_nome');
            $table->dropIndex('idx_propriedades_municipio');
            $table->dropIndex('idx_propriedades_uf');
            $table->dropIndex('idx_propriedades_municipio_uf');
            $table->dropIndex('idx_propriedades_produtor_id');
            $table->dropIndex('idx_propriedades_produtor_created');
            $table->dropIndex('idx_propriedades_produtor_local');
        });

        // Remover índices de unidades_producao
        Schema::table('unidades_producao', function (Blueprint $table) {
            $table->dropIndex('idx_unidades_cultura');
            $table->dropIndex('idx_unidades_propriedade_id');
            $table->dropIndex('idx_unidades_prop_cultura');
            $table->dropIndex('idx_unidades_area');
            $table->dropIndex('idx_unidades_cultura_area');
        });

        // Remover índices de rebanhos
        Schema::table('rebanhos', function (Blueprint $table) {
            $table->dropIndex('idx_rebanhos_especie');
            $table->dropIndex('idx_rebanhos_propriedade_id');
            $table->dropIndex('idx_rebanhos_prop_especie');
            $table->dropIndex('idx_rebanhos_quantidade');
            $table->dropIndex('idx_rebanhos_especie_qtd');
        });
    }
};
