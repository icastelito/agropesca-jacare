<?php

namespace App\Http\Controllers;

use App\Models\Propriedade;
use App\Models\Rebanho;
use App\Models\UnidadeProducao;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class RelatorioController extends Controller
{
    /**
     * Relatório 1: Total de propriedades por município
     * Usa índice idx_propriedades_municipio_uf para GROUP BY
     */
    public function propriedadesPorMunicipio()
    {
        $dados = Propriedade::select('municipio', 'uf', DB::raw('COUNT(*) as total'))
            ->groupBy('municipio', 'uf') // Usa índice idx_propriedades_municipio_uf
            ->orderBy('total', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $dados,
            'total_geral' => $dados->sum('total'),
        ]);
    }

    /**
     * Relatório 2: Total de animais por espécie
     * Usa índice idx_rebanhos_especie_qtd para otimizar GROUP BY e SUM
     * Espécies: Suínos, Caprinos, Bovinos
     */
    public function animaisPorEspecie()
    {
        $dados = Rebanho::select('especie', DB::raw('SUM(quantidade) as total_animais'), DB::raw('COUNT(*) as total_rebanhos'))
            ->groupBy('especie') // Usa índice idx_rebanhos_especie_qtd
            ->orderBy('total_animais', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $dados,
            'total_geral_animais' => $dados->sum('total_animais'),
            'total_geral_rebanhos' => $dados->sum('total_rebanhos'),
        ]);
    }

    /**
     * Relatório 3: Total de hectares por cultura
     * Usa índice idx_unidades_cultura_area para otimizar GROUP BY e SUM
     * Culturas: Laranja Pera, Melancia Crimson Sweet, Goiaba Paluma
     */
    public function hectaresPorCultura()
    {
        $dados = UnidadeProducao::select('nome_cultura', DB::raw('SUM(area_total_ha) as total_hectares'), DB::raw('COUNT(*) as total_unidades'))
            ->groupBy('nome_cultura') // Usa índice idx_unidades_cultura_area
            ->orderBy('total_hectares', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $dados,
            'total_geral_hectares' => $dados->sum('total_hectares'),
            'total_geral_unidades' => $dados->sum('total_unidades'),
        ]);
    }

    /**
     * Página de relatórios (Inertia)
     * Consultas otimizadas com índices compostos
     */
    public function index()
    {
        // Relatório 1: Propriedades por município (usa idx_propriedades_municipio_uf)
        $propriedadesPorMunicipio = Propriedade::select('municipio', 'uf', DB::raw('COUNT(*) as total'))
            ->groupBy('municipio', 'uf')
            ->orderBy('total', 'desc')
            ->get();

        // Relatório 2: Animais por espécie (usa idx_rebanhos_especie_qtd)
        $animaisPorEspecie = Rebanho::select('especie', DB::raw('SUM(quantidade) as total_animais'), DB::raw('COUNT(*) as total_rebanhos'))
            ->groupBy('especie')
            ->orderBy('total_animais', 'desc')
            ->get();

        // Relatório 3: Hectares por cultura (usa idx_unidades_cultura_area)
        $hectaresPorCultura = UnidadeProducao::select('nome_cultura', DB::raw('SUM(area_total_ha) as total_hectares'), DB::raw('COUNT(*) as total_unidades'))
            ->groupBy('nome_cultura')
            ->orderBy('total_hectares', 'desc')
            ->get();

        // Buscar todos os produtores para o dropdown de exportação (usa idx_produtores_nome)
        $produtores = \App\Models\ProdutorRural::select('id', 'nome')
            ->orderBy('nome')
            ->get();

        return Inertia::render('Relatorios/Index', [
            'propriedades_municipio' => $propriedadesPorMunicipio,
            'animais_especie' => $animaisPorEspecie,
            'hectares_cultura' => $hectaresPorCultura,
            'produtores' => $produtores,
        ]);
    }
}
