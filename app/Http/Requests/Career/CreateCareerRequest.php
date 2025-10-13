<?php

namespace App\Http\Requests\Career;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
use App\Enums\Career\StatusEnum;

class CreateCareerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'nanny_id' => ['nullable', 'integer', 'exists:nannies,id'],
            'degree' => ['nullable', 'string', 'max:255'],
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
