<?php

namespace App\Http\Requests\Address;

use App\Enums\Address\TypeEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class UpdateAddressRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Permite enviar owner_* y los mapea a addressable_*.
     * En update NO son obligatorios, pero si vienen, se normalizan.
     */
    protected function prepareForValidation(): void
    {
        $type = $this->input('addressable_type', $this->input('owner_type'));
        $id = $this->input('addressable_id', $this->input('owner_id'));

        if ($type) {
            $type = str_replace('/', '\\', trim($type));
            if (! str_contains($type, '\\')) {
                $type = 'App\\Models\\'.Str::studly($type);
            }
        }

        $merge = [];
        if ($type) {
            $merge['addressable_type'] = $type;
        }
        if ($id) {
            $merge['addressable_id'] = $id;
        }

        if (! empty($merge)) {
            $this->merge($merge);
        }
    }

    public function rules(): array
    {
        return [
            'postal_code' => ['required', 'string', 'size:5', 'regex:/^\d{5}$/'],
            'street' => ['required', 'string', 'max:255'],
            'neighborhood' => ['required', 'string', 'max:255'],
            'external_number' => ['required', 'string', 'max:50'],
            'internal_number' => ['nullable', 'string', 'max:50'],
            'municipality' => ['nullable', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'type' => ['required', 'in:'.implode(',', TypeEnum::values())],
            'addressable_type' => ['sometimes', 'string'],
            'addressable_id' => ['sometimes', 'integer'],
        ];
    }

    public function attributes(): array
    {
        return [
            'postal_code' => 'código postal',
            'street' => 'calle',
            'neighborhood' => 'colonia',
            'external_number' => 'número exterior',
            'internal_number' => 'número interno',
            'municipality' => 'municipio',
            'state' => 'estado',
            'latitude' => 'latitud',
            'longitude' => 'longitud',
            'type' => 'tipo de dirección',
            'zone' => 'zona',
            'addressable_type' => 'tipo de propietario',
            'addressable_id' => 'propietario',
        ];
    }
}
