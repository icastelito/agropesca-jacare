<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;

echo "=== CORREÇÃO DE EMAILS PARA MINÚSCULAS ===\n\n";

$users = User::all();
$updated = 0;

foreach ($users as $user) {
    $emailLower = strtolower($user->email);

    if ($user->email !== $emailLower) {
        echo "Atualizando: {$user->email} -> {$emailLower}\n";
        $user->email = $emailLower;
        $user->save();
        $updated++;
    }
}

echo "\n=== CONCLUSÃO ===\n";
echo "Total de usuários: " . $users->count() . "\n";
echo "Emails atualizados: {$updated}\n";
echo "Emails já em minúsculas: " . ($users->count() - $updated) . "\n";
