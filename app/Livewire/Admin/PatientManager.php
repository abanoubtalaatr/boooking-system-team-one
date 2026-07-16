<?php

namespace App\Livewire\Admin;

use App\Livewire\Forms\Admin\PatientForm;
use App\Models\Patient;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithPagination;

class PatientManager extends Component
{
    use WithPagination;

    /** @var array<string, string> */
    public const PERMISSIONS = [
        'view' => 'patients.view',
        'create' => 'patients.create',
        'update' => 'patients.update',
        'delete' => 'patients.delete',
    ];

    public PatientForm $form;

    public string $search = '';

    public int $perPage = 15;

    public bool $showForm = false;

    public string $successMessage = '';

    /** @var list<int> */
    public array $perPageOptions = [15, 30, 50, 100];

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function setPerPage(int $perPage): void
    {
        if (! in_array($perPage, $this->perPageOptions, true)) {
            return;
        }

        $this->perPage = $perPage;
        $this->resetPage();
    }

    public function create(): void
    {
        Gate::authorize(self::PERMISSIONS['create']);
        $this->form->resetForm();
        $this->successMessage = '';
        $this->showForm = true;
    }

    public function edit(Patient $patient): void
    {
        Gate::authorize(self::PERMISSIONS['update']);
        $this->form->setPatient($patient);
        $this->successMessage = '';
        $this->showForm = true;
    }

    public function save(): void
    {
        $isEditing = $this->form->patient !== null;
        Gate::authorize(self::PERMISSIONS[$isEditing ? 'update' : 'create']);

        if ($isEditing) {
            $this->form->update();
            $this->successMessage = 'تم تحديث بيانات المريض بنجاح.';
        } else {
            $this->form->store();
            $this->successMessage = 'تم إنشاء حساب المريض بنجاح.';
        }

        $this->showForm = false;
        $this->form->resetForm();
        $this->resetPage();
    }

    public function delete(Patient $patient): void
    {
        Gate::authorize(self::PERMISSIONS['delete']);
        $patient->tokens()->delete();
        $patient->delete();
        $this->successMessage = 'تم حذف حساب المريض بنجاح.';
        $this->resetPage();
    }

    public function closeForm(): void
    {
        $this->showForm = false;
        $this->form->resetForm();
    }

    public function render(): View
    {
        Gate::authorize(self::PERMISSIONS['view']);

        $search = trim($this->search);
        $patients = Patient::query()
            ->select(['id', 'name', 'phone', 'email', 'birthdate', 'verified_at', 'created_at'])
            ->withCount('patientBookings')
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->latest('id')
            ->paginate($this->perPage);

        return view('livewire.admin.patient-manager', ['patients' => $patients]);
    }
}
