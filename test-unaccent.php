<?php

use Illuminate\Support\Facades\DB;

// Teste da função unaccent do PostgreSQL

echo "=== Teste de Busca com Acentos (PostgreSQL unaccent) ===\n\n";

// 1. Testar se unaccent funciona
echo "1. Testando função unaccent():\n";
$result = DB::select("SELECT unaccent('José') as sem_acento, unaccent('São Paulo') as cidade");
echo "   unaccent('José') = " . $result[0]->sem_acento . "\n";
echo "   unaccent('São Paulo') = " . $result[0]->cidade . "\n\n";

// 2. Buscar produtores com nome "José" digitando "jose"
echo "2. Busca: 'jose' (sem acento) deve encontrar 'José' (com acento)\n";
$produtores = DB::table('produtores_rurais')
    ->whereRaw("unaccent(LOWER(nome)) LIKE unaccent(?)", ['%jose%'])
    ->select('id', 'nome')
    ->limit(5)
    ->get();

if ($produtores->count() > 0) {
    echo "   ✅ Encontrados " . $produtores->count() . " produtores:\n";
    foreach ($produtores as $p) {
        echo "      - ID {$p->id}: {$p->nome}\n";
    }
} else {
    echo "   ❌ Nenhum produtor encontrado\n";
}

echo "\n3. Busca: 'sao paulo' deve encontrar 'São Paulo'\n";
$propriedades = DB::table('propriedades')
    ->whereRaw("unaccent(LOWER(municipio)) LIKE unaccent(?)", ['%sao paulo%'])
    ->select('id', 'nome', 'municipio')
    ->limit(5)
    ->get();

if ($propriedades->count() > 0) {
    echo "   ✅ Encontradas " . $propriedades->count() . " propriedades:\n";
    foreach ($propriedades as $p) {
        echo "      - ID {$p->id}: {$p->nome} ({$p->municipio})\n";
    }
} else {
    echo "   ❌ Nenhuma propriedade encontrada\n";
}

echo "\n=== Teste Concluído ===\n";
