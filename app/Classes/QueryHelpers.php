<?php

namespace App\Classes;
use App\Classes\Utility;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class QueryHelpers
{
    /**
     * Recibe un query y busca un valor dado en los campos especificados.
     * Ejecuta una búsqueda no estricta (WHERE LIKE), y pueden especificarse campos del query original o de alguna relación existente.
     * 
     * @var Builder $query, query base a la cuál aplicar la búsqueda.
     * @var array<string> $fields, listado de campos sobre los cuales buscar.
     * @var null|string $value, valor a buscar en los campos específicados.
     */
    public static function search (Builder $query, array $fields, null|string $value): Builder
    {
        $value = Utility::sanitizarString($value);

        if ( !$value ) return $query;

        $query->where(function ($subquery) use ($fields, $value) {
            foreach ($fields as $i => $field) {
                $isNested = Str::contains(haystack: $field, needles: '.');
    
                if ($isNested) {
                    $subquery = Self::searchInNested(
                        query: $subquery, 
                        clause: $i === 0 ? 'whereHas' : 'orWhereHas',
                        field: $field, 
                        value: $value
                    );
                } else {
                    $subquery = Self::searchIn(
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
     * @var Builder $query, query base en donde se aplicará la búsqueda.
     * @var string $field, nombre del campo en DB en la cuál realizar la búsqueda.
     * @var null|string $value, valor de búsqueda.
     * @var string $clause, cláusula tipo WHERE a aplicar.
     */
    public static function searchIn (Builder $query, string $field, null|string $value, string $clause = 'where', bool $strict = false): Builder
    {
        $value = $strict ? $value : "%$value%"; 

        return $query->$clause($field, 'LIKE', "%$value%");
    }

    /**
     * Realiza una búsqueda en una relación. 
     * 
     * @var Builder $query, query base en donde se aplicará la búsqueda.
     * @var string $field, nombre del campo en DB mediante notación de punto (e.g address.country.name) en la cuál realizar la búsqueda.
     * @var null|string $value, valor de búsqueda.
     * @var string $clause, clausula tipo WhereHas a aplicar.
     */
    public static function searchInNested (Builder $query, string $field, null|string $value, string $clause = 'whereHas', bool $strict = false): Builder
    {
        $relation = Str::beforeLast($field, '.');
        $relation_field = Str::afterLast($field, '.');
        $value = $strict ? $value : "%$value%";

        return $query->$clause($relation, fn ($relation_query) => $relation_query->where($relation_field, 'LIKE', "%$value%"));
    }

    /**
     * Se encarga de ordenar un query por los parámetros dados.
     */
    public static function sort (Builder $query, String $field, String $sortDirection): Builder
    {
        $query->orderBy($field, $sortDirection);

        return $query;
    }
}