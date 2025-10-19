<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProdutorRuralController;
use App\Http\Controllers\PropriedadeController;
use App\Http\Controllers\UnidadeProducaoController;
use App\Http\Controllers\RebanhoController;
use App\Http\Controllers\RelatorioController;
use App\Http\Controllers\ExportacaoController;
use App\Http\Controllers\LogViewerController;
use App\Http\Controllers\DocumentoController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Health check para Docker/Railway
Route::get('/health', function () {
    try {
        \Illuminate\Support\Facades\DB::connection()->getPdo();
        return response()->json([
            'status' => 'healthy',
            'database' => 'connected',
            'timestamp' => now()->toIso8601String(),
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'unhealthy',
            'database' => 'disconnected',
            'error' => $e->getMessage(),
            'timestamp' => now()->toIso8601String(),
        ], 503);
    }
});

Route::redirect('/', '/home');
Route::get('/home', [HomeController::class, 'index'])->name('home');

// Guest routes: login / register
Route::middleware('guest')->group(function () {
    Route::get('/login', function () {
        return Inertia::render('Auth/Login');
    })->name('login');
    Route::get('/register', function () {
        return Inertia::render('Auth/Register');
    })->name('register');
    Route::post('/login', [LoginController::class, 'login'])->name('login.post');
    Route::post('/register', [RegisterController::class, 'register'])->name('register.post');
});

// Protected routes: todo o sistema precisa de autenticação
Route::middleware('auth')->group(function () {
    // Auth actions (logout)
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // Dashboard com gráficos
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Rotas CRUD obrigatórias (Inertia)
    // Definindo parâmetros personalizados para evitar conflitos de nomenclatura
    Route::resource('produtores-rurais', ProdutorRuralController::class)->parameters([
        'produtores-rurais' => 'produtorRural'
    ]);
    Route::resource('propriedades', PropriedadeController::class);
    Route::resource('unidades-producao', UnidadeProducaoController::class)->parameters([
        'unidades-producao' => 'unidadeProducao'
    ]);
    Route::resource('rebanhos', RebanhoController::class);
    // Rotas de relatórios
    Route::prefix('relatorios')->group(function () {
        Route::get('/', [RelatorioController::class, 'index'])->name('relatorios.index');
        Route::get('/propriedades-por-municipio', [RelatorioController::class, 'propriedadesPorMunicipio'])->name('relatorios.propriedades-por-municipio');
        Route::get('/animais-por-especie', [RelatorioController::class, 'animaisPorEspecie'])->name('relatorios.animais-por-especie');
        Route::get('/hectares-por-cultura', [RelatorioController::class, 'hectaresPorCultura'])->name('relatorios.hectares-por-cultura');
    });

    // Rotas de exportação
    Route::prefix('exportar')->group(function () {
        Route::get('/propriedades/excel', [ExportacaoController::class, 'propriedadesExcel'])->name('exportar.propriedades-excel');
        Route::get('/rebanhos/pdf/{produtor_id}', [ExportacaoController::class, 'rebanhosPdf'])->name('exportar.rebanhos-pdf');
    });

    // Rotas de visualização de logs
    Route::prefix('logs')->group(function () {
        Route::get('/', [LogViewerController::class, 'index'])->name('logs.index');
        Route::delete('/clear', [LogViewerController::class, 'clear'])->name('logs.clear');
    });

    // Rotas de documentos (upload, download, exclusão)
    Route::prefix('documentos')->group(function () {
        Route::post('/{tipo}/{id}', [DocumentoController::class, 'store'])
            ->name('documentos.store')
            ->where('tipo', 'produtor|propriedade');

        Route::get('/{documento}/download', [DocumentoController::class, 'download'])
            ->name('documentos.download');

        Route::delete('/{documento}', [DocumentoController::class, 'destroy'])
            ->name('documentos.destroy');
    });
});
