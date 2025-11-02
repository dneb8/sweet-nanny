<?php

namespace App\Http\Controllers;

use App\Http\Requests\Course\CreateCourseRequest;
use App\Http\Requests\Course\UpdateCourseRequest;
use App\Models\Course;
use App\Services\CourseService;
use Illuminate\Http\RedirectResponse;

class CourseController extends Controller
{
    /**
     * Crea un nuevo curso
     */
    public function store(CourseService $courseService, CreateCourseRequest $request): RedirectResponse
    {
        // Gate::authorize('create', Course::class);

        $courseService->createCourse($request);

        return redirect()->back()->with('message', [
            'title' => 'Curso creado',
            'description' => 'El curso ha sido creado correctamente.',
        ]);
    }

    /**
     * Actualiza un curso existente
     */
    public function update(CourseService $courseService, UpdateCourseRequest $request, Course $course): RedirectResponse
    {
        // Gate::authorize('edit', Course::class);

        $courseService->updateCourse($course, $request);

        return redirect()->back()->with('message', [
            'title' => 'Curso actualizado',
            'description' => 'El curso ha sido actualizado correctamente.',
        ]);
    }

    /**
     * Elimina un curso
     */
    public function destroy(Course $course): RedirectResponse
    {
        // Gate::authorize('delete', Course::class);

        $course->delete();

        return redirect()->back()->with('message', [
            'title' => 'Curso eliminado',
            'description' => 'El curso ha sido eliminado correctamente.',
        ]);
    }
}
