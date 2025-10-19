<?php

namespace App\Models\Traits;

use Illuminate\Support\Str;

trait HasUuid
{
    /**
     * Boot do trait
     * Gera UUID automaticamente ao criar um novo registro
     */
    protected static function bootHasUuid(): void
    {
        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }

    /**
     * Indica que o ID não é auto-incrementável
     */
    public function getIncrementing(): bool
    {
        return false;
    }

    /**
     * Define o tipo da chave primária como string
     */
    public function getKeyType(): string
    {
        return 'string';
    }
}
