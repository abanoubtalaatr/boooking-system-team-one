<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use Illuminate\Http\Request;

class PatientFavoriteDoctorsController extends Controller
{
    /**
     * Patients with a count of their favorite doctors
     */
    public function index(Request $request)
    {
        $perPageOptions = [15, 30, 50, 100];
        $perPage = (int) $request->input('per_page', 15);

        if (! in_array($perPage, $perPageOptions, true)) {
            $perPage = 15;
        }

        $patients = Patient::withCount('favorites')
            ->when($request->search, fn ($q) => $q->where('name', 'like', "%{$request->search}%"))
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

        return view('admin.patient-favorites.index', compact('patients', 'perPage', 'perPageOptions'));
    }

    /**
     * Doctors favorited by a specific patient
     */
    public function show(Patient $patient)
    {
        $favorites = $patient->favorites()
            ->with(['doctor.doctorProfile.specialty', 'doctor.doctorProfile.hospital'])
            ->latest()
            ->paginate(12);

        return view('admin.patient-favorites.show', compact('patient', 'favorites'));
    }
}
