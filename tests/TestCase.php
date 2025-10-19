<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Artisan;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     * Setup que roda após todos os testes
     */
    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();

        // Roda as seeds após todos os testes para restaurar os dados
        echo "\n\n🌱 Restaurando dados do banco com seeds...\n";
        Artisan::call('db:seed', ['--force' => true]);
        echo "✅ Seeds executadas com sucesso!\n\n";
    }
}
