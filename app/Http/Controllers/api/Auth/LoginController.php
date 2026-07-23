<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Traits\ApiResponse;

/**
 * Minimal Sanctum token login — just enough to get a Bearer token for testing
 * the Doctor/Chat modules in Postman. Swap this out for your project's real
 * auth flow (or extend it: refresh tokens, device names, rate limiting, etc.)
 * before shipping.
 */
class LoginController extends Controller
{
    use ApiResponse;

    public function __invoke(LoginRequest $request): JsonResponse
    {
        $user = User::where("email", $request->validated("email"))->first();

        if (! $user || $user->isSuspended() || ! Hash::check($request->validated("password"), $user->password)) {
            throw ValidationException::withMessages([
                "email" => ["The provided credentials are incorrect."],
            ]);
        }

        // Revoke previous tokens for this device/session name if you want single-session
        // behavior; skipped here to keep this a minimal testing helper.
        $token = $user->createToken("api-token")->plainTextToken;

        return $this->apiResponse([
            "token" => $token,
            "user" => [
                "id" => $user->id,
                "name" => $user->name,
                "email" => $user->email,
                "roles" => $user->getRoleNames(),
                "status" => $user->status,
            ],
        ], "Logged in.");
    }
}
