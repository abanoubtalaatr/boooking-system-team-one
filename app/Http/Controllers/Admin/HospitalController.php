<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\Admin\Hospital\StoreHospitalRequest;
use App\Http\Requests\Web\Admin\Hospital\UpdateHospitalRequest;
use App\Models\Hospital;
use App\Services\Web\HospitalService;

class HospitalController extends Controller
{
    public function __construct(protected HospitalService $hospitalService)
    {
    }

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
    public function store(StoreHospitalRequest $request)
    {
        $this->hospitalService->create($request->validated());

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
            'doctorProfiles.specialization',
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
    public function update(UpdateHospitalRequest $request, Hospital $hospital)
    {
        $this->hospitalService->update($hospital, $request->validated());

        return redirect()
            ->route('admin.hospitals.index')
            ->with('success', 'تم تحديث بيانات المستشفى بنجاح.');
    }

    /**
     * Remove the specified hospital.
     */
    public function destroy(Hospital $hospital)
    {
        if (! $this->hospitalService->delete($hospital)) {
            return back()->with(
                'error',
                'لا يمكن حذف المستشفى لوجود أطباء مرتبطين بها.'
            );
        }

        return redirect()
            ->route('admin.hospitals.index')
            ->with('success', 'تم حذف المستشفى بنجاح.');
    }
}