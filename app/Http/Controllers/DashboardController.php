<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Nanny;
use App\Models\Tutor;
use Illuminate\Support\Facades\Auth;
use App\Enums\User\RoleEnum;

class DashboardController extends Controller
{
 public function index()
    {
        $user = Auth::user();

    if ($user->hasRole(RoleEnum::NANNY->value)) {
        return $this->nannyDashboard();
    }

    if ($user->hasRole(RoleEnum::TUTOR->value)) {
        return $this->tutorDashboard();
    }

    if ($user->hasRole(RoleEnum::ADMIN->value)) {
        return $this->adminDashboard();
    }

        return redirect()->route('login');
    }

    public function nannyDashboard()
    {
        return Inertia::render('Dashboard/NannyDashboard');
    }

    public function tutorDashboard()
    {
        return Inertia::render('Dashboard/TutorDashboard');
    }

    public function adminDashboard()
    {
        return Inertia::render('Dashboard/AdminDashboard');
    }
}
