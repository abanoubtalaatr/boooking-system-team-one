<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\DoctorAccountCreatedMail;
use App\Models\DoctorProfile;
use App\Models\Hospital;
use App\Models\Specialization;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class DoctorController extends Controller
{
    public function index(Request $request)
    {
        $doctors = User::where('role', 'doctor')
            ->with([
                'doctorProfile.specialization',
                'doctorProfile.hospital',
            ])
            ->withCount(['bookingsAsDoctor', 'reviews'])
            ->withAvg('reviews', 'rating')
            ->when($request->specialization_id, function ($q) use ($request) {
                $q->whereHas('doctorProfile', function ($q) use ($request) {
                    $q->where('specialization_id', $request->specialization_id);
                });
            })
            ->when($request->search, function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%");
            })
            ->paginate(10);

        $specializations = Specialization::all();

        return view('admin.doctors.index', compact(
            'doctors',
            'specializations'
        ));
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

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'              => ['required', 'string', 'max:255'],
            'email'             => ['required', 'email', 'unique:users,email'],
            'password'          => ['required', 'confirmed', 'min:8'],

            'specialization_id' => ['required', 'exists:specializations,id'],
            'hospital_id'       => ['required', 'exists:hospitals,id'],
        ]);

        DB::transaction(function () use ($validated) {

            $doctor = User::create([
                'name'     => $validated['name'],
                'email'    => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role'     => 'doctor',
            ]);

            DoctorProfile::create([
                'user_id'           => $doctor->id,
                'specialization_id' => $validated['specialization_id'],
                'hospital_id'       => $validated['hospital_id'],
            ]);

            Mail::to($doctor->email)->send(
                new DoctorAccountCreatedMail(
                    $doctor,
                    $validated['password']
                )
            );

        });

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

    public function update(Request $request, User $doctor)
    {
        $validated = $request->validate([
            'specialization_id' => ['nullable', 'exists:specializations,id'],
            'hospital_id'       => ['nullable', 'exists:hospitals,id'],
            'is_active'         => ['required', 'boolean'],
        ]);

        $doctor->doctorProfile()->updateOrCreate(
            ['user_id' => $doctor->id],
            [
                'specialization_id' => $validated['specialization_id'],
                'hospital_id'       => $validated['hospital_id'],
                'is_active'         => $validated['is_active'],
            ]
        );

        return redirect()
            ->route('admin.doctors.index')
            ->with('success', 'تم تحديث بيانات الطبيب بنجاح.');
    }

    public function destroy(User $doctor)
    {
        $doctor->delete();

        return redirect()
            ->route('admin.doctors.index')
            ->with('success', 'تم حذف الطبيب بنجاح.');
    }

   
}
