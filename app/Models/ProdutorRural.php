<?php

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class ProdutorRural extends Model
{
    use HasFactory, HasUuid;

    protected $table = 'produtores_rurais';

    protected $fillable = [
        'nome',
        'cpf_cnpj',
        'telefone',
        'email',
        'endereco',
        'data_cadastro',
    ];

    protected $casts = [
        'data_cadastro' => 'datetime',
    ];

    /**
     * Relacionamento 1:N com Propriedades
     * Um produtor rural pode ter várias propriedades
     */
    public function propriedades(): HasMany
    {
        return $this->hasMany(Propriedade::class, 'produtor_id');
    }

    /**
     * Relacionamento polimórfico 1:N com Documentos
     * Um produtor rural pode ter vários documentos
     */
    public function documentos(): MorphMany
    {
        return $this->morphMany(Documento::class, 'documentavel');
    }
}
