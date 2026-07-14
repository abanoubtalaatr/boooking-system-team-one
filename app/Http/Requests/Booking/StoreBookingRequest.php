<?php

namespace App\Http\Requests\Booking;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBookingRequest extends FormRequest
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
            'doctor_id' => [
                'required',
                'exists:users,id',
            ],

            'availability_slot_id' => [
                'required',
                'exists:availability_slots,id',
            ],

            'consultation_type' => [
                'required',
                Rule::in([
                    'online',
                    'clinic',
                    'home',
                ]),
            ],
        ];
    }
}
