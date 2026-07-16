<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\Admin\Doctor\StoreDoctorRequest;
use App\Http\Requests\Web\Admin\Doctor\UpdateDoctorRequest;
use App\Models\Hospital;
use App\Models\Specialization;
use App\Models\User;
use App\Services\Web\DoctorAccountService;
use Illuminate\Http\Request;

class DoctorController extends Controller
{
    public function __construct(protected DoctorAccountService $doctorAccountService)
    {
    }

    public function index(Request $request)
    {
        $doctors = User::where('role', 'doctor')
            ->with([ 'doctorProfile.specialization','doctorProfile.hospital', ])
            ->withCount(['bookingsAsDoctor', 'reviews'])
            ->withAvg('reviews', 'rating')
            ->when($request->specialization_id, function ($q) use ($request) {
                $q->whereHas('doctorProfile', function ($q) use ($request) { 
                     $q->where('specialization_id', $request->specialization_id); 
                });  
            })->when($request->search, function ($q) use ($request) {$q->where('name', 'like', "%{$request->search}%");})->paginate(10);

        $specializations = Specialization::all();
        return view('admin.doctors.index', compact(  'doctors',  'specializations'  ));
    }

    public function show(User $doctor)
    {
        return view('admin.doctors.show', compact('doctor'));
    }

    public function create()
    {
        $specializations = Specialization::orderBy('name')->get();

        $hospitals = Hospital::orderBy('name')->get();

        return view('admin.doctors.create', compact(
            'specializations',
            'hospitals'
        ));
    }

    public function store(StoreDoctorRequest $request)
    {
        $this->doctorAccountService->createDoctor($request->validated());

        return redirect()
            ->route('admin.doctors.index')
            ->with('success', 'تم إنشاء حساب الطبيب وإرسال بيانات الدخول إلى البريد الإلكتروني.');
    }

    public function edit(User $doctor)
    {
        $specializations = Specialization::orderBy('name')->get();
        $hospitals       = Hospital::orderBy('name')->get();

        return view('admin.doctors.edit', compact(
            'doctor',
            'specializations',
            'hospitals'
        ));
    }

    public function update(UpdateDoctorRequest $request, User $doctor)
    {
        $this->doctorAccountService->updateDoctor($doctor, $request->validated());

        return redirect()
            ->route('admin.doctors.index')
            ->with('success', 'تم تحديث بيانات الطبيب بنجاح.');
    }

    public function destroy(User $doctor)
    {
        if (! $this->doctorAccountService->deleteDoctor($doctor)) {
            return back()->with(
                'error',
                $this->doctorAccountService->deletionBlockReason($doctor)
            );
        }

        return redirect()
            ->route('admin.doctors.index')
            ->with('success', 'تم حذف الطبيب بنجاح.');
    }
}