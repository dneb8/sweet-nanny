<?php

namespace App\Http\Requests\Nanny;

use Illuminate\Foundation\Http\FormRequest;
use App\Enums\Nanny\QualityEnum;

class AsyncQualitiesRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Puedes poner tu lógica de autorización aquí.
        // Por ejemplo, solo el dueño del perfil o un admin puede editar:
        return true;
    }

    public function rules(): array
    {
        return [
            'qualities' => ['required', 'array'],
            'qualities.*' => ['string', 'in:' . implode(',', QualityEnum::values())],
        ];
    }

    public function messages(): array
    {
        return [
            'qualities.required' => 'Debes enviar al menos una cualidad.',
            'qualities.array' => 'Las cualidades deben enviarse como un array.',
            'qualities.*.in' => 'La cualidad seleccionada no es válida.',
        ];
    }
}
