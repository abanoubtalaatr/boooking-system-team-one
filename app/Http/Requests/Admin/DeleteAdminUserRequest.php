<?php

namespace App\Http\Requests\Admin;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class DeleteAdminUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        $admin = $this->route('admin');

        return $admin instanceof User
            && $admin->isAdmin()
            && ! $admin->is($this->user())
            && ! ($admin->id === 1 && $admin->email === 'camila.herman@example.net')
            && ($this->user()?->can('admins.delete') ?? false);
    }

    public function rules(): array
    {
        return [];
    }
}
