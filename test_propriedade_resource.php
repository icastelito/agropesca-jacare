<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Propriedade;
use App\Http\Resources\PropriedadeResource;

$propriedade = Propriedade::with(['produtorRural', 'documentos'])->first();

if ($propriedade) {
    echo "Propriedade encontrada:\n";
    echo "ID: " . $propriedade->id . "\n";
    echo "Nome: " . $propriedade->nome . "\n";

    // Criar resource
    $resource = new PropriedadeResource($propriedade);

    echo "\nDados do Resource:\n";
    print_r($resource->toArray(request()));
} else {
    echo "Nenhuma propriedade encontrada no banco de dados.\n";
}
