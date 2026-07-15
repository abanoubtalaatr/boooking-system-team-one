<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Patient;

class PatientFavoriteDoctorsController extends Controller
{
    /**
     * Patients with a count of their favorite doctors
     */
    public function index()
    {
        return view('admin.patient-favorite.index');
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

        return view('admin.patient-favorite.show', compact('patient', 'favorites'));
    }
}
