<?php

namespace App\Http\Requests\Bookings;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class CreateBookingRequest extends FormRequest
{
    // Si quieres cortar validación al primer error, cámbialo a true
    protected $stopOnFirstFailure = false;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        // 1) Tomar el payload entrante con defaults seguros
        $incomingBooking      = (array) $this->input('booking', []);
        $incomingAppointments = (array) $this->input('appointments', []);
        $incomingAddress      = (array) $this->input('address', []);

        // 2) Normalizar booking.* con defaults
        $booking = array_merge([
            'tutor_id'    => null,
            'address_id'  => null,
            'description' => '',
            'recurrent'   => false,
            'children'    => [],
        ], $incomingBooking);

        // 3) Tipos fuertes y limpieza
        $tutorId   = (int) data_get($booking, 'tutor_id');
        $addressId = data_get($booking, 'address_id');
        $addressId = empty($addressId) ? null : (int) $addressId;

        // Trim a la descripción
        $description = (string) data_get($booking, 'description', '');
        $description = Str::of($description)->trim()->value();

        // Boolean fiable
        $recurrent = (bool) data_get($booking, 'recurrent', false);

        // 4) children puede venir como array de objetos o IDs; lo reducimos a IDs(string)
        $childrenRaw = (array) data_get($booking, 'children', []);
        $childrenIds = array_values(array_filter(array_map(function ($item) {
            // objetos con id
            if (is_array($item) && array_key_exists('id', $item)) {
                return (string) $item['id'];
            }
            // modelos/DTOs con ->id
            if (is_object($item) && isset($item->id)) {
                return (string) $item->id;
            }
            // id escalar
            if (is_scalar($item) && $item !== '') {
                return (string) $item;
            }
            return null;
        }, $childrenRaw)));

        // 5) Asegurar estructura de arrays
        $appointments = array_values($incomingAppointments);
        $address      = (array) $incomingAddress;

        // 6) Volcar normalización al request interno
        $this->merge([
            'booking' => [
                'tutor_id'    => $tutorId,
                'address_id'  => $addressId,
                'description' => $description,
                'recurrent'   => $recurrent,
                // IMPORTANTE: ya normalizado a IDs string
                'children'    => $childrenIds,
            ],
            'appointments' => $appointments,
            'address'      => $address,
        ]);
    }

    public function rules(): array
    {
        $recurrent = (bool) data_get($this->booking, 'recurrent', false);
        $tutorId   = (int) data_get($this->booking, 'tutor_id');
        $minAppts  = $recurrent ? 2 : 1;
        $maxAppts  = $recurrent ? 10 : 1;

        return [
            'booking.tutor_id'      => ['required', 'integer', 'min:1'],
            'booking.description'   => ['required', 'string', 'min:5'],
            'booking.recurrent'     => ['required', 'boolean'],

            // children como arreglo de IDs string, 1..4, sin duplicados y existentes para ese tutor
            'booking.children'      => ['required', 'array', 'min:1', 'max:4'],
            'booking.children.*'    => [
                'required',
                'string',
                'distinct',
                Rule::exists('children', 'id')->where(fn ($q) => $q->where('tutor_id', $tutorId)),
            ],

            // citas según recurrent
            'appointments'                 => ['required', 'array', "min:$minAppts", "max:$maxAppts"],
            'appointments.*.start_date'    => ['required', 'date'],
            'appointments.*.end_date'      => ['required', 'date', 'after:start_date'],
            'appointments.*.duration'      => ['required', 'integer', 'min:1', 'max:8'],

            // dirección: o address_id válido o se piden campos mínimos (se fuerza con sometimes)
            'booking.address_id'           => ['nullable', 'integer', 'min:1', 'exists:addresses,id'],
            'address.postal_code'          => ['nullable', 'string', 'min:4'],
            'address.street'               => ['nullable', 'string', 'min:2'],
            'address.neighborhood'         => ['nullable', 'string', 'min:2'],
            'address.type'                 => ['nullable', 'string'],
            'address.other_type'           => ['nullable', 'string'],
            'address.internal_number'      => ['nullable', 'string'],
        ];
    }

    public function withValidator($validator): void
    {
        // Si NO hay address_id, pedimos los 3 campos mínimos de dirección
        $validator->sometimes(
            ['address.postal_code', 'address.street', 'address.neighborhood'],
            'required',
            function ($input) {
                $addrId = data_get($input, 'booking.address_id');
                return empty($addrId);
            }
        );

        // Chequeos defensivos adicionales
        $validator->after(function ($v) {
            $data = $this->validated() ?: $this->all();

            // Asegurar al menos 1 child tras normalización
            $children = (array) data_get($data, 'booking.children', []);
            if (count($children) < 1) {
                $v->errors()->add('booking.children', 'Selecciona al menos 1 niño.');
            }

            // Confirmar pertenencia de children al tutor (ya lo hace exists(where tutor_id), esto es redundante)
            // también puedes validar duplicados aquí, pero usamos 'distinct' en las reglas.

            // Validación defensiva de fechas (la regla 'after:start_date' ya lo hace por ítem)
            $appts = (array) data_get($data, 'appointments', []);
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
        return [
            'booking.tutor_id.required'      => 'Debes indicar el tutor.',
            'booking.tutor_id.integer'       => 'El tutor es inválido.',
            'booking.tutor_id.min'           => 'El tutor es inválido.',

            'booking.description.required'   => 'Agrega una descripción.',
            'booking.description.min'        => 'La descripción debe tener al menos :min caracteres.',

            'booking.recurrent.required'     => 'Debes indicar si es recurrente o no.',
            'booking.recurrent.boolean'      => 'El valor de recurrente es inválido.',

            'booking.children.required'      => 'Selecciona al menos 1 niño.',
            'booking.children.array'         => 'Formato de niños inválido.',
            'booking.children.min'           => 'Selecciona al menos 1 niño.',
            'booking.children.max'           => 'Máximo puedes seleccionar :max niños.',
            'booking.children.*.required'    => 'Id de niño faltante.',
            'booking.children.*.string'      => 'Id de niño inválido.',
            'booking.children.*.distinct'    => 'No repitas el mismo niño.',
            'booking.children.*.exists'      => 'Algún niño no existe o no pertenece al tutor.',

            'appointments.required'          => 'Debes agregar al menos una cita.',
            'appointments.array'             => 'Formato de citas inválido.',
            'appointments.min'               => 'Número de citas insuficiente.',
            'appointments.max'               => 'Número de citas excedido.',
            'appointments.*.start_date.*'    => 'La fecha/hora de inicio es inválida o faltante.',
            'appointments.*.end_date.required' => 'La fecha/hora de término es obligatoria.',
            'appointments.*.end_date.after'  => 'La hora de término debe ser posterior a la de inicio.',
            'appointments.*.duration.*'      => 'La duración debe ser un entero entre 1 y 8 horas.',

            'booking.address_id.exists'      => 'La dirección seleccionada no existe.',
            'address.postal_code.required'   => 'El código postal es obligatorio si no eliges una dirección.',
            'address.street.required'        => 'La calle es obligatoria si no eliges una dirección.',
            'address.neighborhood.required'  => 'La colonia es obligatoria si no eliges una dirección.',
        ];
    }

    public function attributes(): array
    {
        return [
            'booking.tutor_id'        => 'tutor',
            'booking.description'     => 'descripción',
            'booking.recurrent'       => 'recurrente',
            'booking.children'        => 'niños',
            'booking.children.*'      => 'niño',
            'appointments'            => 'citas',
            'appointments.*.start_date' => 'inicio de la cita',
            'appointments.*.end_date'   => 'fin de la cita',
            'appointments.*.duration'   => 'duración',
            'booking.address_id'      => 'dirección',
            'address.postal_code'     => 'código postal',
            'address.street'          => 'calle',
            'address.neighborhood'    => 'colonia',
            'address.type'            => 'tipo de dirección',
            'address.other_type'      => 'otro tipo',
            'address.internal_number' => 'número interior',
        ];
    }
}
