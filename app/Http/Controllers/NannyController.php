<?php

namespace App\Http\Controllers;

use App\Enums\Nanny\QualityEnum;
use App\Http\Requests\Nanny\CreateNannyRequest;
use App\Http\Requests\Nanny\UpdateNannyRequest;
use App\Models\Nanny;
use App\Models\Quality;
use Illuminate\Http\Request;
use Inertia\Inertia;

class NannyController extends Controller
{
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateNannyRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Nanny $nanny)
    {
        return Inertia::render('Nanny/Show', [
            'nanny' => $nanny->load([
                'addresses',
                'courses',
                'careers',
                'qualities',
                'reviews',
                'bookingAppointments.booking',
            ]),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Nanny $nanny)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateNannyRequest $request, Nanny $nanny)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Nanny $nanny)
    {
        //
    }

    public function updateQualities(Request $request, Nanny $nanny)
    {
        $validated = $request->validate([
            'qualities' => 'array',
            'qualities.*' => 'string|in:'.implode(',', QualityEnum::values()),
        ]);

        $qualityIds = Quality::whereIn('name', $validated['qualities'])->pluck('id');
        $nanny->qualities()->sync($qualityIds);

        return response()->json([
            'message' => 'Cualidades actualizadas correctamente.',
            'qualities' => $nanny->qualities()->get(['id', 'name']),
        ]);
    }
}
