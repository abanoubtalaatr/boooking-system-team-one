<?php

namespace App\Http\Requests\Chat;

use Illuminate\Foundation\Http\FormRequest;

class StartConversationRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Any authenticated patient may start a conversation with any doctor.
        return $this->user()->role === "patient";
    }

    public function rules(): array
    {
        return [
            "doctor_id" => ["required", "uuid", "exists:users,id"],
        ];
    }
}
