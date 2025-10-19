<?php

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Storage;

class Documento extends Model
{
    use HasFactory, HasUuid;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nome_original',
        'nome_arquivo',
        'tipo',
        'tamanho',
        'categoria',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'url',
        'tamanho_formatado',
        'is_imagem',
        'is_pdf',
    ];

    /**
     * Relacionamento polimórfico
     * Um documento pode pertencer a qualquer entidade (Produtor, Propriedade, etc)
     */
    public function documentavel(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Accessor para obter a URL completa do documento
     */
    public function getUrlAttribute(): string
    {
        return Storage::url('documentos/' . $this->nome_arquivo);
    }

    /**
     * Accessor para obter o tamanho formatado em KB ou MB
     */
    public function getTamanhoFormatadoAttribute(): string
    {
        $bytes = $this->tamanho;

        if ($bytes < 1024) {
            return $bytes . ' bytes';
        } elseif ($bytes < 1048576) {
            return round($bytes / 1024, 2) . ' KB';
        } else {
            return round($bytes / 1048576, 2) . ' MB';
        }
    }

    /**
     * Accessor para verificar se é uma imagem
     */
    public function getIsImagemAttribute(): bool
    {
        return in_array($this->tipo, [
            'image/jpeg',
            'image/jpg',
            'image/png',
        ]);
    }

    /**
     * Accessor para verificar se é um PDF
     */
    public function getIsPdfAttribute(): bool
    {
        return $this->tipo === 'application/pdf';
    }
}
