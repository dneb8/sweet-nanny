<?php

namespace App\Enums\Children;

enum KinkshipEnum: string
{
    case HIJO = 'hijo';
    case SOBRINO = 'sobrino';
    case PRIMO = 'primo';
    case HERMANO = 'hermano';
    case OTRO = 'otro';

    public function label(): string
    {
        return match ($this) {
            static::HIJO => 'Hij@',
            static::SOBRINO => 'Sobrin@',
            static::PRIMO => 'Prim@',
            static::HERMANO => 'Herman@',
            static::OTRO=>'Otro'
        };
    }
}

