<?php

namespace App\Livewire\Forms\Admin;

use App\Models\Patient;
use Illuminate\Validation\Rule;
use Livewire\Form;

class PatientForm extends Form
{
    public ?Patient $patient = null;

    public string $name = '';

    public string $phone = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    public string $birthdate = '';

    public string $profile_photo = '';

    public string $latitude = '';

    public string $longitude = '';

    public bool $verified = false;

    /** @return array<string, array<int, mixed>> */
    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'phone' => ['required', 'string', 'max:30', Rule::unique(Patient::class, 'phone')->ignore($this->patient)],
            'email' => ['required', 'email', 'max:255', Rule::unique(Patient::class, 'email')->ignore($this->patient)],
            'password' => [$this->patient ? 'nullable' : 'required', 'string', 'min:8', 'confirmed'],
            'birthdate' => ['nullable', 'date', 'before_or_equal:today'],
            'profile_photo' => ['nullable', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'verified' => ['boolean'],
        ];
    }

    /** @return array<string, string> */
    protected function messages(): array
    {
        return [
            'name.required' => 'اسم المريض مطلوب.',
            'phone.required' => 'رقم الهاتف مطلوب.',
            'phone.unique' => 'رقم الهاتف مستخدم بالفعل.',
            'email.required' => 'البريد الإلكتروني مطلوب.',
            'email.email' => 'صيغة البريد الإلكتروني غير صحيحة.',
            'email.unique' => 'البريد الإلكتروني مستخدم بالفعل.',
            'password.required' => 'كلمة المرور مطلوبة.',
            'password.min' => 'كلمة المرور يجب ألا تقل عن 8 أحرف.',
            'password.confirmed' => 'تأكيد كلمة المرور غير مطابق.',
            'birthdate.before_or_equal' => 'تاريخ الميلاد لا يمكن أن يكون في المستقبل.',
            'latitude.between' => 'خط العرض يجب أن يكون بين -90 و90.',
            'longitude.between' => 'خط الطول يجب أن يكون بين -180 و180.',
        ];
    }

    public function setPatient(Patient $patient): void
    {
        $this->patient = $patient;
        $this->name = $patient->name;
        $this->phone = $patient->phone;
        $this->email = $patient->email;
        $this->birthdate = $patient->birthdate?->format('Y-m-d') ?? '';
        $this->profile_photo = $patient->profile_photo ?? '';
        $this->latitude = $patient->latitude ?? '';
        $this->longitude = $patient->longitude ?? '';
        $this->verified = $patient->isVerified();
        $this->password = '';
        $this->password_confirmation = '';
        $this->resetValidation();
    }

    public function store(): Patient
    {
        return Patient::query()->create($this->validatedData());
    }

    public function update(): Patient
    {
        $patient = $this->patient ?? throw new \LogicException('A patient must be selected before updating.');
        $patient->update($this->validatedData());

        return $patient->refresh();
    }

    public function resetForm(): void
    {
        $this->reset();
        $this->resetValidation();
    }

    /** @return array<string, mixed> */
    private function validatedData(): array
    {
        $data = $this->validate();
        unset($data['password_confirmation'], $data['verified']);

        if ($data['password'] === '') {
            unset($data['password']);
        }

        foreach (['birthdate', 'profile_photo', 'latitude', 'longitude'] as $nullableField) {
            $data[$nullableField] = filled($data[$nullableField]) ? $data[$nullableField] : null;
        }

        $data['verified_at'] = $this->verified ? ($this->patient?->verified_at ?? now()) : null;

        return $data;
    }
}
