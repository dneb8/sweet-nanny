<?php

namespace App\Http\Controllers;

use App\Enums\Career\DegreeEnum;
use App\Enums\Course\NameEnum as CourseNameEnum;
use App\Enums\Nanny\QualityEnum;
use Illuminate\Http\JsonResponse;

class EnumController extends Controller
{
    /**
     * Get all qualities enum values
     */
    public function qualities(): JsonResponse
    {
        return response()->json([
            'values' => QualityEnum::values(),
            'labels' => QualityEnum::labels(),
        ]);
    }

    /**
     * Get all degree enum values
     */
    public function degrees(): JsonResponse
    {
        return response()->json([
            'values' => DegreeEnum::values(),
            'labels' => DegreeEnum::labels(),
        ]);
    }

    /**
     * Get all course name enum values
     */
    public function courseNames(): JsonResponse
    {
        return response()->json([
            'values' => CourseNameEnum::values(),
            'labels' => CourseNameEnum::labels(),
        ]);
    }

    /**
     * Get all enums at once
     */
    public function all(): JsonResponse
    {
        return response()->json([
            'qualities' => [
                'values' => QualityEnum::values(),
                'labels' => QualityEnum::labels(),
            ],
            'degrees' => [
                'values' => DegreeEnum::values(),
                'labels' => DegreeEnum::labels(),
            ],
            'courseNames' => [
                'values' => CourseNameEnum::values(),
                'labels' => CourseNameEnum::labels(),
            ],
        ]);
    }
}
