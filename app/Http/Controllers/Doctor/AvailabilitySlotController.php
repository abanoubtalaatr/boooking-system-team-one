<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Doctor\Web\StoreAvailabilitySlotRequest;
use App\Http\Requests\Doctor\Web\UpdateAvailabilitySlotRequest;
use App\Models\AvailabilitySlot;
use App\Services\Web\AvailabilitySlotService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class AvailabilitySlotController extends Controller
{
    public function __construct(
        protected AvailabilitySlotService $slots
    ) {
    }

    public function index()
    {
        $doctorId = Auth::id();

        $slots = AvailabilitySlot::where('doctor_id', $doctorId)->latest()->paginate(10);

        return view('doctor.availability-slots.index', [
            'slots'          => $slots,
            'totalSlots'     => AvailabilitySlot::where('doctor_id', $doctorId)->count(),
            'availableSlots' => AvailabilitySlot::where('doctor_id', $doctorId)->where('is_booked', false)->count(),
            'bookedSlots'    => AvailabilitySlot::where('doctor_id', $doctorId)->where('is_booked', true)->count(),
        ]);
    }

    public function create()
    {
        return view('doctor.availability-slots.create');
    }

    public function store(StoreAvailabilitySlotRequest $request)
    {
        $validated = $request->validated();

        $result = $this->slots->createHourlySlots(Auth::id(),$validated['day'],  $validated['start_time'], $validated['end_time'], );

        if ($result['conflict']) {
            return back()->withErrors(['start_time' => $result['conflict']])->withInput();
        }

        return redirect()->route('doctor.availability-slots.index')->with('success', 'تم إنشاء المواعيد بنجاح.');
    }

    public function show(AvailabilitySlot $availabilitySlot)
    {
        // Doctors can only view their own slots.
        abort_if($availabilitySlot->doctor_id != Auth::id(), 403);
        return view('doctor.availability-slots.show', compact('availabilitySlot'));
    }

    public function edit(AvailabilitySlot $availabilitySlot)
    {
        // Doctors can only edit their own slots.
        abort_if($availabilitySlot->doctor_id != Auth::id(), 403);
        return view('doctor.availability-slots.edit', compact('availabilitySlot'));
    }

    public function update(UpdateAvailabilitySlotRequest $request, AvailabilitySlot $availabilitySlot)
    {
        // Doctors can only edit their own slots.
        abort_if($availabilitySlot->doctor_id != Auth::id(), 403);

        // Booked slots are locked for editing; this is a business rule
        // (friendly redirect), not an access-control failure, so it's a
        // 302 with a flash message rather than a 403.
        if ($availabilitySlot->is_booked) {
            return redirect() ->route('doctor.availability-slots.index')->with('error', 'لا يمكن تعديل المواعيد المحجوزة.');
        }

        $validated = $request->validated();
        $startTime = Carbon::createFromFormat('H:i', $validated['start_time']);

        // Exclude the slot being edited from the duplicate check, otherwise
        // it would always conflict with itself.
        if ($this->slots->slotExists(Auth::id(), $validated['day'], $startTime->format('H:i:s'), $availabilitySlot->id)) {
            return back()->withErrors(['start_time' => 'يوجد موعد آخر بالفعل في هذا التوقيت.']) ->withInput();
        }
        $availabilitySlot->update([
            'day'        => $validated['day'],
            'start_time' => $startTime->format('H:i:s'),
            'end_time'   => $startTime->copy()->addHour()->format('H:i:s'),
        ]);

        return redirect() ->route('doctor.availability-slots.index')->with('success', 'تم تحديث الموعد بنجاح.');
    }

    public function destroy(AvailabilitySlot $availabilitySlot)
    {
        // Doctors can only delete their own slots.
        abort_if($availabilitySlot->doctor_id != Auth::id(), 403);
        $availabilitySlot->delete();
        return redirect()->route('doctor.availability-slots.index')->with('success', 'تم حذف الموعد.');
    }
}