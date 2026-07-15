<?php

namespace App\Exceptions;

use Illuminate\Contracts\Debug\ShouldntReport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use RuntimeException;

class NoShowReportDomainException extends RuntimeException implements ShouldntReport
{
    public function __construct(
        string $message,
        public readonly string $errorCode,
        public readonly int $status = 422,
    ) {
        parent::__construct($message);
    }

    public function render(Request $request): JsonResponse|RedirectResponse
    {
        if (! $request->expectsJson() && ! $request->is('api/*')) {
            return back()->withErrors(['no_show' => $this->getMessage()])->withInput();
        }

        return response()->json([
            'message' => $this->getMessage(),
            'error' => ['code' => $this->errorCode],
        ], $this->status);
    }
}
