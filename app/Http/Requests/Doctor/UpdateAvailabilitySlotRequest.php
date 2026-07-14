<?php

namespace App\Http\Requests\Doctor;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAvailabilitySlotRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->id === $this->route("availability_slot")->doctor->user_id;
    }

    public function rules(): array
    {
        return [
            "day" => ["sometimes", "date", "after_or_equal:today"],
            "start_time" => ["sometimes", "date_format:H:i"],
            "end_time" => ["sometimes", "date_format:H:i", "after:start_time"],
        ];
    }
}
