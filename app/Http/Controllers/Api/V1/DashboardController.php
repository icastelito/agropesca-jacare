<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ProdutorRural;
use App\Models\Propriedade;
use App\Models\Rebanho;
use App\Models\UnidadeProducao;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Retorna todos os dados do dashboard em JSON
     */
    public function index()
    {
        // 1. Propriedades por Município (Top 10)
        $produtoresPorMunicipio = Propriedade::select('municipio', DB::raw('COUNT(*) as total'))
            ->whereNotNull('municipio')
            ->where('municipio', '!=', '')
            ->groupBy('municipio')
            ->orderByDesc('total')
            ->limit(10)
            ->get()
            ->map(fn($item) => [
                'municipio' => $item->municipio,
                'total' => (int) $item->total
            ]);

        // 2. Animais por Espécie
        $animaisPorEspecie = Rebanho::select('especie', DB::raw('SUM(quantidade) as total'))
            ->whereNotNull('especie')
            ->groupBy('especie')
            ->orderByDesc('total')
            ->get()
            ->map(fn($item) => [
                'especie' => ucfirst($item->especie),
                'total' => (int) $item->total
            ]);

        // 3. Evolução de Cadastros por Mês (Últimos 12 meses)
        $cadastrosPorMes = $this->getEvolutionData();

        // 4. Hectares por Cultura (Top 8)
        $hectaresPorCultura = UnidadeProducao::select('nome_cultura', DB::raw('SUM(area_total_ha) as hectares'))
            ->whereNotNull('nome_cultura')
            ->where('nome_cultura', '!=', '')
            ->groupBy('nome_cultura')
            ->orderByDesc('hectares')
            ->limit(8)
            ->get()
            ->map(fn($item) => [
                'cultura' => ucfirst($item->nome_cultura),
                'hectares' => round((float) $item->hectares, 2)
            ]);

        // 5. Estatísticas gerais (cards)
        $stats = [
            'total_produtores' => ProdutorRural::count(),
            'total_propriedades' => Propriedade::count(),
            'total_unidades' => UnidadeProducao::count(),
            'total_animais' => Rebanho::sum('quantidade'),
            'total_hectares' => UnidadeProducao::sum('area_total_ha'),
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'totais' => [
                    'produtores' => $stats['total_produtores'],
                    'propriedades' => $stats['total_propriedades'],
                    'unidades' => $stats['total_unidades'],
                    'animais' => $stats['total_animais'],
                    'hectares' => $stats['total_hectares'],
                ],
                'propriedades_por_municipio' => $produtoresPorMunicipio,
                'animais_por_especie' => $animaisPorEspecie,
                'evolucao_cadastros' => $cadastrosPorMes,
                'hectares_por_cultura' => $hectaresPorCultura,
            ]
        ]);
    }

    /**
     * Retorna total de produtores
     */
    public function totalProdutores()
    {
        return response()->json([
            'success' => true,
            'data' => [
                'total' => ProdutorRural::count()
            ]
        ]);
    }

    /**
     * Retorna total de propriedades
     */
    public function totalPropriedades()
    {
        return response()->json([
            'success' => true,
            'data' => [
                'total' => Propriedade::count()
            ]
        ]);
    }

    /**
     * Retorna propriedades por município
     */
    public function propriedadesPorMunicipio()
    {
        $dados = Propriedade::select('municipio', 'uf', DB::raw('COUNT(*) as total'))
            ->whereNotNull('municipio')
            ->where('municipio', '!=', '')
            ->groupBy('municipio', 'uf')
            ->orderByDesc('total')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $dados
        ]);
    }

    /**
     * Retorna animais por espécie
     */
    public function animaisPorEspecie()
    {
        $dados = Rebanho::select('especie', DB::raw('SUM(quantidade) as total'))
            ->whereNotNull('especie')
            ->groupBy('especie')
            ->orderByDesc('total')
            ->get()
            ->map(fn($item) => [
                'especie' => ucfirst($item->especie),
                'total' => (int) $item->total
            ]);

        return response()->json([
            'success' => true,
            'data' => $dados
        ]);
    }

    /**
     * Retorna hectares por cultura
     */
    public function hectaresPorCultura()
    {
        $dados = UnidadeProducao::select('nome_cultura', DB::raw('SUM(area_total_ha) as total_hectares'))
            ->whereNotNull('nome_cultura')
            ->where('nome_cultura', '!=', '')
            ->groupBy('nome_cultura')
            ->orderByDesc('total_hectares')
            ->get()
            ->map(fn($item) => [
                'cultura' => ucfirst($item->nome_cultura),
                'total_hectares' => round((float) $item->total_hectares, 2)
            ]);

        return response()->json([
            'success' => true,
            'data' => $dados
        ]);
    }

    /**
     * Gera dados de evolução de cadastros dos últimos 12 meses
     */
    private function getEvolutionData(): array
    {
        $meses = [];
        $result = [];

        // Gerar últimos 12 meses
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $mesAno = $date->format('Y-m');
            $mesNome = $date->locale('pt_BR')->isoFormat('MMM/YY');

            $meses[] = [
                'mes' => $mesNome,
                'mesAno' => $mesAno,
            ];
        }

        // Buscar contagem de produtores por mês
        $produtoresPorMes = ProdutorRural::select(
            DB::raw("TO_CHAR(data_cadastro, 'YYYY-MM') as mes_ano"),
            DB::raw('COUNT(*) as total')
        )
            ->where('data_cadastro', '>=', now()->subMonths(12))
            ->groupBy('mes_ano')
            ->pluck('total', 'mes_ano')
            ->toArray();

        // Buscar contagem de propriedades por mês
        $propriedadesPorMes = Propriedade::select(
            DB::raw("TO_CHAR(data_cadastro, 'YYYY-MM') as mes_ano"),
            DB::raw('COUNT(*) as total')
        )
            ->where('data_cadastro', '>=', now()->subMonths(12))
            ->groupBy('mes_ano')
            ->pluck('total', 'mes_ano')
            ->toArray();

        // Montar array final
        foreach ($meses as $mes) {
            $result[] = [
                'mes' => $mes['mes'],
                'produtores' => $produtoresPorMes[$mes['mesAno']] ?? 0,
                'propriedades' => $propriedadesPorMes[$mes['mesAno']] ?? 0,
            ];
        }

        return $result;
    }
}
