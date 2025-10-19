<?php

namespace App\Http\Requests\Bookings;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBookingRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'booking'      => array_merge([
                'tutor_id'   => null,
                'address_id' => null,
                'description'=> '',
                'recurrent'  => false,
                'child_ids'  => [],
            ], (array) $this->input('booking', [])),
            'appointments' => (array) $this->input('appointments', []),
            'address'      => (array) $this->input('address', []),
        ]);

        data_set($this->request, 'booking.tutor_id', (int) data_get($this->booking, 'tutor_id'));
        data_set($this->request, 'booking.address_id', data_get($this->booking, 'address_id') !== null ? (int) data_get($this->booking, 'address_id') : null);
        data_set($this->request, 'booking.recurrent', (bool) data_get($this->booking, 'recurrent'));
    }

    public function rules(): array
    {
        $recurrent = (bool) data_get($this->booking, 'recurrent', false);
        $tutorId   = (int) data_get($this->booking, 'tutor_id');
        $minAppts  = $recurrent ? 2 : 1;
        $maxAppts  = $recurrent ? 10 : 1;

        return [
            'booking.tutor_id'    => ['required','integer','min:1'],
            'booking.description' => ['required','string','min:5'],
            'booking.recurrent'   => ['required','boolean'],
            'booking.child_ids'   => ['required','array','min:1','max:4'],
            'booking.child_ids.*' => ['required','string', Rule::exists('children','id')->where('tutor_id', $tutorId)],

            'appointments'                 => ['required','array',"min:$minAppts","max:$maxAppts"],
            'appointments.*.start_date'    => ['required','date'],
            'appointments.*.end_date'      => ['required','date','after:appointments.*.start_date'],
            'appointments.*.duration'      => ['required','integer','min:1','max:8'],

            'booking.address_id'           => ['nullable','integer','min:1','exists:addresses,id'],
            'address.postal_code'          => ['nullable','string','min:4'],
            'address.street'               => ['nullable','string','min:2'],
            'address.neighborhood'         => ['nullable','string','min:2'],
            'address.type'                 => ['nullable','string'],
            'address.other_type'           => ['nullable','string'],
            'address.internal_number'      => ['nullable','string'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->sometimes(['address.postal_code','address.street','address.neighborhood'], 'required', function ($input) {
            return empty(data_get($input, 'booking.address_id'));
        });
    }

    public function messages(): array
    {
        $rec = (bool) data_get($this->booking, 'recurrent');
        return [
            'appointments.min' => $rec ? 'Agrega al menos 2 citas para un servicio recurrente.' : 'Debes agregar exactamente 1 cita para un servicio fijo.',
            'appointments.max' => $rec ? 'Máximo 10 citas para un servicio recurrente.' : 'Debes agregar exactamente 1 cita para un servicio fijo.',
        ];
    }

    public function attributes(): array
    {
        return [
            'booking.tutor_id'                 => 'tutor',
            'booking.description'              => 'descripción',
            'booking.recurrent'                => 'recurrente',
            'booking.child_ids'                => 'niños',
            'booking.child_ids.*'              => 'niño',
            'appointments'                     => 'citas',
            'appointments.*.start_date'        => 'inicio de cita',
            'appointments.*.end_date'          => 'fin de cita',
            'appointments.*.duration'          => 'duración',
            'booking.address_id'               => 'dirección existente',
            'address.postal_code'              => 'código postal',
            'address.street'                   => 'calle',
            'address.neighborhood'             => 'colonia',
            'address.type'                     => 'tipo de domicilio',
            'address.other_type'               => 'otro tipo',
            'address.internal_number'          => 'número interno',
        ];
    }
}
