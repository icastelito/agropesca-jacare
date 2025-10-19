<?php

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UnidadeProducao extends Model
{
    use HasFactory, HasUuid;

    protected $table = 'unidades_producao';

    protected $fillable = [
        'nome_cultura',
        'area_total_ha',
        'data_cadastro',
        'coordenadas_geograficas',
        'propriedade_id',
    ];

    protected $casts = [
        'area_total_ha' => 'decimal:2',
        'data_cadastro' => 'date',
    ];

    /**
     * Relacionamento N:1 com Propriedade
     * Uma unidade de produção pertence a uma propriedade
     */
    public function propriedade(): BelongsTo
    {
        return $this->belongsTo(Propriedade::class, 'propriedade_id');
    }
}
