<?php

namespace App\Http\Requests\Admin;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAdminUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        $admin = $this->route('admin');

        return $admin instanceof User
            && $admin->isAdmin()
            && ! $this->isProtected($admin)
            && ($this->user()?->can('admins.update') ?? false);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->route('admin'))],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ];
    }

    private function isProtected(User $admin): bool
    {
        return $admin->id === 1 && $admin->email === 'camila.herman@example.net';
    }
}
