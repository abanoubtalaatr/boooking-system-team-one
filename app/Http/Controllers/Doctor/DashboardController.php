<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
//use App\Models\AvailabilitySlot;
use App\Models\Booking;
//use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Main doctor dashboard
     */
    public function index()
    {/*
        $doctor = Auth::user();

        $stats = [
            'total_bookings'     => Booking::where('doctor_id', $doctor->id)->count(),
            'pending_bookings'   => Booking::where('doctor_id', $doctor->id)->where('status', 'pending')->count(),
            'today_appointments' => Booking::where('doctor_id', $doctor->id)
                ->whereHas('slot', fn ($q) => $q->whereDate('day', today()))
                ->count(),
            'total_patients' => Booking::where('doctor_id', $doctor->id)
                ->distinct('patient_id')
                ->count('patient_id'),
            'total_earnings' => Booking::where('doctor_id', $doctor->id)
                ->where('payment_status', 'paid')
                ->sum('price'),
            'average_rating' => round(Review::where('user_id', $doctor->id)->avg('rating'), 1),
            'total_reviews'  => Review::where('user_id', $doctor->id)->count(),
        ];

        // Upcoming appointments (today + future, not cancelled)
        $upcomingBookings = Booking::with(['patient', 'slot'])
            ->where('doctor_id', $doctor->id)
            ->whereIn('status', ['pending', 'confirmed'])
            ->whereHas('slot', fn ($q) => $q->where('day', '>=', today()))
            ->orderBy(
                AvailabilitySlot::select('day')
                    ->whereColumn('id', 'bookings.slot_id')
            )
            ->take(5)
            ->get();

        // Latest reviews
        $recentReviews = Review::with('patient')
            ->where('user_id', $doctor->id)
            ->latest()
            ->take(5)
            ->get();

        // Slot availability today (for quick "am I fully booked" glance)
        $todaySlots = AvailabilitySlot::where('doctor_id', $doctor->id)
            ->whereDate('day', today())
            ->orderBy('start_time')
            ->get();

        return view('doctor.dashboard', compact(
            'stats', 'upcomingBookings', 'recentReviews', 'todaySlots'
        ));*/
    }

    /**
     * Doctor's own bookings, with filters
     */
    public function bookings(Request $request)
    {
        $doctor = Auth::user();

        $bookings = Booking::with(['patient', 'slot'])
            ->where('doctor_id', $doctor->id)
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->date, fn ($q) => $q->whereHas('slot', fn ($q) => $q->whereDate('day', $request->date)))
            ->latest()
            ->paginate(15);

        return view('doctor.bookings.index', compact('bookings'));
    }

   

    

    /**
     * List of patients who booked with this doctor
     */
    public function patients(Request $request)
    {/*
        $doctor = Auth::user();

        $patients = \App\Models\Patient::whereHas('bookings', fn ($q) => $q->where('doctor_id', $doctor->id))
            ->withCount(['bookings' => fn ($q) => $q->where('doctor_id', $doctor->id)])
            ->when($request->search, fn ($q) => $q->where('name', 'like', "%{$request->search}%"))
            ->paginate(15);

        return view('doctor.patients.index', compact('patients'));*/
    }

    /**
     * Reviews received by this doctor
     */
    public function reviews()
    {/*
        $doctor = Auth::user();

        $reviews = Review::with('patient')
            ->where('user_id', $doctor->id)
            ->latest()
            ->paginate(15);

        $averageRating = round($reviews->avg('rating'), 1);

        return view('doctor.reviews.index', compact('reviews', 'averageRating'));*/
    }

    /**
     * Doctor's professional profile
     */
    public function profile()
    {/*
        $doctor = Auth::user()->load('doctorProfile.specialization', 'doctorProfile.hospital');

        return view('doctor.profile.index', compact('doctor'));*/
    }

    /**
     * Update doctor profile
     */
    public function updateProfile(Request $request)
    {/*
        $doctor = Auth::user();

        $validated = $request->validate([
            'bio'              => 'nullable|string',
            'price'            => 'nullable|numeric|min:0',
            'experience_years' => 'nullable|integer|min:0',
            'education'        => 'nullable|string|max:255',
            'language'         => 'nullable|string|max:255',
        ]);

        $doctor->doctorProfile()->update($validated);

        return back()->with('success', 'تم تحديث الملف المهني بنجاح');*/
    }
}
