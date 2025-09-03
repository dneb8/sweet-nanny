<?php

namespace App\Services;

use App\Http\Requests\Course\CreateCourseRequest;
use App\Http\Requests\Course\UpdateCourseRequest;
use App\Models\Course;

class CourseService
{
    /**
     * Crea un curso
     */
    public function createCourse(CreateCourseRequest $request): Course
    {
        $validated = $request->safe();

        $course = Course::create([
            'name' => $validated->name,
            'organization' => $validated->organization,
            'date' => $validated->date,
            'nanny_id' => $validated->nanny_id ?? null,
        ]);

        return $course;
    }

    /**
     * Actualiza un curso existente
     */
    public function updateCourse(Course $course, UpdateCourseRequest $request): void
    {
        $validated = $request->safe();

        $course->update([
            'name' => $validated->name,
            'organization' => $validated->organization,
            'date' => $validated->date,
            'nanny_id' => $validated->nanny_id ?? $course->nanny_id,
        ]);
    }
}
