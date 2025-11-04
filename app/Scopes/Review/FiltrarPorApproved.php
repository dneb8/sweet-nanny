<?php

namespace App\Scopes\Review;

use Illuminate\Database\Eloquent\Builder;

final readonly class FiltrarPorApproved
{
    public function __construct(
        public string|bool|null $approved = null,
    ) {}

    public function __invoke(Builder $query): Builder
    {
        if ($this->approved === null || $this->approved === '') {
            return $query;
        }

        // Convert string to boolean if needed
        $approvedValue = is_string($this->approved) 
            ? filter_var($this->approved, FILTER_VALIDATE_BOOLEAN)
            : (bool) $this->approved;

        return $query->where('approved', $approvedValue);
    }
}
