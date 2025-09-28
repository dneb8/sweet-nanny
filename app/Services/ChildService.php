<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use App\Models\Child;
use App\Models\Tutor;

class ChildService
{
    public function createChild(Request $request): Child
    {
        $data = $request->validate([
            'tutor_id'  => ['required', 'integer', 'exists:tutors,id'],
            'name'      => ['required', 'string', 'min:2', 'max:100'],
            'birthdate' => ['required', 'date'],
            'kinkship'  => ['required', 'string', Rule::in(['hijo','sobrino','primo','hermano','otro'])],
        ], [], [
            'tutor_id'  => 'tutor',
            'name'      => 'nombre',
            'birthdate' => 'fecha de nacimiento',
            'kinkship'  => 'parentesco',
        ]);

        $data['birthdate'] = Carbon::parse($data['birthdate'])->toDateString();

        $child = Child::create($data);
        $child->refresh(); // asegura casts en la misma instancia

        return $child;
    }

    public function updateChild(Child $child, Request $request): Child
    {
        $data = $request->validate([
            'tutor_id'  => ['sometimes', 'required', 'integer', 'exists:tutors,id'],
            'name'      => ['sometimes', 'required', 'string', 'min:2', 'max:100'],
            'birthdate' => ['sometimes', 'required', 'date'],
            'kinkship'  => ['sometimes', 'required', 'string', Rule::in(['hijo','sobrino','primo','hermano','otro'])],
        ], [], [
            'tutor_id'  => 'tutor',
            'name'      => 'nombre',
            'birthdate' => 'fecha de nacimiento',
            'kinkship'  => 'parentesco',
        ]);

        if (array_key_exists('birthdate', $data)) {
            $data['birthdate'] = Carbon::parse($data['birthdate'])->toDateString();
        }
        if (empty($data)) {
            $child->refresh();
            return $child;
        }

        $child->update($data);
        $child->refresh();

        return $child;
    }

    public function deleteChild(Child $child): void
    {
        $child->delete();
    }
}
