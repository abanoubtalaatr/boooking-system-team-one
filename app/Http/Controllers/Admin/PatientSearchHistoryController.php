<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Patient;

class PatientSearchHistoryController extends Controller
{
    /**
     * Patients with a count of their search history entries
     */
    public function index()
    {
        return view('admin.search-history.index');
    }

    /**
     * Search history entries for a specific patient
     */
    public function show(Patient $patient)
    {
        $searchHistories = $patient->searchHistories()
            ->latest()
            ->paginate(12);

        return view('admin.search-history.show', compact('patient', 'searchHistories'));
    }
}
