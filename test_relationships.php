<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\ProdutorRural;
use App\Models\Propriedade;
use App\Models\UnidadeProducao;
use App\Models\Rebanho;

echo "\n=== TESTE DE RELACIONAMENTOS ===\n\n";

// Teste 1: Produtor Rural -> Propriedades
$produtor = ProdutorRural::with(['propriedades.unidadesProducao', 'propriedades.rebanhos'])->first();
echo "Produtor: {$produtor->nome}\n";
echo "CPF/CNPJ: {$produtor->cpf_cnpj}\n";
echo "Total de propriedades: {$produtor->propriedades->count()}\n\n";

foreach ($produtor->propriedades as $prop) {
    echo "  Propriedade: {$prop->nome}\n";
    echo "  - Município: {$prop->municipio}/{$prop->uf}\n";
    echo "  - Área Total: {$prop->area_total} ha\n";
    echo "  - Unidades de Produção: {$prop->unidadesProducao->count()}\n";

    foreach ($prop->unidadesProducao as $unidade) {
        echo "    * {$unidade->nome_cultura} ({$unidade->area_total_ha} ha)\n";
    }

    echo "  - Rebanhos: {$prop->rebanhos->count()}\n";

    foreach ($prop->rebanhos as $rebanho) {
        echo "    * {$rebanho->especie} ({$rebanho->quantidade} animais) - {$rebanho->finalidade}\n";
    }

    echo "\n";
}

// Teste 2: Verificar culturas cadastradas
echo "\n=== CULTURAS CADASTRADAS ===\n";
$culturas = UnidadeProducao::select('nome_cultura')
    ->distinct()
    ->pluck('nome_cultura');

foreach ($culturas as $cultura) {
    $total = UnidadeProducao::where('nome_cultura', $cultura)->sum('area_total_ha');
    echo "- {$cultura}: {$total} ha\n";
}

// Teste 3: Verificar espécies cadastradas
echo "\n=== ESPÉCIES DE REBANHO ===\n";
$especies = Rebanho::select('especie')
    ->distinct()
    ->pluck('especie');

foreach ($especies as $especie) {
    $total = Rebanho::where('especie', $especie)->sum('quantidade');
    echo "- {$especie}: {$total} animais\n";
}

echo "\n=== TESTES CONCLUÍDOS COM SUCESSO ===\n\n";
