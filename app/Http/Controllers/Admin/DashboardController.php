<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AvailabilitySlot;
use App\Models\Booking;
use App\Models\Hospital;
use App\Models\Patient;
use App\Models\Specialization;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Main dashboard overview
     */
    public function index()
    {
        $stats = [
            'total_doctors' => User::role('doctor')->count(),
            'total_patients' => Patient::count(),
            'total_bookings' => Booking::count(),
            'total_hospitals' => Hospital::count(),
            'pending_bookings' => Booking::where('status', 'pending')->count(),
            'confirmed_bookings' => Booking::where('status', 'confirmed')->count(),
            'completed_bookings' => Booking::where('status', 'completed')->count(),
            'cancelled_bookings' => Booking::where('status', 'cancelled')->count(),
            'total_revenue' => Booking::where('payment_status', 'paid')->sum('price'),
        ];

        // Bookings over the last 7 days (for a chart)
        $bookingsPerDay = Booking::selectRaw('DATE(created_at) as date, COUNT(*) as total')
            ->where('created_at', '>=', now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Latest bookings for a quick table on the dashboard
        $recentBookings = Booking::with(['patient', 'doctor.doctorProfile'])
            ->latest()
            ->take(5)
            ->get();

        // Top rated doctors
        $topDoctors = User::role('doctor')
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->orderByDesc('reviews_avg_rating')
            ->take(5)
            ->get();

        return view('admin.dashboard', compact(
            'stats', 'bookingsPerDay', 'recentBookings', 'topDoctors'
        ));
    }

    /**
     * All bookings with filters
     */
    public function bookings(Request $request)
    {
        $bookings = Booking::with(['patient', 'doctor.doctorProfile', 'slot'])
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->payment_status, fn ($q) => $q->where('payment_status', $request->payment_status))
            ->when($request->search, function ($q) use ($request) {
                $q->whereHas('patient', fn ($q) => $q->where('name', 'like', "%{$request->search}%"));
            })
            ->latest()
            ->paginate(15);

        return view('admin.bookings.index', compact('bookings'));
    }

    /**
     * All doctors listing
     */
    public function doctors(Request $request)
    {
        $doctors = User::role('doctor')->with('doctorProfile')
           // ->with(['doctorProfile.specialization', 'doctorProfile.hospital'])
            // ->withCount(['bookingsAsDoctor', 'reviews'])
           // ->withAvg('reviews', 'rating')
            // ->when($request->specialization_id, function ($q) use ($request) {
            //    $q->whereHas('doctorProfile', fn ($q) => $q->where('specialization_id', $request->specialization_id));
           // })
            // ->when($request->search, fn ($q) => $q->where('name', 'like', "%{$request->search}%"))
            ->paginate(10);

        // $specializations = Specialization::all();

        return view('admin.doctors.index', compact('doctors')); // , 'specializations'));
    }

    /**
     * All patients listing
     */
    public function patients()
    {
        return view('admin.patients.index');
    }

    public function patientProfile(Patient $patient)
    {
        return view('admin.patients.show', compact('patient'));
    }

    /**
     * Specializations CRUD listing
     */
    public function specialties()
    {
        $specializations = Specialization::withCount('doctors')->latest()->paginate(15);

        return view('admin.specialties.index', compact('specializations'));
    }

    /**
     * Hospitals/clinics listing
     */
    public function clinics()
    {
        $hospitals = Hospital::withCount('doctorProfiles')->latest()->paginate(15);

        return view('admin.clinics.index', compact('hospitals'));
    }

    /**
     * Appointments / availability slots overview
     */
    public function appointments(Request $request)
    {
        $slots = AvailabilitySlot::with('doctor.doctorProfile')
            ->when($request->date, fn ($q) => $q->whereDate('day', $request->date))
            ->when($request->doctor_id, fn ($q) => $q->where('doctor_id', $request->doctor_id))
            ->orderBy('day')
            ->orderBy('start_time')
            ->paginate(20);

        return view('admin.appointments.index', compact('slots'));
    }

    /**
     * Reports: revenue + booking trends
     */
    public function reports()
    {
        return view('admin.reports.index');
    }

    /**
     * Users & permissions management (admin/doctor accounts)
     */
    public function users(Request $request)
    {
        $users = User::role(['admin', 'super-admin'])
            ->when($request->search, fn ($q) => $q->where('name', 'like', "%{$request->search}%"))
            ->latest()
            ->paginate(15);

        return view('admin.users.index', compact('users'));
    }
}
