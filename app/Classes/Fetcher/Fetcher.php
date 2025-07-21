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
