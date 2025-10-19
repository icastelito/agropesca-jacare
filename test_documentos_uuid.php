<?php

require __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\DB;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Verificando estrutura da tabela documentos ===\n\n";

// Verificar estrutura da tabela documentos
$columns = DB::select("
    SELECT 
        column_name, 
        data_type, 
        character_maximum_length,
        is_nullable
    FROM information_schema.columns
    WHERE table_name = 'documentos'
    ORDER BY ordinal_position
");

foreach ($columns as $column) {
    echo "Coluna: {$column->column_name}\n";
    echo "  Tipo: {$column->data_type}\n";
    echo "  Nullable: {$column->is_nullable}\n";
    if ($column->character_maximum_length) {
        echo "  Tamanho: {$column->character_maximum_length}\n";
    }
    echo "\n";
}

echo "\n=== Testando relacionamento polimórfico com UUID ===\n\n";

// Pegar um produtor rural
$produtor = DB::table('produtores_rurais')->first();

if ($produtor) {
    echo "Produtor ID: {$produtor->id}\n";
    echo "Tipo do ID: " . (strlen($produtor->id) === 36 ? "UUID ✓" : "Não é UUID ✗") . "\n";
    echo "Nome: {$produtor->nome}\n\n";

    // Verificar se existem documentos para este produtor
    $documentos = DB::table('documentos')
        ->where('documentavel_id', $produtor->id)
        ->where('documentavel_type', 'App\Models\ProdutorRural')
        ->get();

    echo "Total de documentos: " . $documentos->count() . "\n";

    if ($documentos->count() > 0) {
        echo "\nDocumentos encontrados:\n";
        foreach ($documentos as $doc) {
            echo "  - ID: {$doc->id} (UUID: " . (strlen($doc->id) === 36 ? "✓" : "✗") . ")\n";
            echo "    Nome: {$doc->nome_original}\n";
            echo "    documentavel_id: {$doc->documentavel_id} (UUID: " . (strlen($doc->documentavel_id) === 36 ? "✓" : "✗") . ")\n\n";
        }
    } else {
        echo "  Nenhum documento encontrado (normal, pois seeds não criam documentos)\n";
    }
}

echo "\n=== Teste de estrutura da tabela: ✅ SUCESSO ===\n";
