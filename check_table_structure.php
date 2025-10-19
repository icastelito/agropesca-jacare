<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== Verificando estrutura das tabelas ===\n\n";

// Verificar estrutura da tabela produtores_rurais
echo "📋 Estrutura da tabela produtores_rurais:\n";
$columns = DB::select("
    SELECT column_name, data_type, character_maximum_length 
    FROM information_schema.columns 
    WHERE table_name = 'produtores_rurais' 
    ORDER BY ordinal_position
");

foreach ($columns as $col) {
    echo "  - {$col->column_name}: {$col->data_type}";
    if ($col->character_maximum_length) {
        echo " ({$col->character_maximum_length})";
    }
    echo "\n";
}

echo "\n📋 Verificando dados na tabela (query direta):\n";
$results = DB::select("SELECT id, nome FROM produtores_rurais LIMIT 3");

foreach ($results as $row) {
    echo "  ID: {$row->id}\n";
    echo "  Nome: {$row->nome}\n";
    echo "  Tipo do ID: " . gettype($row->id) . "\n";
    echo "  ID é null? " . ($row->id === null ? 'SIM' : 'NÃO') . "\n";
    echo "  ---\n";
}

echo "\n📋 Contagem de registros:\n";
$count = DB::table('produtores_rurais')->count();
echo "  Total de produtores: {$count}\n";

$nullIds = DB::table('produtores_rurais')->whereNull('id')->count();
echo "  Produtores com ID NULL: {$nullIds}\n";

if ($nullIds > 0) {
    echo "\n⚠️ PROBLEMA DETECTADO: Existem {$nullIds} registros com ID NULL!\n";
} else {
    echo "\n✅ Todos os IDs estão preenchidos corretamente!\n";
}
