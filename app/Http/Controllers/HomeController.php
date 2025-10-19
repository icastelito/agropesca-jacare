<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Models\ProdutorRural;
use App\Models\Propriedade;
use App\Models\UnidadeProducao;
use App\Models\Rebanho;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function index()
    {
        // Buscar estatísticas para o dashboard
        $stats = [
            'total_produtores' => ProdutorRural::count(),
            'total_propriedades' => Propriedade::count(),
            'total_unidades' => UnidadeProducao::count(),
            'total_rebanhos' => Rebanho::count(),
            'total_animais' => Rebanho::sum('quantidade'),
            'total_hectares' => UnidadeProducao::sum('area_total_ha'),
        ];

        return Inertia::render('Home', [
            'stats' => $stats,
        ]);
    }
}
