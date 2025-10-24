<?php

namespace App\Http\Requests\Course;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
use App\Enums\Course\NameEnum;

class UpdateCourseRequest extends FormRequest
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
            'name' => ['required', new Enum(NameEnum::class)],
            'organization' => ['nullable', 'string', 'max:255'],
            'date' => ['required', 'date'],
            'nanny_id' => ['nullable', 'integer', 'exists:nannies,id'], 
        ];
    }

    /**
     * Obtiene nombre de atributos personalizados para mensajes de errores.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'nombre del curso',
            'organization' => 'organización',
            'date' => 'fecha',
            'nanny_id' => 'niñera asignada',
        ];
    }
}
