<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\ProdutorRural;
use App\Models\Propriedade;
use App\Models\UnidadeProducao;
use App\Models\Rebanho;
use App\Models\Documento;

echo "=== VERIFICAÇÃO DE UUID ===\n\n";

// Verificar User
$user = User::first();
if ($user) {
    echo "✅ User:\n";
    echo "   ID: {$user->id}\n";
    echo "   Tipo: " . gettype($user->id) . "\n";
    echo "   É UUID: " . (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $user->id) ? 'SIM' : 'NÃO') . "\n\n";
} else {
    echo "⚠️  Nenhum usuário encontrado\n\n";
}

// Verificar ProdutorRural
$produtor = ProdutorRural::first();
if ($produtor) {
    echo "✅ ProdutorRural:\n";
    echo "   ID: {$produtor->id}\n";
    echo "   Tipo: " . gettype($produtor->id) . "\n";
    echo "   É UUID: " . (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $produtor->id) ? 'SIM' : 'NÃO') . "\n\n";
}

// Verificar Propriedade
$propriedade = Propriedade::first();
if ($propriedade) {
    echo "✅ Propriedade:\n";
    echo "   ID: {$propriedade->id}\n";
    echo "   Tipo: " . gettype($propriedade->id) . "\n";
    echo "   É UUID: " . (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $propriedade->id) ? 'SIM' : 'NÃO') . "\n\n";
}

// Verificar UnidadeProducao
$unidade = UnidadeProducao::first();
if ($unidade) {
    echo "✅ UnidadeProducao:\n";
    echo "   ID: {$unidade->id}\n";
    echo "   Tipo: " . gettype($unidade->id) . "\n";
    echo "   É UUID: " . (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $unidade->id) ? 'SIM' : 'NÃO') . "\n\n";
}

// Verificar Rebanho
$rebanho = Rebanho::first();
if ($rebanho) {
    echo "✅ Rebanho:\n";
    echo "   ID: {$rebanho->id}\n";
    echo "   Tipo: " . gettype($rebanho->id) . "\n";
    echo "   É UUID: " . (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $rebanho->id) ? 'SIM' : 'NÃO') . "\n\n";
}

// Verificar Documento
$documento = Documento::first();
if ($documento) {
    echo "✅ Documento:\n";
    echo "   ID: {$documento->id}\n";
    echo "   Tipo: " . gettype($documento->id) . "\n";
    echo "   É UUID: " . (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $documento->id) ? 'SIM' : 'NÃO') . "\n\n";
}

echo "=== VERIFICAÇÃO COMPLETA ===\n";
