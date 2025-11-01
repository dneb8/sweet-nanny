<?php

namespace App\Scopes\User;

use Illuminate\Database\Eloquent\Builder;

final readonly class FiltrarPorRole
{
    public function __construct(
        public string|array|null $role = null,
    ) {}

    public function __invoke(Builder $query): Builder
    {
        if (empty($this->role)) {
            return $query;
        }

        $roles = is_array($this->role) ? $this->role : [$this->role];

        return $query->whereHas('roles', function (Builder $q) use ($roles) {
            $q->whereIn('name', $roles);
        });
    }
}
