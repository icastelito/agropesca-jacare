<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Propriedade;
use App\Models\Rebanho;
use App\Models\UnidadeProducao;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RelatorioController extends Controller
{
    public function index()
    {
        $propriedadesPorMunicipio = Propriedade::select('municipio', 'uf', DB::raw('COUNT(*) as total'))
            ->groupBy('municipio', 'uf')
            ->orderBy('total', 'desc')
            ->get();

        $animaisPorEspecie = Rebanho::select('especie', DB::raw('SUM(quantidade) as total_animais'), DB::raw('COUNT(*) as total_rebanhos'))
            ->groupBy('especie')
            ->orderBy('total_animais', 'desc')
            ->get();

        $hectaresPorCultura = UnidadeProducao::select('nome_cultura', DB::raw('SUM(area_total_ha) as total_hectares'), DB::raw('COUNT(*) as total_unidades'))
            ->groupBy('nome_cultura')
            ->orderBy('total_hectares', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'propriedades_municipio' => $propriedadesPorMunicipio,
                'animais_especie' => $animaisPorEspecie,
                'hectares_cultura' => $hectaresPorCultura,
            ]
        ]);
    }

    public function propriedadesPorMunicipio()
    {
        $dados = Propriedade::select('municipio', 'uf', DB::raw('COUNT(*) as total'))
            ->groupBy('municipio', 'uf')
            ->orderBy('total', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $dados,
            'total_geral' => $dados->sum('total'),
        ]);
    }

    public function animaisPorEspecie()
    {
        $dados = Rebanho::select('especie', DB::raw('SUM(quantidade) as total_animais'), DB::raw('COUNT(*) as total_rebanhos'))
            ->groupBy('especie')
            ->orderBy('total_animais', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $dados,
            'total_geral_animais' => $dados->sum('total_animais'),
            'total_geral_rebanhos' => $dados->sum('total_rebanhos'),
        ]);
    }

    public function hectaresPorCultura()
    {
        $dados = UnidadeProducao::select('nome_cultura', DB::raw('SUM(area_total_ha) as total_hectares'), DB::raw('COUNT(*) as total_unidades'))
            ->groupBy('nome_cultura')
            ->orderBy('total_hectares', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $dados,
            'total_geral_hectares' => $dados->sum('total_hectares'),
            'total_geral_unidades' => $dados->sum('total_unidades'),
        ]);
    }
}
