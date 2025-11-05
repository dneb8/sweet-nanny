<?php

namespace App\Eloquent\Builders;

use App\Scopes\Review\FiltrarPorApproved;
use Illuminate\Database\Eloquent\Builder;

class ReviewBuilder extends Builder
{
    /**
     * Filtra reviews por estado aprobado/no aprobado.
     */
    public function filtrarPorApproved($approved)
    {
        return $this->tap(new FiltrarPorApproved($approved));
    }
}
