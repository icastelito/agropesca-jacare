<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Propriedade;

$propriedade = Propriedade::first();

if ($propriedade) {
    echo "Propriedade encontrada:\n";
    echo "ID: " . $propriedade->id . "\n";
    echo "Nome: " . $propriedade->nome . "\n";
    echo "\nTodos os atributos:\n";
    print_r($propriedade->toArray());
} else {
    echo "Nenhuma propriedade encontrada no banco de dados.\n";
}
