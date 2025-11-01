<?php

namespace App\Classes\Fetcher;

use App\Classes\QueryHelpers;
use Closure;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class Fetcher
{
    /**
     * Constructor de la clase. Se constituye de un $query y de un $request.
     */
    public function __construct(
        public Builder $query,
        public Request $request,
        public $sortDefaultField = null,
        public $sortDefaultDirection = 'desc',
    ) {}

    /**
     * Método estático que permite instanciar la clase de una forma más
     * legible ~Fetcher::for($query)~, retorna una nueva instancia de la clase.
     */
    public static function for(Builder $query)
    {
        return new static(
            query: $query,
            request: request()
        );
    }

    /**
     * Extrae el valor de búsqueda del request y discrimina los resultados en función
     * de dicho valor. Solamente busca en los campos recibidos "$enabledField", que pueden
     * llegar a incluir campos anidados.
     *
     * @var array<string>, arreglo de campos permitidos para buscar.
     * @var string parámetro del cuál extraer el término de búsqueda desde el querystring.
     */
    public function allowSearch(array $enabledFields, string $fromQuerystringAs = 'searchTerm'): self
    {
        $this->query = QueryHelpers::search(
            query: $this->query,
            fields: $enabledFields,
            value: $this->request->get($fromQuerystringAs)
        );

        return $this;
    }

    /**
     * Permite filtrar mediante ciertos campos. Permitiendo filtrado estricto o no.
     *
     * @var array<string, array>, arreglo de filtros permitidos.
     * @var string parámetro del cuál extraer los filtros desde el querystring.
     */
    public function allowFilters(array $enabledFields, string $fromQuerystringAs = 'filters'): self
    {
        $activeFilters = $this->request->get($fromQuerystringAs) ?? [];

        collect($enabledFields)->each(function ($options, $filterName) use ($activeFilters) {
            $field = $options['as'] ?? '';
            $filterCallback = $options['using'] ?? null;

            if (! array_key_exists($filterName, $activeFilters)) {
                return;
            }

            $filter = new Filter($this->query, $field, $activeFilters[$filterName]);

            if ($filterCallback) {
                $filterCallback($filter);
            }

            $filter->apply();
        });

        return $this;
    }

    /**
     * Se encarga de extraer el método de ordenamiento del request y ejecutarlo en la query dada.
     * Sólo se podrá ordenar pos los campos "$enabledSorts"
     *
     * @var array<string>, arreglo de campos permitidos para ordenar.
     * @var string parámetro del cuál extraer la columna a ordenar desde el querystring.
     * @var string parámetro del cuál extraer la dirección de ordenamiento desde el querystring.
     */
    public function allowSort(array $enabledSorts, string $columnFromQuerystringAs = 'sortBy', string $directionFromQuerystringAs = 'sortDirection'): self
    {
        $sortField = $this->request->has($columnFromQuerystringAs) ? $this->request->get($columnFromQuerystringAs) : '';
        $sortDirection = $this->request->get($directionFromQuerystringAs);

        if (in_array($sortField, $enabledSorts) && $sortDirection) {
            QueryHelpers::sort(
                query: $this->query,
                fields: [$sortField],
                sortDirection: $sortDirection
            );
        } elseif ($this->sortDefaultField) {

            QueryHelpers::sort(
                query: $this->query,
                fields: $this->sortDefaultField,
                sortDirection: $this->sortDefaultDirection
            );
            dd('enableSort', $this->request->all());
        }

        return $this;
    }

    /**
     * Extra los parámetros de paginación del request y los aplica a la instancia de la query.
     * Recibe un $defaultOfsset para aplicar en caso de que no existan parámetros en  el request.
     *
     * @var int cantidad de elementos para paginar por default.
     * @var string nombre de la página a paginar.
     * @var string parámetro del cuál extraer la cantidad de elementos desde el querystring.
     */
    public function paginate(int $defaultOffset = 10, string $pageName = 'page', string $fromQuerystringAs = 'per_page'): LengthAwarePaginator
    {
        $offset = $this->request->get($fromQuerystringAs);

        if ($offset === 'all') {
            $offset = $this->query->count();

        } elseif (! isset($offset)) {
            $offset = $defaultOffset;
        }

        return $this->query->paginate($offset, pageName: $pageName);
    }

    /**
     * Ejecuta y retorna el método get() de la instancia de la query.
     */
    public function get(): Collection
    {
        return $this->query->get();
    }

    /**
     * Retorna el query instanciado.
     */
    public function getQuery(): Builder
    {
        return $this->query;
    }

    /**
     * Permite acceder a la instancia del query y manipularla
     */
    public function touch(Closure $closure): self
    {
        $closure($this);

        return $this;
    }

    /**
     * Se define la forma de ordenar por defecto.
     */
    public function defaultSort(string $field, string $direction = 'desc'): self
    {
        $this->sortDefaultField = [$field];
        $this->sortDefaultDirection = $direction;

        return $this;
    }
}
