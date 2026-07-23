<?php

namespace App\Livewire\Admin;

use App\Enums\BookingStatus;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class PatientProfile extends Component
{
    use WithPagination;

    public Patient $patient;

    #[Url(as: 'tab', except: 'bookings')]
    public string $activeTab = 'bookings';

    #[Url(as: 'q', except: '')]
    public string $search = '';

    public int $visitedDoctorsCount = 0;

    public function mount(Patient $patient): void
    {
        Gate::authorize('patients.view');
        $this->patient = $patient->loadCount(['patientBookings', 'reviews']);
        $this->visitedDoctorsCount = $patient->patientBookings()
            ->where('status', BookingStatus::Completed)
            ->distinct()
            ->count('doctor_id');
    }

    public function setTab(string $tab): void
    {
        abort_unless(in_array($tab, ['bookings', 'visits', 'reviews'], true), 404);

        $this->activeTab = $tab;
        $this->search = '';
        $this->resetPage('bookingsPage');
        $this->resetPage('visitsPage');
        $this->resetPage('reviewsPage');
    }

    public function updatedSearch(): void
    {
        $this->resetPage(match ($this->activeTab) {
            'visits' => 'visitsPage',
            'reviews' => 'reviewsPage',
            default => 'bookingsPage',
        });
    }

    public function render(): View
    {
        Gate::authorize('patients.view');

        if (! in_array($this->activeTab, ['bookings', 'visits', 'reviews'], true)) {
            $this->activeTab = 'bookings';
        }

        $bookings = $visits = $reviews = null;
        $search = trim($this->search);

        if ($this->activeTab === 'bookings') {
            $paymentTable = (new Payment)->getTable();
            $bookings = $this->patient->patientBookings()
                ->select([
                    'id', 'booking_number', 'patient_id', 'doctor_id', 'booking_date', 'booking_time',
                    'consultation_type', 'status', 'price',
                ])
                ->with([
                    'doctor:id,name,email',
                    'latestPayment' => function (HasOne $query) use ($paymentTable): void {
                        $query->select([
                            "{$paymentTable}.id",
                            "{$paymentTable}.booking_id",
                            "{$paymentTable}.method",
                            "{$paymentTable}.status",
                        ]);
                    },
                ])
                ->when($search !== '', function (Builder $query) use ($search): void {
                    $query->where(function (Builder $query) use ($search): void {
                        $query->where('booking_number', 'like', "%{$search}%")
                            ->orWhereIn('doctor_id', $this->matchingDoctors($search));
                    });
                })
                ->orderByDesc('booking_date')
                ->orderByDesc('booking_time')
                ->paginate(10, pageName: 'bookingsPage');
        }

        if ($this->activeTab === 'visits') {
            $visits = $this->patient->patientBookings()
                ->select([
                    'id', 'booking_number', 'patient_id', 'doctor_id', 'booking_date', 'booking_time',
                    'consultation_type', 'status',
                ])
                ->where('status', BookingStatus::Completed)
                ->with([
                    'doctor:id,name,email',
                    'doctor.doctorProfile:id,user_id,specialization_id',
                    'doctor.doctorProfile.specialty:id,name',
                ])
                ->when($search !== '', fn (Builder $query) => $query->whereIn('doctor_id', $this->matchingDoctors($search)))
                ->orderByDesc('booking_date')
                ->orderByDesc('booking_time')
                ->paginate(10, pageName: 'visitsPage');
        }

        if ($this->activeTab === 'reviews') {
            $reviews = $this->patient->reviews()
                ->select(['id', 'user_id', 'patient_id', 'rating', 'comment', 'created_at'])
                ->with([
                    'doctor:id,name,email',
                    'doctor.doctorProfile:id,user_id,specialization_id',
                    'doctor.doctorProfile.specialty:id,name',
                ])
                ->when($search !== '', function (Builder $query) use ($search): void {
                    $query->where(function (Builder $query) use ($search): void {
                        $query->where('comment', 'like', "%{$search}%")
                            ->orWhereIn('user_id', $this->matchingDoctors($search));
                    });
                })
                ->latest('id')
                ->paginate(10, pageName: 'reviewsPage');
        }

        return view('livewire.admin.patient-profile', compact('bookings', 'visits', 'reviews'));
    }

    private function matchingDoctors(string $search): Builder
    {
        return User::query()
            ->select('id')
            ->where(function (Builder $query) use ($search): void {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
    }
}
