<?php

namespace App\Eloquent\Builders;

use App\Scopes\User\FiltrarPorRole;
use Illuminate\Database\Eloquent\Builder;

class UserBuilder extends Builder
{
    /**
     * Filtra las respuestas por tipo de formulario.
     */
    public function filtrarPorRole($role)
    {
        return $this->tap(new FiltrarPorRole($role));
    }
}
