<?php

namespace App\Http\Requests\Address;

use Illuminate\Foundation\Http\FormRequest;
use App\Enums\Address\TypeEnum;

class CreateAddressRequest extends FormRequest
{
    /**
     * Determina si el usuario está autorizado para hacer este request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Reglas de validación para crear una dirección
     */
    public function rules(): array
    {
        return [
            'postal_code' => ['required', 'string', 'max:10'],
            'street' => ['required', 'string', 'max:255'],
            'neighborhood' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:' . implode(',', TypeEnum::values())],
            'other_type' => ['nullable', 'string', 'max:255'],
            'internal_number' => ['nullable', 'string', 'max:50'],
        ];
    }

    /**
     * Nombres personalizados para los atributos en los mensajes de error
     */
    public function attributes(): array
    {
        return [
            'postal_code' => 'código postal',
            'street' => 'calle',
            'neighborhood' => 'colonia',
            'type' => 'tipo de dirección',
            'other_type' => 'otro tipo de dirección',
            'internal_number' => 'número interno',
        ];
    }
}
