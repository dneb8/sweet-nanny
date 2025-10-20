<?php

namespace App\Http\Requests\Career;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
use App\Enums\Career\StatusEnum;
use App\Enums\Career\DegreeEnum;
use App\Enums\Career\NameCareerEnum;

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
            
            'name' => ['required', new Enum(NameCareerEnum::class)],
            'nanny_id' => ['nullable', 'integer', 'exists:nannies,id'],
            'degree' => ['nullable', new Enum(DegreeEnum::class)],
            'status' => ['nullable', 'string', new Enum(StatusEnum::class)],
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
