<?php

namespace App\Http\Requests\Doctor\Web;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateAvailabilitySlotRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Ownership + "not booked" are checked in the controller with
        // abort_if(), not here — form requests only handle input shape.
        return true;
    }

    public function rules(): array
    {
        return [
            'day'        => ['required', 'date', 'after_or_equal:today'],
            'start_time' => ['required', 'date_format:H:i'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if (! $this->filled(['day', 'start_time'])) {
                return;
            }

            $day = Carbon::parse($this->input('day'));

            if (! $day->isToday()) {
                return;
            }

            $startDateTime = Carbon::parse($this->input('day') . ' ' . $this->input('start_time'));

            if ($startDateTime->lessThanOrEqualTo(now())) {
                $validator->errors()->add('start_time', 'لا يمكنك نقل الموعد إلى وقت قد مضى.');
            }
        });
    }
}