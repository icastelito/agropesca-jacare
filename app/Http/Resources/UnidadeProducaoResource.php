<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UnidadeProducaoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nome_cultura' => $this->nome_cultura,
            'area_total_ha' => (float) $this->area_total_ha,
            'area_total_formatada' => number_format($this->area_total_ha, 2, ',', '.') . ' ha',
            'coordenadas_geograficas' => $this->coordenadas_geograficas,
            'propriedade_id' => $this->propriedade_id,
            'created_at' => $this->created_at?->format('d/m/Y H:i'),
            'updated_at' => $this->updated_at?->format('d/m/Y H:i'),

            // Relacionamentos
            'propriedade' => new PropriedadeResource($this->whenLoaded('propriedade')),
        ];
    }
}
