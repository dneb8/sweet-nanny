<?php

namespace App\Http\Requests\Career;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCareerRequest extends FormRequest
{
    /**
     * Determine si el usuario está autorizado a realizar esta solicitud.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Reglas de validación para actualizar una carrera
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'nanny_id' => ['nullable', 'integer', 'exists:nannies,id'],
            'degree' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'max:255'],
            'institution' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * Nombres personalizados para mensajes de error
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'nombre de la carrera',
            'nanny_id' => 'niñera asignada',
            'degree' => 'grado',
            'status' => 'estado',
            'institution' => 'institución',
        ];
    }
}
