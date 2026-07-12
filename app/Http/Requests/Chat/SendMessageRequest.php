<?php

namespace App\Http\Requests\Chat;

use Illuminate\Foundation\Http\FormRequest;

class SendMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        $conversation = $this->route("conversation");
        $userId = $this->user()->id;

        return $conversation->patient_id === $userId || $conversation->doctor_id === $userId;
    }

    public function rules(): array
    {
        return [
            "type" => ["required", "in:text,image,file,voice"],
            "content" => ["required_if:type,text", "nullable", "string", "max:5000"],
            "file" => [
                "required_unless:type,text",
                "file",
                "max:20480", // 20MB ceiling; per-type mimes enforced below
                function ($attribute, $value, $fail) {
                    if (! $value) {
                        return;
                    }

                    $mimeRules = [
                        "image" => ["jpg", "jpeg", "png", "webp"],
                        "file" => ["pdf", "doc", "docx"],
                        "voice" => ["mp3", "wav", "m4a", "ogg"],
                    ];

                    $type = $this->input("type");

                    if (isset($mimeRules[$type]) && ! in_array($value->getClientOriginalExtension(), $mimeRules[$type], true)) {
                        $fail("The {$attribute} must be a valid {$type} file.");
                    }
                },
            ],
        ];
    }
}
