<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AvailabilitySlot;

class AvailabilitySlotController extends Controller
{
    public function index()
    {
        $slots = AvailabilitySlot::with([
                'doctor',
                'doctor.doctorProfile.specialization',
                'doctor.doctorProfile.hospital',
            ])
            ->latest()
            ->paginate(10);

        return view('admin.availability-slots.index', compact('slots'));
    }

    public function show(AvailabilitySlot $availabilitySlot)
    {
        $availabilitySlot->load([
            'doctor',
            'doctor.doctorProfile.specialization',
            'doctor.doctorProfile.hospital',
        ]);

        return view(
            'admin.availability-slots.show',
            compact('availabilitySlot')
        );
    }
}