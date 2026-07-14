<?php

namespace App\Http\Requests\Doctor;

use Illuminate\Foundation\Http\FormRequest;

class StoreAvailabilitySlotRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->role === "doctor";
    }

    public function rules(): array
    {
        return [
            "day" => ["required", "date", "after_or_equal:today"],
            "start_time" => ["required", "date_format:H:i"],
            "end_time" => ["required", "date_format:H:i", "after:start_time"],
        ];
    }
}
