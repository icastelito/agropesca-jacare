<?php

namespace App\Http\Controllers;

use App\Models\ProdutorRural;
use App\Models\Rebanho;
use App\Exports\PropriedadesExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class ExportacaoController extends Controller
{
    /**
     * Exportar propriedades para Excel (.xlsx)
     */
    public function propriedadesExcel()
    {
        $nomeArquivo = 'propriedades_' . date('Y-m-d_His') . '.xlsx';

        return Excel::download(new PropriedadesExport, $nomeArquivo);
    }

    /**
     * Exportar rebanhos por produtor para PDF
     */
    public function rebanhosPdf($produtorId)
    {
        $produtor = ProdutorRural::with([
            'propriedades.rebanhos' => function ($query) {
                $query->orderBy('especie');
            }
        ])->findOrFail($produtorId);

        // Coletar todos os rebanhos de todas as propriedades do produtor
        $rebanhos = collect();
        foreach ($produtor->propriedades as $propriedade) {
            foreach ($propriedade->rebanhos as $rebanho) {
                $rebanhos->push([
                    'rebanho' => $rebanho,
                    'propriedade' => $propriedade,
                ]);
            }
        }

        // Totalizadores
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

    /**
     * Visualizar PDF de rebanhos no navegador (para desenvolvimento/preview)
     */
    public function rebanhosPdfPreview($produtorId)
    {
        $produtor = ProdutorRural::with([
            'propriedades.rebanhos' => function ($query) {
                $query->orderBy('especie');
            }
        ])->findOrFail($produtorId);

        // Coletar todos os rebanhos de todas as propriedades do produtor
        $rebanhos = collect();
        foreach ($produtor->propriedades as $propriedade) {
            foreach ($propriedade->rebanhos as $rebanho) {
                $rebanhos->push([
                    'rebanho' => $rebanho,
                    'propriedade' => $propriedade,
                ]);
            }
        }

        // Totalizadores
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

        return $pdf->stream();
    }
}
