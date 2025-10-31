<?php

namespace App\Http\Requests\BookingAppointment;

use Illuminate\Foundation\Http\FormRequest;

class AssignNannyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization is handled in the controller via policy
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Route parameters are already validated by Laravel's route model binding
            // No additional validation needed for this request
        ];
    }
}
