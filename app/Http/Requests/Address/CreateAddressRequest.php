<?php

namespace App\Http\Requests\Address;

use App\Enums\Address\TypeEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class CreateAddressRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Mapea owner_* -> addressable_* y normaliza FQCN
     */
    protected function prepareForValidation(): void
    {
        $type = $this->input('addressable_type', $this->input('owner_type'));
        $id   = $this->input('addressable_id',   $this->input('owner_id'));

        if ($type) {
            $type = str_replace('/', '\\', trim($type));
            if (!str_contains($type, '\\')) {
                $type = 'App\\Models\\' . Str::studly($type);
            }
        }

        $this->merge([
            'addressable_type' => $type,
            'addressable_id'   => $id,
        ]);
    }

    public function rules(): array
    {
        return [
            'postal_code'     => ['required', 'string', 'max:10', function ($attribute, $value, $fail) {
                $cp = (int) $value;

                // Validar si el CP está dentro de los intervalos permitidos
                $valid = ($cp >= 44000 && $cp <= 45000)   // Guadalajara
                    || ($cp >= 45000 && $cp <= 45246)     // Zapopan
                    || ($cp >= 45500 && $cp <= 45640)     // Tlaquepaque
                    || ($cp >= 45640 && $cp <= 45680)     // Tlajomulco
                    || ($cp >= 45400 && $cp <= 45430);    // Tonalá


                if (!$valid) {
                    $fail('El código postal no pertenece a una zona válida.');
                }
            }],
            'street'          => ['required', 'string', 'max:255'],
            'neighborhood'    => ['required', 'string', 'max:255'],
            'type'            => ['required', 'in:' . implode(',', TypeEnum::values())],
            'other_type'      => ['nullable', 'string', 'max:255'],
            'internal_number' => ['nullable', 'string', 'max:50'],
            'addressable_type' => ['required', 'string'],
            'addressable_id'   => ['required', 'integer'],
        ];
    }


    public function attributes(): array
    {
        return [
            'postal_code'     => 'código postal',
            'street'          => 'calle',
            'neighborhood'    => 'colonia',
            'type'            => 'tipo de dirección',
            'other_type'      => 'otro tipo de dirección',
            'internal_number' => 'número interno',
            'addressable_type'=> 'tipo de propietario',
            'addressable_id'  => 'propietario',
        ];
    }
}
