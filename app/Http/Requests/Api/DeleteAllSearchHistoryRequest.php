<?php

namespace App\Http\Requests\Api;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DeleteAllSearchHistoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'source' => ['nullable', 'string', Rule::in(['chat', 'search', 'favorite'])],
            'confirm' => ['required_without:source', 'accepted'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'source.in' => 'The source must be one of: chat, search, favorite.',
            'confirm.required_without' => 'You must confirm before clearing all search history.',
            'confirm.accepted' => 'You must confirm before clearing all search history.',
        ];
    }
}
