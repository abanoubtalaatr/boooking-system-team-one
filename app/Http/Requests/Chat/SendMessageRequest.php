<?php

namespace App\Http\Requests\Chat;

use Illuminate\Foundation\Http\FormRequest;

// app/Http/Requests/Chat/SendMessageRequest.php
class SendMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        $conversation = $this->route('conversation');
        $user = auth('patient-api')->user() ?? auth('api')->user();

        return $user && ($user->id === $conversation->patient_id || $user->id === $conversation->doctor_id);
    }

    public function rules(): array
    {
        return [
            'type'       => ['required', 'in:text,voice,image,file'],
            'body'       => ['required_if:type,text', 'nullable', 'string', 'max:2000'],
            'attachment' => [
                'required_unless:type,text',
                'file',
                'max:20480', // 20MB
                'mimes:jpg,jpeg,png,mp3,m4a,wav,pdf,docx',
            ],
        ];
    }
}
