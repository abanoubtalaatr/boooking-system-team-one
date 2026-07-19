<?php

namespace App\Http\Requests\Admin;

use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAdminStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        $admin = $this->route('admin');

        return $admin instanceof User
            && $admin->isAdmin()
            && ! $admin->is($this->user())
            && ! ($admin->id === 1 && $admin->email === 'camila.herman@example.net')
            && ($this->user()?->can('admins.status') ?? false);
    }

    public function rules(): array
    {
        return ['status' => ['required', Rule::in([UserStatus::Active->value, UserStatus::Suspended->value])]];
    }
}
