<?php

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Propriedade extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'nome',
        'municipio',
        'uf',
        'inscricao_estadual',
        'area_total',
        'data_cadastro',
        'produtor_id',
    ];

    protected $casts = [
        'area_total' => 'decimal:2',
        'data_cadastro' => 'date',
    ];

    /**
     * Relacionamento N:1 com Produtor Rural
     * Uma propriedade pertence a um produtor rural
     */
    public function produtorRural(): BelongsTo
    {
        return $this->belongsTo(ProdutorRural::class, 'produtor_id');
    }

    /**
     * Relacionamento 1:N com Unidades de Produção
     * Uma propriedade pode ter várias unidades de produção
     */
    public function unidadesProducao(): HasMany
    {
        return $this->hasMany(UnidadeProducao::class, 'propriedade_id');
    }

    /**
     * Relacionamento 1:N com Rebanhos
     * Uma propriedade pode ter vários rebanhos
     */
    public function rebanhos(): HasMany
    {
        return $this->hasMany(Rebanho::class, 'propriedade_id');
    }

    /**
     * Relacionamento polimórfico 1:N com Documentos
     * Uma propriedade pode ter vários documentos
     */
    public function documentos(): MorphMany
    {
        return $this->morphMany(Documento::class, 'documentavel');
    }
}
