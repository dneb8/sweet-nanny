<?php

namespace App\Http\Requests\Bookings;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UpdateBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        // 1) Tomar payload entrante con defaults
        $incomingBooking = (array) $this->input('booking', []);
        $incomingAppointments = (array) $this->input('appointments', []);
        $incomingAddress = (array) $this->input('address', []);

        // 2) Normalizar booking con defaults
        $booking = array_merge([
            'tutor_id' => null,
            'address_id' => null,
            'description' => '',
            'recurrent' => false,
            'child_ids' => [],
            'qualities' => [],
            'careers' => [],
            'courses' => [],
        ], $incomingBooking);

        // 3) Tipos fuertes y limpieza
        $tutorId = (int) data_get($booking, 'tutor_id');
        $addressId = data_get($booking, 'address_id');
        $addressId = empty($addressId) ? null : (int) $addressId;

        $description = (string) data_get($booking, 'description', '');
        $description = Str::of($description)->trim()->value();

        $recurrent = (bool) data_get($booking, 'recurrent', false);

        // 4) Normalizar child_ids a ARRAY DE STRINGS
        //    (acepta objetos con id, números, strings)
        $childIdsRaw = (array) data_get($booking, 'child_ids', []);
        $childIds = array_values(array_filter(array_map(function ($item) {
            if (is_array($item) && array_key_exists('id', $item)) {
                return (string) $item['id'];
            }
            if (is_object($item) && isset($item->id)) {
                return (string) $item->id;
            }
            if (is_scalar($item) && $item !== '') {
                return (string) $item;
            }

            return null;
        }, $childIdsRaw)));

        // 5) Volcar normalización al request interno
        $this->merge([
            'booking' => [
                'tutor_id' => $tutorId,
                'address_id' => $addressId,
                'description' => $description,
                'recurrent' => $recurrent,
                'child_ids' => $childIds, // <-- ya normalizado a strings
                'qualities' => (array) data_get($booking, 'qualities', []),
                'careers' => (array) data_get($booking, 'careers', []),
                'courses' => (array) data_get($booking, 'courses', []),
            ],
            'appointments' => array_values($incomingAppointments),
            'address' => (array) $incomingAddress,
        ]);
    }

    public function rules(): array
    {
        $recurrent = (bool) data_get($this->booking, 'recurrent', false);
        $tutorId = (int) data_get($this->booking, 'tutor_id');
        $minAppts = $recurrent ? 2 : 1;
        $maxAppts = $recurrent ? 10 : 1;

        return [
            'booking.tutor_id' => ['required', 'integer', 'min:1'],
            'booking.description' => ['required', 'string', 'min:5'],
            'booking.recurrent' => ['required', 'boolean'],

            // child_ids ya vienen como strings; validamos array y cada item existe para ese tutor
            'booking.child_ids' => ['required', 'array', 'min:1', 'max:4'],
            'booking.child_ids.*' => [
                'required',
                'string', // ahora sí, 100% string
                Rule::exists('children', 'id')->where(fn ($q) => $q->where('tutor_id', $tutorId)),
            ],

            'appointments' => ['required', 'array', "min:$minAppts", "max:$maxAppts"],
            'appointments.*.start_date' => ['required', 'date'],
            'appointments.*.end_date' => ['required', 'date', 'after:start_date'],
            'appointments.*.duration' => ['required', 'integer', 'min:1', 'max:8'],

            'booking.address_id' => ['nullable', 'integer', 'min:1', 'exists:addresses,id'],
            'address.postal_code' => ['nullable', 'string', 'min:4'],
            'address.street' => ['nullable', 'string', 'min:2'],
            'address.neighborhood' => ['nullable', 'string', 'min:2'],
            'address.type' => ['nullable', 'string'],
            'address.other_type' => ['nullable', 'string'],
            'address.internal_number' => ['nullable', 'string'],

            'booking.qualities' => ['nullable', 'array'],
            'booking.qualities.*' => ['string'],
            'booking.careers' => ['nullable', 'array'],
            'booking.careers.*' => ['string'],
            'booking.courses' => ['nullable', 'array'],
            'booking.courses.*' => ['string'],
        ];
    }

    public function withValidator($validator): void
    {
        // Si no hay address_id pedimos mínimos de address
        $validator->sometimes(['address.postal_code', 'address.street', 'address.neighborhood'], 'required', function ($input) {
            return empty(data_get($input, 'booking.address_id'));
        });

        // Validación defensiva adicional de citas (por si difieren timezones)
        $validator->after(function ($v) {
            $appts = (array) data_get($this->all(), 'appointments', []);
            foreach ($appts as $i => $a) {
                $s = data_get($a, 'start_date');
                $e = data_get($a, 'end_date');
                if ($s && $e && strtotime($e) <= strtotime($s)) {
                    $v->errors()->add("appointments.$i.end_date", 'La hora de término debe ser posterior a la de inicio.');
                }
            }
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
            'booking.tutor_id' => 'tutor',
            'booking.description' => 'descripción',
            'booking.recurrent' => 'recurrente',
            'booking.child_ids' => 'niños',
            'booking.child_ids.*' => 'niño',
            'appointments' => 'citas',
            'appointments.*.start_date' => 'inicio de cita',
            'appointments.*.end_date' => 'fin de cita',
            'appointments.*.duration' => 'duración',
            'booking.address_id' => 'dirección existente',
            'address.postal_code' => 'código postal',
            'address.street' => 'calle',
            'address.neighborhood' => 'colonia',
            'address.type' => 'tipo de domicilio',
            'address.other_type' => 'otro tipo',
            'address.internal_number' => 'número interno',
        ];
    }
}
