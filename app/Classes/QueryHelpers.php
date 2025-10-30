<?php

namespace App\Classes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class QueryHelpers
{
    /**
     * Recibe un query y busca un valor dado en los campos especificados.
     * Ejecuta una búsqueda no estricta (WHERE LIKE), y pueden especificarse campos del query original o de alguna relación existente.
     *
     * @var Builder, query base a la cuál aplicar la búsqueda.
     * @var array<string>, listado de campos sobre los cuales buscar.
     * @var null|string, valor a buscar en los campos específicados.
     */
    public static function search(Builder $query, array $fields, ?string $value): Builder
    {
        $value = Utility::sanitizarString($value);

        if (! $value) {
            return $query;
        }

        $query->where(function ($subquery) use ($fields, $value) {
            foreach ($fields as $i => $field) {
                $isNested = Str::contains(haystack: $field, needles: '.');

                if ($isNested) {
                    $subquery = self::searchInNested(
                        query: $subquery,
                        clause: $i === 0 ? 'whereHas' : 'orWhereHas',
                        field: $field,
                        value: $value
                    );
                } else {
                    $subquery = self::searchIn(
                        query: $subquery,
                        clause: $i === 0 ? 'where' : 'orWhere',
                        field: $field,
                        value: $value
                    );
                }
            }
        });

        return $query;
    }

    /**
     * Realiza una búsqueda en un campo en particular.
     *
     * @var Builder, query base en donde se aplicará la búsqueda.
     * @var string, nombre del campo en DB en la cuál realizar la búsqueda.
     * @var null|string, valor de búsqueda.
     * @var string, cláusula tipo WHERE a aplicar.
     */
    public static function searchIn(Builder $query, string $field, ?string $value, string $clause = 'where', bool $strict = false): Builder
    {
        $value = $strict ? $value : "%$value%";

        return $query->$clause($field, 'LIKE', "%$value%");
    }

    /**
     * Realiza una búsqueda en una relación.
     *
     * @var Builder, query base en donde se aplicará la búsqueda.
     * @var string, nombre del campo en DB mediante notación de punto (e.g address.country.name) en la cuál realizar la búsqueda.
     * @var null|string, valor de búsqueda.
     * @var string, clausula tipo WhereHas a aplicar.
     */
    public static function searchInNested(Builder $query, string $field, ?string $value, string $clause = 'whereHas', bool $strict = false): Builder
    {
        $relation = Str::beforeLast($field, '.');
        $relation_field = Str::afterLast($field, '.');
        $value = $strict ? $value : "%$value%";

        return $query->$clause($relation, fn ($relation_query) => $relation_query->where($relation_field, 'LIKE', "%$value%"));
    }

    /**
     * Se encarga de ordenar un query por los parámetros dados.
     */
    public static function sort(Builder $query, string $field, string $sortDirection): Builder
    {
        $query->orderBy($field, $sortDirection);

        return $query;
    }
}
