<?php

namespace App\Http\Requests\Web\Doctor\Conversation;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class SendMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'body'       => ['nullable', 'string', 'max:2000'],
            'attachment' => ['nullable', 'file', 'max:20480'],
        ];
    }

    /**
     * A message must have either a body or an attachment.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if (empty($this->input('body')) && ! $this->hasFile('attachment')) {
                $validator->errors()->add('body', 'الرجاء كتابة رسالة أو إرفاق ملف.');
            }
        });
    }
}