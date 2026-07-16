<?php

namespace App\Http\Requests\Doctor\Web;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreAvailabilitySlotRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'day'        => ['required', 'date', 'after_or_equal:today'],
            'start_time' => ['required'],
            'end_time'   => ['required', 'after:start_time'],
        ];
    }

    /**
     * Extra validation that needs access to multiple fields at once
     * (a single "after_or_equal:today" rule can't catch "today, but the
     * chosen start time has already passed").
     */
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
                $validator->errors()->add('start_time', 'لا يمكنك إنشاء موعد في وقت قد مضى.');
            }
        });
    }
}