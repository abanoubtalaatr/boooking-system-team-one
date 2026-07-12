<?php

namespace App\Http\Requests\Chat;

use Illuminate\Foundation\Http\FormRequest;

// app/Http/Requests/Chat/StartConversationRequest.php
class StartConversationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('patient-api')->check();
    }

    public function rules(): array
    {
        return [
            'doctor_id' => ['required', 'exists:users,id'],
        ];
    }
}