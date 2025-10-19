<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\ProdutorRuralController;
use App\Http\Controllers\Api\V1\PropriedadeController;
use App\Http\Controllers\Api\V1\UnidadeProducaoController;
use App\Http\Controllers\Api\V1\RebanhoController;
use App\Http\Controllers\Api\V1\RelatorioController;
use App\Http\Controllers\Api\V1\ExportacaoController;
use App\Http\Controllers\Api\V1\DocumentoController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Rotas públicas de autenticação
Route::prefix('v1')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);

    // Rotas protegidas com Sanctum
    Route::middleware('auth:sanctum')->group(function () {
        // Auth
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/refresh', [AuthController::class, 'refresh']);

        // Dashboard
        Route::get('/dashboard', [DashboardController::class, 'index']);
        Route::get('/dashboard/total-produtores', [DashboardController::class, 'totalProdutores']);
        Route::get('/dashboard/total-propriedades', [DashboardController::class, 'totalPropriedades']);
        Route::get('/dashboard/propriedades-por-municipio', [DashboardController::class, 'propriedadesPorMunicipio']);
        Route::get('/dashboard/animais-por-especie', [DashboardController::class, 'animaisPorEspecie']);
        Route::get('/dashboard/hectares-por-cultura', [DashboardController::class, 'hectaresPorCultura']);

        // Produtores Rurais - CRUD completo
        Route::apiResource('produtores-rurais', ProdutorRuralController::class)->parameters([
            'produtores-rurais' => 'produtorRural'
        ]);

        // Propriedades - CRUD completo
        Route::apiResource('propriedades', PropriedadeController::class);

        // Unidades de Produção - CRUD completo
        Route::apiResource('unidades-producao', UnidadeProducaoController::class)->parameters([
            'unidades-producao' => 'unidadeProducao'
        ]);

        // Rebanhos - CRUD completo
        Route::apiResource('rebanhos', RebanhoController::class);

        // Relatórios
        Route::prefix('relatorios')->group(function () {
            Route::get('/', [RelatorioController::class, 'index']);
            Route::get('/propriedades-por-municipio', [RelatorioController::class, 'propriedadesPorMunicipio']);
            Route::get('/animais-por-especie', [RelatorioController::class, 'animaisPorEspecie']);
            Route::get('/hectares-por-cultura', [RelatorioController::class, 'hectaresPorCultura']);
        });

        // Exportações
        Route::prefix('exportar')->group(function () {
            Route::get('/propriedades/excel', [ExportacaoController::class, 'propriedadesExcel']);
            Route::get('/rebanhos/pdf/{produtor_id}', [ExportacaoController::class, 'rebanhosPdf']);
            Route::get('/produtores/excel', [ExportacaoController::class, 'produtoresExcel']);
            Route::get('/consolidado/excel', [ExportacaoController::class, 'consolidadoExcel']);
        });

        // Logs
        Route::prefix('logs')->group(function () {
            Route::get('/', function (Request $request) {
                // Simulação básica - você pode melhorar depois
                return response()->json([
                    'success' => true,
                    'data' => []
                ]);
            });
            Route::delete('/', function (Request $request) {
                return response()->json([
                    'success' => true,
                    'message' => 'Logs limpos com sucesso'
                ]);
            });
        });

        // Documentos (upload, download, exclusão)
        Route::prefix('documentos')->group(function () {
            Route::post('/{tipo}/{id}', [DocumentoController::class, 'store'])
                ->where('tipo', 'produtor|propriedade');
            Route::get('/{documento}/download', [DocumentoController::class, 'download']);
            Route::delete('/{documento}', [DocumentoController::class, 'destroy']);
        });
    });
});
