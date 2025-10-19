<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\ProdutorRural;
use App\Models\Propriedade;

echo "=== Verificando UUIDs ===\n\n";

$produtor = ProdutorRural::first();
echo "ProdutorRural:\n";
echo "  ID: {$produtor->id}\n";
echo "  Nome: {$produtor->nome}\n";
echo "  Tamanho ID: " . strlen($produtor->id) . " caracteres\n";
echo "  É UUID válido? " . (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $produtor->id) ? 'SIM' : 'NÃO') . "\n\n";

$propriedade = Propriedade::first();
echo "Propriedade:\n";
echo "  ID: {$propriedade->id}\n";
echo "  Nome: {$propriedade->nome}\n";
echo "  Produtor ID: {$propriedade->produtor_id}\n";
echo "  Tamanho ID: " . strlen($propriedade->id) . " caracteres\n";
echo "  É UUID válido? " . (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $propriedade->id) ? 'SIM' : 'NÃO') . "\n\n";

echo "=== Teste de relacionamento ===\n";
$produtorComPropriedades = ProdutorRural::with('propriedades')->first();
echo "Produtor: {$produtorComPropriedades->nome}\n";
echo "Total de propriedades: " . $produtorComPropriedades->propriedades->count() . "\n";

if ($produtorComPropriedades->propriedades->count() > 0) {
    $prop = $produtorComPropriedades->propriedades->first();
    echo "Primeira propriedade: {$prop->nome} - {$prop->municipio}/{$prop->uf}\n";
}

echo "\n✅ Verificação concluída!\n";
