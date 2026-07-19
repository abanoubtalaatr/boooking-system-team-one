<?php

namespace Database\Seeders;

use App\Enums\BookingStatus;
use App\Enums\ConsultationType;
use App\Enums\NoShowReportStatus;
use App\Enums\PaymentStatus;
use App\Enums\SlotReservationStatus;
use App\Models\AvailabilitySlot;
use App\Models\Booking;
use App\Models\BookingNoShowReport;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NoShowReportsDemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(NoShowScenarioSeeder::class);

        DB::transaction(function (): void {
            $admin = User::query()->where('email', 'demo.admin@cure.test')->firstOrFail();
            $doctor = User::query()->where('email', 'demo.doctor@cure.test')->firstOrFail();
            $patient = Patient::query()->where('email', 'demo.patient@cure.test')->firstOrFail();
            $pendingBooking = Booking::query()->where('booking_number', 'BK-NOSHOW-DEMO')->firstOrFail();
            $approvedBooking = $this->booking(
                $doctor,
                $patient,
                'BK-NOSHOW-APPROVED',
                now()->subDays(4)->toDateString(),
                '12:00:00',
                '13:00:00',
                BookingStatus::Cancelled,
                PaymentStatus::Voided,
                false,
            );
            $rejectedBooking = $this->booking(
                $doctor,
                $patient,
                'BK-NOSHOW-REJECTED',
                now()->subDays(3)->toDateString(),
                '14:00:00',
                '15:00:00',
                BookingStatus::Completed,
                PaymentStatus::Pending,
                true,
            );

            BookingNoShowReport::query()->updateOrCreate(
                ['booking_id' => $pendingBooking->id],
                [
                    'doctor_id' => $doctor->id,
                    'status' => NoShowReportStatus::PendingReview,
                    'reason' => 'المريض لم يحضر الموعد ولم يستجب لمحاولتي اتصال من العيادة.',
                    'reviewed_by' => null,
                    'review_note' => null,
                    'reviewed_at' => null,
                ],
            );
            BookingNoShowReport::query()->updateOrCreate(
                ['booking_id' => $approvedBooking->id],
                [
                    'doctor_id' => $doctor->id,
                    'status' => NoShowReportStatus::Approved,
                    'reason' => 'تأكد عدم حضور المريض بعد مراجعة سجل الاستقبال والاتصالات.',
                    'reviewed_by' => $admin->id,
                    'review_note' => 'تم التحقق من البلاغ وإلغاء الحجز.',
                    'reviewed_at' => now()->subDay(),
                ],
            );
            BookingNoShowReport::query()->updateOrCreate(
                ['booking_id' => $rejectedBooking->id],
                [
                    'doctor_id' => $doctor->id,
                    'status' => NoShowReportStatus::Rejected,
                    'reason' => 'لم يظهر المريض في الوقت المحدد وفقًا لملاحظة الطبيب.',
                    'reviewed_by' => $admin->id,
                    'review_note' => 'تم رفض البلاغ لعدم كفاية دليل التواصل مع المريض.',
                    'reviewed_at' => now()->subHours(12),
                ],
            );

            $this->command?->info('Three no-show reports are ready for the admin dashboard.');
        }, attempts: 3);
    }

    private function booking(
        User $doctor,
        Patient $patient,
        string $bookingNumber,
        string $day,
        string $startTime,
        string $endTime,
        BookingStatus $status,
        PaymentStatus $paymentStatus,
        bool $isBooked,
    ): Booking {
        $slot = AvailabilitySlot::query()
            ->where('doctor_id', $doctor->id)
            ->whereDate('day', $day)
            ->whereTime('start_time', $startTime)
            ->whereTime('end_time', $endTime)
            ->first() ?? new AvailabilitySlot;
        $slot->fill([
            'doctor_id' => $doctor->id,
            'day' => $day,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'is_booked' => $isBooked,
            'reservation_status' => $isBooked ? SlotReservationStatus::Booked : SlotReservationStatus::Available,
            'reserved_until' => null,
        ])->save();

        $booking = Booking::query()->updateOrCreate(
            ['booking_number' => $bookingNumber],
            [
                'patient_id' => $patient->id,
                'doctor_id' => $doctor->id,
                'availability_slot_id' => $slot->id,
                'booking_date' => $day,
                'booking_time' => $startTime,
                'consultation_type' => ConsultationType::Clinic,
                'status' => $status,
                'price' => 300,
                'payment_status' => $paymentStatus,
                'hold_expires_at' => null,
            ],
        );
        $slot->update(['reserved_booking_id' => $isBooked ? $booking->id : null]);

        return $booking;
    }
}
