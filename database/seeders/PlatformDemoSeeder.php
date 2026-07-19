<?php

namespace Database\Seeders;

use App\Enums\BookingStatus;
use App\Enums\ConsultationType;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\SlotReservationStatus;
use App\Enums\WalletTransactionType;
use App\Enums\WalletWithdrawalStatus;
use App\Models\AvailabilitySlot;
use App\Models\Booking;
use App\Models\Conversation;
use App\Models\DoctorProfile;
use App\Models\Faq;
use App\Models\FaqCategory;
use App\Models\Favorite;
use App\Models\Hospital;
use App\Models\Message;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\Policy;
use App\Models\Promotion;
use App\Models\Review;
use App\Models\SearchHistory;
use App\Models\Specialization;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Models\WalletWithdrawal;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;

class PlatformDemoSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $admin = User::query()->where('email', 'camila.herman@example.net')->firstOrFail();
            $regularAdmin = User::query()->where('email', 'admin@cure.test')->firstOrFail();
            $regularAdmin->syncRoles(['admin']);
            $regularAdmin->syncPermissions(Permission::query()->where('guard_name', 'web')->get());

            $specializations = $this->specializations();
            $hospitals = $this->hospitals();
            $doctors = $this->doctors($admin, $specializations, $hospitals);
            $patients = $this->patients();

            $this->futureAvailability($doctors);
            $this->bookingsAndPayments($doctors, $patients);
            $this->patientActivity($doctors, $patients);
            $this->walletWithdrawals($admin, $doctors);
            $this->content();

            $this->command?->info('Platform demo data is ready. Password for all demo accounts: password');
        }, attempts: 3);
    }

    /** @return Collection<int, Specialization> */
    private function specializations(): Collection
    {
        return collect([
            ['name' => 'طب القلب', 'image' => null],
            ['name' => 'طب الأطفال', 'image' => null],
            ['name' => 'الجلدية', 'image' => null],
            ['name' => 'العظام', 'image' => null],
            ['name' => 'الأسنان', 'image' => null],
            ['name' => 'الباطنة', 'image' => null],
        ])->map(fn (array $attributes): Specialization => Specialization::query()->updateOrCreate(
            ['name' => $attributes['name']],
            $attributes,
        ));
    }

    /** @return Collection<int, Hospital> */
    private function hospitals(): Collection
    {
        return collect([
            ['name' => 'مستشفى الشفاء', 'address' => 'مدينة نصر، القاهرة', 'latitude' => 30.0566000, 'longitude' => 31.3301000],
            ['name' => 'عيادات كير الطبية', 'address' => 'المهندسين، الجيزة', 'latitude' => 30.0511000, 'longitude' => 31.2001000],
            ['name' => 'مركز النخبة الطبي', 'address' => 'التجمع الخامس، القاهرة', 'latitude' => 30.0131000, 'longitude' => 31.4913000],
        ])->map(fn (array $attributes): Hospital => Hospital::query()->updateOrCreate(
            ['name' => $attributes['name']],
            $attributes,
        ));
    }

    /**
     * @param  Collection<int, Specialization>  $specializations
     * @param  Collection<int, Hospital>  $hospitals
     * @return Collection<int, User>
     */
    private function doctors(User $admin, Collection $specializations, Collection $hospitals): Collection
    {
        $doctorNames = ['أحمد محمود', 'سارة علي', 'محمد حسن', 'مريم سامي', 'عمر خالد', 'نور إبراهيم'];
        $doctors = collect();

        foreach ($doctorNames as $index => $name) {
            $doctor = User::query()->updateOrCreate(
                ['email' => 'doctor'.($index + 1).'@cure.test'],
                [
                    'name' => 'د. '.$name,
                    'status' => 'active',
                    'email_verified_at' => now(),
                    'password' => Hash::make('password'),
                    'created_by' => $admin->id,
                ],
            );
            $doctor->syncRoles(['doctor']);

            $profile = DoctorProfile::query()->updateOrCreate(
                ['user_id' => $doctor->id],
                [
                    'specialization_id' => $specializations[$index % $specializations->count()]->id,
                    'hospital_id' => $hospitals[$index % $hospitals->count()]->id,
                    'latitude' => 30.04 + ($index / 1000),
                    'longitude' => 31.23 + ($index / 1000),
                    'bio' => 'طبيب تجريبي بخبرة واسعة، متاح لاختبار جميع عمليات المنصة.',
                    'price' => 250 + ($index * 50),
                    'experience_years' => 4 + ($index * 2),
                    'is_active' => true,
                ],
            );
            $profile->hospitals()->syncWithoutDetaching([
                $hospitals[$index % $hospitals->count()]->id,
                $hospitals[($index + 1) % $hospitals->count()]->id,
            ]);
            Wallet::query()->updateOrCreate(
                ['doctor_id' => $doctor->id, 'currency' => 'EGP'],
                ['balance_cents' => 0, 'payout_blocked' => false],
            );
            $doctors->push($doctor);
        }

        return $doctors;
    }

    /** @return Collection<int, Patient> */
    private function patients(): Collection
    {
        $patientNames = ['يوسف أحمد', 'فاطمة محمد', 'محمود علي', 'هدى سامي', 'خالد حسن', 'آية إبراهيم', 'عمر ياسر', 'منى أشرف'];
        $patients = collect();

        foreach ($patientNames as $index => $name) {
            $patients->push(Patient::query()->updateOrCreate(
                ['phone' => '0101000000'.($index + 1)],
                [
                    'name' => $name,
                    'email' => 'patient'.($index + 1).'@cure.test',
                    'password' => Hash::make('password'),
                    'birthdate' => CarbonImmutable::today()->subYears(22 + $index)->subMonths($index)->toDateString(),
                    'latitude' => 30.05 + ($index / 1000),
                    'longitude' => 31.24 + ($index / 1000),
                    'verified_at' => now(),
                ],
            ));
        }

        return $patients;
    }

    /** @param Collection<int, User> $doctors */
    private function futureAvailability(Collection $doctors): void
    {
        foreach ($doctors as $doctor) {
            for ($day = 1; $day <= 7; $day++) {
                for ($hour = 10; $hour <= 14; $hour += 2) {
                    $this->availabilitySlot(
                        $doctor,
                        CarbonImmutable::today()->addDays($day)->toDateString(),
                        sprintf('%02d:00:00', $hour),
                        sprintf('%02d:00:00', $hour + 1),
                        [
                            'is_booked' => false,
                            'reservation_status' => SlotReservationStatus::Available,
                            'reserved_booking_id' => null,
                            'reserved_until' => null,
                        ],
                    );
                }
            }
        }
    }

    /**
     * @param  Collection<int, User>  $doctors
     * @param  Collection<int, Patient>  $patients
     */
    private function bookingsAndPayments(Collection $doctors, Collection $patients): void
    {
        $sequence = 1;

        for ($daysAgo = 0; $daysAgo < 40; $daysAgo++) {
            $this->bookingWithPayment($sequence++, CarbonImmutable::now()->subDays($daysAgo), $doctors, $patients);
        }

        for ($monthsAgo = 2; $monthsAgo <= 11; $monthsAgo++) {
            $this->bookingWithPayment($sequence++, CarbonImmutable::now()->subMonths($monthsAgo)->startOfMonth()->addDays(5), $doctors, $patients);
            $this->bookingWithPayment($sequence++, CarbonImmutable::now()->subMonths($monthsAgo)->startOfMonth()->addDays(15), $doctors, $patients);
        }

        foreach ($doctors as $doctor) {
            $wallet = Wallet::query()->whereBelongsTo($doctor, 'doctor')->firstOrFail();
            $wallet->update(['balance_cents' => (int) $wallet->transactions()->sum('amount_cents')]);
        }
    }

    /**
     * @param  Collection<int, User>  $doctors
     * @param  Collection<int, Patient>  $patients
     */
    private function bookingWithPayment(int $sequence, CarbonImmutable $occurredAt, Collection $doctors, Collection $patients): void
    {
        $doctor = $doctors[($sequence - 1) % $doctors->count()];
        $patient = $patients[($sequence - 1) % $patients->count()];
        $startHour = 9 + (($sequence - 1) % 8);
        $priceCents = 25000 + ((($sequence - 1) % 6) * 5000);
        [$bookingStatus, $method, $paymentStatus] = match ($sequence % 8) {
            0 => [BookingStatus::Completed, PaymentMethod::Card, PaymentStatus::Succeeded],
            1 => [BookingStatus::Completed, PaymentMethod::Cash, PaymentStatus::CashCollected],
            2 => [BookingStatus::Confirmed, PaymentMethod::Card, PaymentStatus::Pending],
            3 => [BookingStatus::Pending, PaymentMethod::Cash, PaymentStatus::CashDue],
            4 => [BookingStatus::Cancelled, PaymentMethod::Card, PaymentStatus::Refunded],
            5 => [BookingStatus::PaymentFailed, PaymentMethod::Card, PaymentStatus::Failed],
            6 => [BookingStatus::Completed, PaymentMethod::Card, PaymentStatus::Paid],
            default => [BookingStatus::Rescheduled, PaymentMethod::Card, PaymentStatus::Initiated],
        };
        $isSettled = in_array($paymentStatus, [PaymentStatus::Succeeded, PaymentStatus::Paid, PaymentStatus::CashCollected], true);
        $commissionCents = $isSettled ? intdiv($priceCents * 10, 100) : 0;
        $uuid = '20000000-0000-4000-8000-'.str_pad((string) $sequence, 12, '0', STR_PAD_LEFT);
        $bookingNumber = 'BK-DEMO-'.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);

        $slot = $this->availabilitySlot(
            $doctor,
            $occurredAt->toDateString(),
            sprintf('%02d:00:00', $startHour),
            sprintf('%02d:00:00', $startHour + 1),
            [
                'is_booked' => true,
                'reservation_status' => SlotReservationStatus::Booked,
                'reserved_until' => null,
            ],
        );
        $booking = Booking::query()->updateOrCreate(
            ['booking_number' => $bookingNumber],
            [
                'patient_id' => $patient->id,
                'doctor_id' => $doctor->id,
                'availability_slot_id' => $slot->id,
                'booking_date' => $occurredAt->toDateString(),
                'booking_time' => sprintf('%02d:00:00', $startHour),
                'consultation_type' => $sequence % 2 === 0 ? ConsultationType::Clinic : ConsultationType::Online,
                'status' => $bookingStatus,
                'price' => $priceCents / 100,
                'payment_status' => $paymentStatus,
                'hold_expires_at' => null,
                'created_at' => $occurredAt,
            ],
        );
        $slot->update(['reserved_booking_id' => $booking->id]);

        $payment = Payment::query()->updateOrCreate(
            ['uuid' => $uuid],
            [
                'booking_id' => $booking->id,
                'patient_id' => $patient->id,
                'doctor_id' => $doctor->id,
                'method' => $method,
                'status' => $paymentStatus,
                'amount_cents' => $priceCents,
                'currency' => 'EGP',
                'commission_bps' => $commissionCents > 0 ? 1000 : 0,
                'commission_amount_cents' => $commissionCents,
                'doctor_amount_cents' => $priceCents - $commissionCents,
                'idempotency_key' => 'demo-payment-'.$sequence,
                'provider' => $method === PaymentMethod::Card ? 'paymob' : null,
                'paid_at' => $isSettled ? $occurredAt : null,
                'failed_at' => $paymentStatus === PaymentStatus::Failed ? $occurredAt : null,
                'refunded_at' => $paymentStatus === PaymentStatus::Refunded ? $occurredAt : null,
                'created_at' => $occurredAt,
            ],
        );

        if ($isSettled) {
            $wallet = Wallet::query()->whereBelongsTo($doctor, 'doctor')->firstOrFail();
            $transactionType = $method === PaymentMethod::Card ? WalletTransactionType::CardCredit : WalletTransactionType::CashCommissionDebit;
            $amountCents = $method === PaymentMethod::Card ? $payment->doctor_amount_cents : -$payment->commission_amount_cents;
            WalletTransaction::query()->updateOrCreate(
                ['idempotency_key' => 'demo-wallet-'.$sequence],
                [
                    'wallet_id' => $wallet->id,
                    'payment_id' => $payment->id,
                    'booking_id' => $booking->id,
                    'type' => $transactionType,
                    'amount_cents' => $amountCents,
                    'balance_after_cents' => $amountCents,
                    'metadata' => ['source' => 'platform_demo_seeder'],
                    'created_at' => $occurredAt,
                ],
            );
        }
    }

    /**
     * @param  Collection<int, User>  $doctors
     * @param  Collection<int, Patient>  $patients
     */
    private function patientActivity(Collection $doctors, Collection $patients): void
    {
        $queries = ['طبيب قلب', 'طبيب أطفال', 'جلدية', 'عظام', 'أسنان', 'باطنة'];

        foreach ($patients as $index => $patient) {
            $doctor = $doctors[$index % $doctors->count()];
            Review::query()->updateOrCreate(
                ['patient_id' => $patient->id, 'user_id' => $doctor->id],
                ['rating' => 3 + ($index % 3), 'comment' => 'تجربة جيدة والطبيب ملتزم بالموعد وشرح الحالة بوضوح.'],
            );
            Favorite::query()->firstOrCreate(['user_id' => $patient->id, 'doctor_id' => $doctor->id]);
            SearchHistory::query()->updateOrCreate(
                ['user_id' => $patient->id, 'query' => $queries[$index % count($queries)], 'source' => 'search'],
            );
            $conversation = Conversation::query()->updateOrCreate(
                ['patient_id' => $patient->id, 'doctor_id' => $doctor->id],
                ['status' => 'active', 'last_message_at' => now()->subMinutes($index + 1)],
            );
            Message::query()->updateOrCreate(
                ['conversation_id' => $conversation->id, 'sender_type' => Patient::class, 'sender_id' => $patient->id, 'body' => 'مرحبًا دكتور، أريد الاستفسار عن موعد الكشف.'],
                ['type' => 'text', 'read_at' => now()],
            );
            Message::query()->updateOrCreate(
                ['conversation_id' => $conversation->id, 'sender_type' => User::class, 'sender_id' => $doctor->id, 'body' => 'أهلًا بك، الموعد متاح ويمكنك تأكيد الحجز من التطبيق.'],
                ['type' => 'text', 'read_at' => null],
            );
        }
    }

    /** @param Collection<int, User> $doctors */
    private function walletWithdrawals(User $admin, Collection $doctors): void
    {
        $doctor = $doctors->first();
        $wallet = Wallet::query()->whereBelongsTo($doctor, 'doctor')->firstOrFail();
        $statuses = [WalletWithdrawalStatus::PendingReview, WalletWithdrawalStatus::Completed, WalletWithdrawalStatus::Cancelled];

        foreach ($statuses as $index => $status) {
            WalletWithdrawal::query()->updateOrCreate(
                ['uuid' => '30000000-0000-4000-8000-'.str_pad((string) ($index + 1), 12, '0', STR_PAD_LEFT)],
                [
                    'doctor_id' => $doctor->id,
                    'wallet_id' => $wallet->id,
                    'amount_cents' => 10000 + ($index * 5000),
                    'currency' => 'EGP',
                    'status' => $status,
                    'idempotency_key' => 'demo-withdrawal-'.($index + 1),
                    'reviewed_by' => $status === WalletWithdrawalStatus::PendingReview ? null : $admin->id,
                    'reviewed_at' => $status === WalletWithdrawalStatus::PendingReview ? null : now()->subDays($index + 1),
                    'rejection_reason' => $status === WalletWithdrawalStatus::Cancelled ? 'بيانات التحويل تحتاج إلى مراجعة.' : null,
                    'balance_before_cents' => $wallet->balance_cents,
                    'balance_after_cents' => $wallet->balance_cents - (10000 + ($index * 5000)),
                ],
            );
        }
    }

    private function content(): void
    {
        $category = FaqCategory::query()->updateOrCreate(['name' => 'الحجوزات']);
        Faq::query()->updateOrCreate(
            ['faq_category_id' => $category->id, 'question' => 'كيف يمكنني حجز موعد؟'],
            ['answer' => 'اختر الطبيب ثم الموعد وطريقة الدفع وأكد الحجز.'],
        );
        Policy::query()->updateOrCreate(
            ['type' => 'privacy'],
            ['content' => 'نحافظ على سرية بيانات المرضى ونستخدمها لتقديم الخدمة الطبية فقط.', 'is_active' => true],
        );
        Policy::query()->updateOrCreate(
            ['type' => 'terms'],
            ['content' => 'باستخدام المنصة فإنك توافق على شروط الحجز والإلغاء والدفع.', 'is_active' => true],
        );
        Promotion::query()->updateOrCreate(
            ['title' => 'خصم الكشف الأول'],
            [
                'description' => 'عرض تجريبي لاختبار العروض داخل التطبيق.',
                'image' => 'promotions/demo-offer.jpg',
                'start_date' => CarbonImmutable::today()->subWeek(),
                'end_date' => CarbonImmutable::today()->addMonth(),
                'is_active' => true,
            ],
        );
    }

    /** @param array<string, mixed> $attributes */
    private function availabilitySlot(User $doctor, string $day, string $startTime, string $endTime, array $attributes): AvailabilitySlot
    {
        $slot = AvailabilitySlot::query()
            ->whereBelongsTo($doctor, 'doctor')
            ->whereDate('day', $day)
            ->whereTime('start_time', $startTime)
            ->whereTime('end_time', $endTime)
            ->first() ?? new AvailabilitySlot;

        $slot->fill([
            'doctor_id' => $doctor->id,
            'day' => $day,
            'start_time' => $startTime,
            'end_time' => $endTime,
            ...$attributes,
        ])->save();

        return $slot;
    }
}
