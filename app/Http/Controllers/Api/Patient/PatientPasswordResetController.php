<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Patient;

use App\Actions\PatientAuth\ForgotPatientPasswordAction;
use App\Actions\PatientAuth\ResetPatientPasswordAction;
use App\Http\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\PatientAuth\ForgotPatientPasswordRequest;
use App\Http\Requests\PatientAuth\ResetPatientPasswordRequest;
use App\Http\Resources\PatientResource;
use App\Models\Patient;
use Illuminate\Http\JsonResponse;

class PatientPasswordResetController extends Controller
{
    use ApiResponse;

    public function forgotPassword(
        ForgotPatientPasswordRequest $request,
        ForgotPatientPasswordAction $forgotPatientPassword,
    ): JsonResponse {
        $forgotPatientPassword($request->validated('phone'));

        return $this->successResponse(__('Password reset OTP sent successfully.'));
    }

    public function resetPassword(
        ResetPatientPasswordRequest $request,
        ResetPatientPasswordAction $resetPatientPassword,
    ): JsonResponse {
        $patient = Patient::query()
            ->where('phone', $request->validated('phone'))
            ->firstOrFail();

        $patient = $resetPatientPassword(
            $patient,
            $request->validated('otp'),
            $request->validated('password'),
        );

        return $this->successResponse(
            __('Password reset successfully.'),
            ['patient' => new PatientResource($patient)],
        );
    }
}
