<?php

namespace App\Classes\Fetcher;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use App\Classes\QueryHelpers;
use Closure;

class Fetcher
{
    public function __construct(
        public Builder $query,
        public Request $request,
        public $sortDefaultField = null,
        public $sortDefaultDirection = 'desc',
    ) {}

    public static function for(Builder $query)
    {
        return new static(
            query: $query,
            request: request()
        );
    }

    public function allowSearch(array $enabledFields, string $fromQuerystringAs = 'searchTerm'): self
    {
        $this->query = QueryHelpers::search(
            query: $this->query,
            fields: $enabledFields,
            value: $this->request->get($fromQuerystringAs)
        );

        return $this;
    }

    public function allowFilters(array $enabledFields, string $fromQuerystringAs = 'filters'): self
    {
        $activeFilters = $this->request->get($fromQuerystringAs) ?? [];

        collect($enabledFields)->each(function ($options, $filterName) use ($activeFilters) {
            $field = $options['as'] ?? '';
            $filterCallback = $options['using'] ?? null;

            if (!array_key_exists($filterName, $activeFilters)) return;

            $filter = new Filter($this->query, $field, $activeFilters[$filterName]);

            if ($filterCallback) {
                $filterCallback($filter);
            }

            $filter->apply();
        });

        return $this;
    }

    public function allowSort(array $enabledSorts, string $columnFromQuerystringAs = 'sortBy', string $directionFromQuerystringAs = 'sortDirection'): self
    {
        $sortField = $this->request->has($columnFromQuerystringAs)
            ? $this->request->get($columnFromQuerystringAs)
            : '';

        $sortDirection = $this->request->get($directionFromQuerystringAs);

        if (in_array($sortField, $enabledSorts) && $sortDirection) {
            QueryHelpers::sort(
                query: $this->query,
                field: $sortField,
                sortDirection: $sortDirection
            );
        } elseif ($this->sortDefaultField) {
            QueryHelpers::sort(
                query: $this->query,
                field: $this->sortDefaultField,
                sortDirection: $this->sortDefaultDirection
            );
        }

        return $this;
    }

    public function paginate(int $defaultOffset = 10, string $pageName = "page", string $fromQuerystringAs = 'per_page'): LengthAwarePaginator
    {
        $offset = $this->request->get($fromQuerystringAs);

        if ($offset === 'all') {
            $offset = $this->query->count();
        } elseif (!isset($offset)) {
            $offset = $defaultOffset;
        }

        return $this->query->paginate($offset, pageName: $pageName);
    }

    public function get(): Collection
    {
        return $this->query->get();
    }

    public function getQuery(): Builder
    {
        return $this->query;
    }

    public function touch(Closure $closure): self
    {
        $closure($this);

        return $this;
    }

    public function defaultSort(string $field, string $direction = 'desc'): self
    {
        $this->sortDefaultField = $field;
        $this->sortDefaultDirection = $direction;

        return $this;
    }
}
