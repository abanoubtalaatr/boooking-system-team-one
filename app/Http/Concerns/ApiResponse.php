<?php

declare(strict_types=1);

namespace App\Http\Concerns;

use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    /**
     * @param  array<string, mixed>  $data
     */
    protected function successResponse(string $message, array $data = [], int $status = 200): JsonResponse
    {
        return response()->json(array_filter([
            'message' => $message,
            'data' => $data === [] ? null : $data,
        ], fn (mixed $value): bool => $value !== null), $status);
    }
}
