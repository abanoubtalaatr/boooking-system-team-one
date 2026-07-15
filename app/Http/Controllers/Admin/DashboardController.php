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
            'total_doctors' => User::where('role', 'doctor')->count(),
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
        $topDoctors = User::where('role', 'doctor')
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
        $doctors = User::where('role', 'doctor')->with('doctorProfile')
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
    public function patients(Request $request)
    {
        $patients = Patient::withCount('bookings')
            ->when($request->search, fn ($q) => $q->where('name', 'like', "%{$request->search}%"))
            ->latest()
            ->paginate(15);

        return view('admin.patients.index', compact('patients'));
    }

    /**
     * Specializations CRUD listing
     */
    public function specialties()
    {
        $specializations = Specialization::withCount('doctorProfiles')->paginate(15);

        return view('admin.specialties.index', compact('specializations'));
    }

    /**
     * Hospitals/clinics listing
     */
    public function clinics()
    {
        $hospitals = Hospital::withCount('doctorProfiles')->paginate(15);

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
    public function reports(Request $request)
    {
        $from = $request->date('from', now()->subMonth());
        $to = $request->date('to', now());

        $revenueByMonth = Booking::selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, SUM(price) as total')
            ->where('payment_status', 'paid')
            ->whereBetween('created_at', [$from, $to])
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $bookingsByStatus = Booking::selectRaw('status, COUNT(*) as total')
            ->whereBetween('created_at', [$from, $to])
            ->groupBy('status')
            ->get();

        $topSpecializations = Specialization::withCount('doctorProfiles')
            ->orderByDesc('doctor_profiles_count')
            ->take(5)
            ->get();

        return view('admin.reports.index', compact(
            'revenueByMonth', 'bookingsByStatus', 'topSpecializations', 'from', 'to'
        ));
    }

    /**
     * Users & permissions management (admin/doctor accounts)
     */
    public function users(Request $request)
    {
        $users = User::when($request->role, fn ($q) => $q->where('role', $request->role))
            ->when($request->search, fn ($q) => $q->where('name', 'like', "%{$request->search}%"))
            ->latest()
            ->paginate(15);

        return view('admin.users.index', compact('users'));
    }

    /**
     * Platform settings
     */
    public function settings()
    {
        return view('admin.settings.index');
    }
}
