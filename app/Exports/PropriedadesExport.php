<?php

namespace App\Exports;

use App\Models\Propriedade;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PropriedadesExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return Propriedade::with('produtorRural')->get();
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'ID',
            'Nome da Propriedade',
            'Produtor Rural',
            'CPF/CNPJ',
            'Município',
            'UF',
            'Inscrição Estadual',
            'Área Total (ha)',
            'Data de Cadastro',
        ];
    }

    /**
     * @param mixed $propriedade
     * @return array
     */
    public function map($propriedade): array
    {
        return [
            $propriedade->id,
            $propriedade->nome,
            $propriedade->produtorRural->nome ?? 'N/A',
            $propriedade->produtorRural->cpf_cnpj ?? 'N/A',
            $propriedade->municipio,
            $propriedade->uf,
            $propriedade->inscricao_estadual,
            number_format($propriedade->area_total, 2, ',', '.'),
            $propriedade->created_at->format('d/m/Y'),
        ];
    }

    /**
     * @param Worksheet $sheet
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // Style da primeira linha (cabeçalho)
            1 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }
}
