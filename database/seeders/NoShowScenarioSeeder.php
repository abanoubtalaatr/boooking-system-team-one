<?php

namespace Database\Seeders;

use App\Enums\BookingStatus;
use App\Enums\ConsultationType;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\SlotReservationStatus;
use App\Enums\UserRole;
use App\Enums\WalletTransactionType;
use App\Models\AvailabilitySlot;
use App\Models\Booking;
use App\Models\BookingNoShowReport;
use App\Models\DoctorProfile;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class NoShowScenarioSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $admin = User::query()->updateOrCreate(
                ['email' => 'demo.admin@cure.test'],
                [
                    'name' => 'Demo Admin',
                    'password' => Hash::make('password'),
                    'role' => UserRole::Admin,
                ],
            );
            $doctor = User::query()->updateOrCreate(
                ['email' => 'demo.doctor@cure.test'],
                [
                    'name' => 'د. أحمد - سيناريو عدم الحضور',
                    'password' => Hash::make('password'),
                    'role' => UserRole::Doctor,
                ],
            );
            DoctorProfile::query()->updateOrCreate(
                ['user_id' => $doctor->id],
                [
                    'bio' => 'حساب تجريبي لاختبار بلاغ عدم حضور المريض.',
                    'price' => 300,
                    'experience_years' => 8,
                    'is_active' => true,
                ],
            );
            $patient = Patient::query()->updateOrCreate(
                ['phone' => '01000000103'],
                [
                    'name' => 'مريض تجريبي لم يحضر',
                    'email' => 'demo.patient@cure.test',
                    'password' => Hash::make('password'),
                    'verified_at' => now(),
                ],
            );
            $slot = AvailabilitySlot::query()
                ->where('doctor_id', $doctor->id)
                ->whereDate('day', '2026-07-14')
                ->whereTime('start_time', '10:00:00')
                ->whereTime('end_time', '11:00:00')
                ->first() ?? new AvailabilitySlot;
            $slot->fill([
                'doctor_id' => $doctor->id,
                'day' => '2026-07-14',
                'start_time' => '10:00:00',
                'end_time' => '11:00:00',
                'is_booked' => true,
                'reservation_status' => SlotReservationStatus::Booked,
                'reserved_until' => null,
            ])->save();
            $booking = Booking::query()->updateOrCreate(
                ['booking_number' => 'BK-NOSHOW-DEMO'],
                [
                    'patient_id' => $patient->id,
                    'doctor_id' => $doctor->id,
                    'availability_slot_id' => $slot->id,
                    'booking_date' => '2026-07-14',
                    'booking_time' => '10:00:00',
                    'consultation_type' => ConsultationType::Clinic,
                    'status' => BookingStatus::Completed,
                    'price' => 300,
                    'payment_status' => PaymentStatus::CashCollected,
                    'hold_expires_at' => null,
                ],
            );
            $slot->update(['reserved_booking_id' => $booking->id]);
            $payment = Payment::query()->updateOrCreate(
                ['uuid' => '10000000-0000-4000-8000-000000000001'],
                [
                    'booking_id' => $booking->id,
                    'patient_id' => $patient->id,
                    'doctor_id' => $doctor->id,
                    'method' => PaymentMethod::Cash,
                    'status' => PaymentStatus::CashCollected,
                    'amount_cents' => 30000,
                    'currency' => 'EGP',
                    'commission_bps' => 1500,
                    'commission_amount_cents' => 4500,
                    'doctor_amount_cents' => 25500,
                    'idempotency_key' => 'no-show-demo-payment',
                    'provider' => null,
                    'paid_at' => now(),
                    'refunded_at' => null,
                ],
            );
            $wallet = Wallet::query()->updateOrCreate(
                ['doctor_id' => $doctor->id, 'currency' => 'EGP'],
                ['balance_cents' => -4500, 'payout_blocked' => true],
            );

            BookingNoShowReport::query()->whereBelongsTo($booking)->delete();
            WalletTransaction::query()
                ->where('idempotency_key', "no-show-commission-reversal:{$payment->uuid}")
                ->delete();
            WalletTransaction::query()->updateOrCreate(
                ['idempotency_key' => "cash-commission:{$payment->uuid}"],
                [
                    'wallet_id' => $wallet->id,
                    'payment_id' => $payment->id,
                    'booking_id' => $booking->id,
                    'type' => WalletTransactionType::CashCommissionDebit,
                    'amount_cents' => -4500,
                    'balance_after_cents' => -4500,
                    'metadata' => ['scenario' => 'patient_no_show_demo'],
                ],
            );

            $this->command?->info("No-show demo ready: booking {$booking->booking_number}, doctor {$doctor->email}, admin {$admin->email}");
        }, attempts: 3);
    }
}
