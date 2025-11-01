<?php

namespace App\Http\Requests\Nanny;

use Illuminate\Foundation\Http\FormRequest;

class UpdateNannyProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Para prueba: quitar admin temporalmente
        // Solo la niñera propietaria o una administradora puede actualizar su perfil
        // return $this->user()->is_admin || $this->user()->id === $this->nanny->user_id;
        return true; // temporal para pruebas
    }

    public function rules(): array
    {
        return [
            'bio' => ['nullable', 'string', 'max:1000'],
            'start_date' => ['required', 'date', 'before_or_equal:today'],
        ];
    }

    public function attributes(): array
    {
        return [
            'bio' => 'biografía',
            'start_date' => 'fecha de inicio',
        ];
    }
}
