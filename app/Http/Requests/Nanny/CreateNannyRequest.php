<?php

namespace App\Http\Requests\Nanny;

use App\Enums\Nanny\RoleEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateNannyRequest extends FormRequest
{
    /**
     * Determine if the nanny is authorized to make this request.
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
            'surnames' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:nannys,email'],
            'number' => ['required', 'string', 'max:10', 'unique:nannys,number'],
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
            'name' => 'nombre',
            'surnames' => 'apellidos',
            'email' => 'correo electrónico',
            'number' => 'número telefónico',
            'roles' => 'rol',
        ];
    }
}
