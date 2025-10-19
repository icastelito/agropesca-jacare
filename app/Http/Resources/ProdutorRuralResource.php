<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProdutorRuralResource extends JsonResource
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
            'nome' => $this->nome,
            'cpf_cnpj' => $this->cpf_cnpj,
            'telefone' => $this->telefone,
            'email' => $this->email,
            'endereco' => $this->endereco,
            'data_cadastro' => $this->data_cadastro?->format('Y-m-d'),
            'data_cadastro_formatada' => $this->data_cadastro?->format('d/m/Y'),
            'data_cadastro_iso' => $this->data_cadastro?->toIso8601String(),
            'created_at' => $this->created_at?->format('d/m/Y H:i'),
            'updated_at' => $this->updated_at?->format('d/m/Y H:i'),

            // Relacionamentos
            'propriedades' => PropriedadeResource::collection($this->whenLoaded('propriedades')),
            'total_propriedades' => $this->whenCounted('propriedades'),
            'documentos' => $this->whenLoaded('documentos'),
        ];
    }
}
