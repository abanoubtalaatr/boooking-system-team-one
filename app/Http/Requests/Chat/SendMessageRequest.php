<?php

namespace App\Http\Requests\Chat;

use Illuminate\Foundation\Http\FormRequest;

class SendMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        $conversationId = $this->input('conversation_id');

        if (! $conversationId) {
            return false;
        }

        $conversation = \App\Models\Conversation::find($conversationId);
        $user = auth()->user();

        if (! $conversation || ! $user) {
            return false;
        }

        return $user->id === $conversation->patient_id || $user->id === $conversation->doctor_id;
    }

    public function rules(): array
    {
        return [
            'conversation_id' => ['required', 'exists:conversations,id'],
            'type' => ['required', 'in:text,voice,image,file'],
            'body' => ['required_if:type,text', 'nullable', 'string', 'max:2000'],
            'attachment' => [
                'required_unless:type,text',
                'file',
                'max:20480',
                'mimes:jpg,jpeg,png,mp3,m4a,wav,pdf,docx',
            ],
        ];
    }
}