<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RebanhoResource extends JsonResource
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
            'especie' => $this->especie,
            'quantidade' => $this->quantidade,
            'quantidade_formatada' => number_format($this->quantidade, 0, ',', '.') . ' animais',
            'finalidade' => $this->finalidade,
            'data_atualizacao' => $this->data_atualizacao?->toISOString(),
            'data_atualizacao_formatada' => $this->data_atualizacao?->format('d/m/Y H:i'),
            'propriedade_id' => $this->propriedade_id,
            'created_at' => $this->created_at?->toISOString(),
            'created_at_formatada' => $this->created_at?->format('d/m/Y H:i'),
            'updated_at' => $this->updated_at?->toISOString(),

            // Relacionamentos
            'propriedade' => new PropriedadeResource($this->whenLoaded('propriedade')),
        ];
    }
}
