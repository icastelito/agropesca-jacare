<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PropriedadeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = [
            'id' => $this->id,
            'nome' => $this->nome,
            'municipio' => $this->municipio,
            'uf' => $this->uf,
            'inscricao_estadual' => $this->inscricao_estadual,
            'area_total' => (float) $this->area_total,
            'area_total_formatada' => number_format($this->area_total, 2, ',', '.') . ' ha',
            'produtor_id' => $this->produtor_id,
            'data_cadastro' => $this->data_cadastro?->format('d/m/Y'),
            'data_cadastro_iso' => $this->data_cadastro?->format('Y-m-d'),
            'created_at' => $this->created_at?->format('d/m/Y H:i'),
            'updated_at' => $this->updated_at?->format('d/m/Y H:i'),
        ];

        // Adicionar relacionamentos apenas se estiverem carregados
        if ($this->relationLoaded('produtorRural') && $this->produtorRural) {
            $data['produtor_rural'] = [
                'id' => $this->produtorRural->id,
                'nome' => $this->produtorRural->nome,
            ];
        }

        if ($this->relationLoaded('unidadesProducao')) {
            $data['unidades_producao'] = $this->unidadesProducao->map(function ($unidade) {
                return [
                    'id' => $unidade->id,
                    'nome_cultura' => $unidade->nome_cultura,
                    'area_total_ha' => $unidade->area_total_ha,
                ];
            });
        }

        if ($this->relationLoaded('rebanhos')) {
            $data['rebanhos'] = $this->rebanhos->map(function ($rebanho) {
                return [
                    'id' => $rebanho->id,
                    'especie' => $rebanho->especie,
                    'quantidade_animais' => $rebanho->quantidade_animais,
                ];
            });
        }

        if ($this->relationLoaded('documentos')) {
            $data['documentos'] = $this->documentos->map(function ($doc) {
                return [
                    'id' => $doc->id,
                    'nome_original' => $doc->nome_original,
                    'nome_arquivo' => $doc->nome_arquivo,
                    'tipo' => $doc->tipo,
                    'tamanho' => $doc->tamanho,
                    'categoria' => $doc->categoria,
                    'url' => $doc->url,
                    'tamanho_formatado' => $doc->tamanho_formatado,
                    'is_imagem' => $doc->is_imagem,
                    'is_pdf' => $doc->is_pdf,
                    'created_at' => $doc->created_at?->format('d/m/Y H:i'),
                ];
            })->values()->toArray();
        }

        // Adicionar contagens se disponíveis
        if ($this->resource->relationLoaded('unidadesProducao')) {
            $data['total_unidades'] = $this->unidades_producao_count ?? $this->unidadesProducao->count();
        }

        if ($this->resource->relationLoaded('rebanhos')) {
            $data['total_rebanhos'] = $this->rebanhos_count ?? $this->rebanhos->count();
        }

        return $data;
    }
}
