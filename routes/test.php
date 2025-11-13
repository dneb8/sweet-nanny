<?php

use Illuminate\Support\Facades\Route;
use App\Services\NannySearchService;

Route::get('/test-train', function () {
    $service = app()->make(NannySearchService::class);
    $allNannies = $service->previewAllNanniesJson();
    $trainResponse = $service->sendNanniesToFlask($allNannies);
    return $trainResponse;
});

Route::get('/preview-nannies-json', function () {
    $service = app()->make(NannySearchService::class);
    return $service->previewAllNanniesJson();
});

Route::get('/test-search', function () {
    $filters = [
        'qualities' => ['empatica'],
        'courses' => ['educacion_y_aprendizaje'],
        'careers' => ['psicologia'],
        'zone' => 'Guadalajara',
        'availability' => true
    ];

    $service = app()->make(NannySearchService::class);
    $allNannies = $service->previewAllNanniesJson();
    $trainResponse = $service->sendAllNanniesToFlask($allNannies);
    $filterResponse = $service->sendFiltersToFlask($filters);

    return [
        'train_response' => $trainResponse,
        'filter_response' => $filterResponse
    ];
});

Route::get('/test-env', function () {
    return response()->json([
        'env_var' => env('NANNY_API_KEY'),
        'config_var' => config('services.flask.nanny_api_key'),
        'flask_url' => config('services.flask.url'),
    ]);
});