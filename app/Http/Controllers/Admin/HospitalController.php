<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Hospital;
use Illuminate\Http\Request;

class HospitalController extends Controller
{
    /**
     * Display a listing of hospitals.
     */
    public function index()
    {
        $hospitals = Hospital::withCount('doctorProfiles')
            ->latest()
            ->paginate(10);

        return view('admin.hospitals.index', compact('hospitals'));
    }

    /**
     * Show the form for creating a new hospital.
     */
    public function create()
    {
        return view('admin.hospitals.create');
    }

    /**
     * Store a newly created hospital.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'      => ['required', 'string', 'max:255'],
            'address'   => ['required', 'string', 'max:255'],
            'latitude'  => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ]);

        Hospital::create($validated);

        return redirect()
            ->route('admin.hospitals.index')
            ->with('success', 'تم إضافة المستشفى بنجاح.');
    }

    /**
     * Display the specified hospital.
     */
    public function show(Hospital $hospital)
    {
        $hospital->load([
            'doctorProfiles.user',
            'doctorProfiles.specialization'
        ]);

        return view('admin.hospitals.show', compact('hospital'));
    }

    /**
     * Show the form for editing the specified hospital.
     */
    public function edit(Hospital $hospital)
    {
        return view('admin.hospitals.edit', compact('hospital'));
    }

    /**
     * Update the specified hospital.
     */
    public function update(Request $request, Hospital $hospital)
    {
        $validated = $request->validate([
            'name'      => ['required', 'string', 'max:255'],
            'address'   => ['required', 'string', 'max:255'],
            'latitude'  => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ]);

        $hospital->update($validated);

        return redirect()
            ->route('admin.hospitals.index')
            ->with('success', 'تم تحديث بيانات المستشفى بنجاح.');
    }

    /**
     * Remove the specified hospital.
     */
    public function destroy(Hospital $hospital)
    {
        if ($hospital->doctorProfiles()->exists()) {

            return back()->with(
                'error',
                'لا يمكن حذف المستشفى لوجود أطباء مرتبطين بها.'
            );
        }

        $hospital->delete();

        return redirect()
            ->route('admin.hospitals.index')
            ->with('success', 'تم حذف المستشفى بنجاح.');
    }
}