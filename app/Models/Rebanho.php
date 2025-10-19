<?php

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Rebanho extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'especie',
        'quantidade',
        'data_cadastro',
        'finalidade',
        'data_atualizacao',
        'propriedade_id',
    ];

    protected $casts = [
        'data_cadastro' => 'date',
        'data_atualizacao' => 'datetime',
        'quantidade' => 'integer',
    ];

    /**
     * Relacionamento N:1 com Propriedade
     * Um rebanho pertence a uma propriedade
     */
    public function propriedade(): BelongsTo
    {
        return $this->belongsTo(Propriedade::class, 'propriedade_id');
    }
}
