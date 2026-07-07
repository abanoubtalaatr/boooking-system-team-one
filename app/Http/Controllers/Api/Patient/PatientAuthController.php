<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Patient;

use App\Actions\PatientAuth\ForgotPatientPasswordAction;
use App\Actions\PatientAuth\GeneratePatientOtpAction;
use App\Actions\PatientAuth\LoginPatientAction;
use App\Actions\PatientAuth\LogoutPatientAction;
use App\Actions\PatientAuth\RegisterPatientAction;
use App\Actions\PatientAuth\ResetPatientPasswordAction;
use App\Actions\PatientAuth\VerifyPatientAccountAction;
use App\Enums\PatientOtpType;
use App\Http\Controllers\Controller;
use App\Http\Requests\PatientAuth\ForgotPatientPasswordRequest;
use App\Http\Requests\PatientAuth\LoginPatientRequest;
use App\Http\Requests\PatientAuth\RegisterPatientRequest;
use App\Http\Requests\PatientAuth\ResendPatientOtpRequest;
use App\Http\Requests\PatientAuth\ResetPatientPasswordRequest;
use App\Http\Requests\PatientAuth\VerifyPatientOtpRequest;
use App\Http\Resources\PatientResource;
use App\Models\Patient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PatientAuthController extends Controller
{
    public function register(
        RegisterPatientRequest $request,
        RegisterPatientAction $registerPatient,
    ): JsonResponse {
        $patient = $registerPatient($request->validated());

        return response()->json([
            'message' => __('Patient registered successfully. Please verify your phone number.'),
            'data' => new PatientResource($patient),
        ], 201);
    }

    public function verifyOtp(
        VerifyPatientOtpRequest $request,
        VerifyPatientAccountAction $verifyPatientAccount,
    ): JsonResponse {
        $patient = Patient::query()
            ->where('phone', $request->validated('phone'))
            ->firstOrFail();

        $patient = $verifyPatientAccount($patient, $request->validated('otp'));

        return response()->json([
            'message' => __('Patient account verified successfully.'),
            'data' => new PatientResource($patient),
        ]);
    }

    public function resendOtp(
        ResendPatientOtpRequest $request,
        GeneratePatientOtpAction $generatePatientOtp,
    ): JsonResponse {
        $patient = Patient::query()
            ->where('phone', $request->validated('phone'))
            ->firstOrFail();

        if (! $patient->isVerified()) {
            $generatePatientOtp($patient, PatientOtpType::AccountVerification);
        }

        return response()->json([
            'message' => __('OTP sent successfully.'),
        ]);
    }

    public function login(
        LoginPatientRequest $request,
        LoginPatientAction $loginPatient,
    ): JsonResponse {
        $result = $loginPatient(
            $request->validated('phone'),
            $request->validated('password'),
        );

        return response()->json([
            'message' => __('Logged in successfully.'),
            'data' => [
                'patient' => new PatientResource($result['patient']),
                'token' => $result['token'],
                'token_type' => 'Bearer',
            ],
        ]);
    }

    public function forgotPassword(
        ForgotPatientPasswordRequest $request,
        ForgotPatientPasswordAction $forgotPatientPassword,
    ): JsonResponse {
        $forgotPatientPassword($request->validated('phone'));

        return response()->json([
            'message' => __('Password reset OTP sent successfully.'),
        ]);
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

        return response()->json([
            'message' => __('Password reset successfully.'),
            'data' => new PatientResource($patient),
        ]);
    }

    public function logout(Request $request, LogoutPatientAction $logoutPatient): JsonResponse
    {
        /** @var Patient $patient */
        $patient = $request->user();

        $logoutPatient($patient);

        return response()->json([
            'message' => __('Logged out successfully.'),
        ]);
    }
}
