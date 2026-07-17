<?php

namespace App\Models\Concerns;

use Illuminate\Support\Str;

/**
 * Genera automaticamente un UUID publico (no usado como PK interna) y lo
 * usa como route key, de forma que las URLs de la API expongan UUIDs en
 * lugar de IDs incrementales, sin perder el rendimiento de los joins con
 * enteros como llave primaria interna.
 */
trait HasUuid
{
    protected static function bootHasUuid(): void
    {
        static::creating(function ($model) {
            $column = $model->uuidColumn();

            if (empty($model->{$column})) {
                $model->{$column} = (string) Str::uuid();
            }
        });
    }

    public function uuidColumn(): string
    {
        return 'uuid';
    }

    public function getRouteKeyName(): string
    {
        return $this->uuidColumn();
    }
}
