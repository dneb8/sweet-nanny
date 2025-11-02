<?php

namespace App\Classes\Fetcher;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class Filter
{
    /**
     * Las relaciones, en caso de existir, necesarias para llegar al campo a filtrar desde el query dado.
     */
    private null|string|array $relationships = null;

    public function __construct(
        private Builder $query,
        private ?string $field,
        private mixed $value
    ) {
        if ($field) {
            $this->relationships = Str::contains($field, '.') ? Str::beforeLast($field, '.') : null;
            $this->field = Str::afterLast($field, '.');
        }
    }

    /**
     * Determina si el filtro por default es estricta o no (WHERE $search vs WHERE LIKE %$search%).
     */
    private $strict = true;

    /**
     * Determina el scope/s que se debe de aplicar, en caso de necesitarlo.
     */
    private ?array $scopes = null;

    /**
     * Es posible aplicar una custom closure que recibe el término de búsqueda/filtro y lo transforma.
     */
    private ?Closure $transformFilterValue = null;

    /**
     * Cambia el estatus de si el filtro debería de ser estricto o no.
     */
    public function nonStrict(bool $strict = false): self
    {
        $this->strict = $strict;

        return $this;
    }

    /**
     * Asigna los scopes que se deberán de aplicar al query.
     */
    public function usingScope(string|array $scope): self
    {
        if (is_array($scope)) {
            $this->scopes = $scope;

        } else {
            $this->scopes[] = $scope;
        }

        return $this;
    }

    /**
     * Aplica una función al término de búsqueda previo a la aplicación del filtro.
     */
    public function transform(Closure $closure): self
    {
        $this->transformFilterValue = $closure;

        return $this;
    }

    /**
     * Aplica las opciones a un query para filtrar datos.
     */
    public function apply(): Builder
    {
        $transformCallable = $this->transformFilterValue;

        $filterValue = $this->transformFilterValue
            ? $transformCallable($this->value)
            : $this->value;

        if (! empty($this->scopes)) {
            collect($this->scopes)->each(fn ($scope) => $this->query->$scope($filterValue));

            return $this->query;
        }

        if ($filterValue === null) {
            return $this->query;
        }

        $clause = is_array($filterValue) ? 'whereIn' : 'where';
        $operator = (is_array($filterValue) || $this->strict) ? '=' : 'LIKE';
        $search = (is_array($filterValue) || $this->strict)
            ? $filterValue
            : "%$filterValue%";

        if (!empty($this->relationships)) {
            $relationships = is_array($this->relationships) ? implode('.', $this->relationships) : $this->relationships;
            
            return $this->query->whereHas($relationships, function ($query) use ($clause, $operator, $search) {
                $relatedTableName = $query->getModel()->getTable();

                if ($clause === 'whereIn') {
                    $query->$clause("$relatedTableName.{$this->field}", $search);
                } else {
                    $query->$clause("$relatedTableName.{$this->field}", $operator, $search);
                }
            });

        } else {
            if ($clause === 'whereIn') {
                return $this->query->$clause($this->field, $search);
            } else {
                return $this->query->$clause($this->field, $operator, $search);
            }

        }
    }
}
