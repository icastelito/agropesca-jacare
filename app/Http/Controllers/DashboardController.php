<?php

namespace App\Http\Controllers;

use App\Models\ProdutorRural;
use App\Models\Propriedade;
use App\Models\Rebanho;
use App\Models\UnidadeProducao;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DashboardController extends Controller
{
    /**
     * Exibe o dashboard com gráficos e estatísticas
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

        return Inertia::render('Dashboard/Index', [
            'produtoresPorMunicipio' => $produtoresPorMunicipio,
            'animaisPorEspecie' => $animaisPorEspecie,
            'cadastrosPorMes' => $cadastrosPorMes,
            'hectaresPorCultura' => $hectaresPorCultura,
            'stats' => $stats,
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

        // Buscar contagem de produtores por mês (usando data_cadastro)
        $produtoresPorMes = ProdutorRural::select(
            DB::raw("TO_CHAR(data_cadastro, 'YYYY-MM') as mes_ano"),
            DB::raw('COUNT(*) as total')
        )
            ->where('data_cadastro', '>=', now()->subMonths(12))
            ->groupBy('mes_ano')
            ->pluck('total', 'mes_ano')
            ->toArray();

        // Buscar contagem de propriedades por mês (usando data_cadastro)
        $propriedadesPorMes = Propriedade::select(
            DB::raw("TO_CHAR(data_cadastro, 'YYYY-MM') as mes_ano"),
            DB::raw('COUNT(*) as total')
        )
            ->where('data_cadastro', '>=', now()->subMonths(12))
            ->groupBy('mes_ano')
            ->pluck('total', 'mes_ano')
            ->toArray();

        // Montar array final com todos os meses (incluindo zeros)
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
