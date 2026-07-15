<?php

namespace App\Http\Requests\Chat;

use Illuminate\Foundation\Http\FormRequest;

class ListMessagesRequest extends FormRequest
{
    public function authorize(): bool
    {
        $conversation = \App\Models\Conversation::find($this->query('conversation_id'));
        $user = auth('patient')->user() ?? auth('api')->user() ?? auth()->user();

        if (! $conversation || ! $user) {
            return false;
        }

        return $user->id === $conversation->patient_id || $user->id === $conversation->doctor_id;
    }

    public function rules(): array
    {
        return [
            'conversation_id' => ['required', 'exists:conversations,id'],
        ];
    }
}
