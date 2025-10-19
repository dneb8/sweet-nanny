<?php

namespace App\Http\Requests\Bookings;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateBookingRequest extends FormRequest
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

        $tid = data_get($this->booking, 'tutor_id');
        $aid = data_get($this->booking, 'address_id');

        data_set($this->request, 'booking.tutor_id', (int) $tid);
        // 0, "0", "", null => null
        data_set($this->request, 'booking.address_id', empty($aid) ? null : (int) $aid);
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
            'appointments.*.end_date'      => ['required','date','after:start_date'], // <- fix
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
}
