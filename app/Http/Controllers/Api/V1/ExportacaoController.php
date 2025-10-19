<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ProdutorRural;
use App\Models\Rebanho;
use App\Exports\PropriedadesExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class ExportacaoController extends Controller
{
    public function propriedadesExcel()
    {
        $nomeArquivo = 'propriedades_' . date('Y-m-d_His') . '.xlsx';
        
        return Excel::download(new PropriedadesExport, $nomeArquivo);
    }

    public function rebanhosPdf($produtorId)
    {
        $produtor = ProdutorRural::with([
            'propriedades.rebanhos' => function ($query) {
                $query->orderBy('especie');
            }
        ])->findOrFail($produtorId);

        $rebanhos = collect();
        foreach ($produtor->propriedades as $propriedade) {
            foreach ($propriedade->rebanhos as $rebanho) {
                $rebanhos->push([
                    'rebanho' => $rebanho,
                    'propriedade' => $propriedade,
                ]);
            }
        }

        $totalAnimais = $rebanhos->sum(fn($item) => $item['rebanho']->quantidade);
        $totalPorEspecie = $rebanhos->groupBy(fn($item) => $item['rebanho']->especie)
            ->map(fn($grupo) => $grupo->sum(fn($item) => $item['rebanho']->quantidade));

        $pdf = Pdf::loadView('pdf.rebanhos', [
            'produtor' => $produtor,
            'rebanhos' => $rebanhos,
            'total_animais' => $totalAnimais,
            'total_por_especie' => $totalPorEspecie,
            'data_geracao' => now()->format('d/m/Y H:i:s'),
        ]);

        $nomeArquivo = 'rebanhos_' . str_replace(' ', '_', $produtor->nome) . '_' . date('Y-m-d_His') . '.pdf';

        return $pdf->download($nomeArquivo);
    }

    public function produtoresExcel()
    {
        return response()->json([
            'success' => true,
            'message' => 'Exportação de produtores em desenvolvimento'
        ], 501);
    }

    public function consolidadoExcel()
    {
        return response()->json([
            'success' => true,
            'message' => 'Exportação consolidada em desenvolvimento'
        ], 501);
    }
}
