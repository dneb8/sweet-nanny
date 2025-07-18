<?php

namespace App\Enums\Address;

enum TypeEnum: string
{
    case SUBDIVISION = 'subdivision';      
    case HOUSE = 'house';                  
    case BUILDING = 'building';          
    case APARTMENT = 'apartment';         
    case DUPLEX = 'duplex';              
    case SHOP = 'shop';                   
    case AMUSEMENT_PARK = 'amusement_park';
    case HOTEL = 'hotel';
    case PARTY_VENUE = 'party_venue';
    case CONDOMINIUM = 'condominium';     
    case OTHER = 'other';                  

    public function label(): string
    {
        return match ($this) {
            self::SUBDIVISION => 'Fraccionamiento',
            self::HOUSE => 'Casa',
            self::BUILDING => 'Edificio',
            self::APARTMENT => 'Departamento',
            self::DUPLEX => 'Dúplex',
            self::SHOP => 'Local',
            self::AMUSEMENT_PARK => 'Parque de diversiones',
            self::HOTEL => 'Hotel',
            self::PARTY_VENUE => 'Salón de fiestas',
            self::CONDOMINIUM => 'Condominio',
            self::OTHER => 'Otro',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function labels(): array
    {
        return array_combine(
            self::values(),
            array_map(fn($case) => $case->label(), self::cases())
        );
    }
}
