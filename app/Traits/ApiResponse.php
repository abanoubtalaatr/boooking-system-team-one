<?php

namespace App\Traits;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response as Status;

trait ApiResponse
{
    /**
     * if the request is successful, return a unified success response (Success Response)
     */
    protected function apiResponse(mixed $data = null, string $message = 'Success', int $statusCode = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $statusCode);
    }

    /**
     * if there is an error, return a unified error response (Error Response)
     */
    protected function errorResponse(string $message = 'Error occurred', int $statusCode = 400): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data' => null,
        ], $statusCode);
    }

    /**************** */

    private function buildResponse(bool $success, int $status, ?string $message = null, mixed $data = null): array
    {
        $isPaginated = $data instanceof ResourceCollection
            && $data->resource instanceof LengthAwarePaginator;

        return [
            'success' => $success,
            'message' => $message,
            'data' => $data ?: null,
            'paginate' => $isPaginated ? [
                'per_page' => $data->resource->perPage(),
                'current_page' => $data->resource->currentPage(),
                'last_page' => $data->resource->lastPage(),
            ] : null,
        ];
    }

    public function success(?string $message, array $data): JsonResponse
    {
        return response()->json(
            $this->buildResponse(true, Status::HTTP_OK, $message, $data),
            Status::HTTP_OK
        );
    }

    public function created(?string $message, array $data): JsonResponse
    {
        return response()->json(
            $this->buildResponse(true, Status::HTTP_CREATED, $message, $data),
            Status::HTTP_CREATED
        );
    }

    public function noContent(): JsonResponse
    {
        return response()->json(
            $this->buildResponse(true, Status::HTTP_NO_CONTENT),
            Status::HTTP_NO_CONTENT
        );
    }

    public function error(?string $message = null, mixed $data = null, int $status = Status::HTTP_BAD_REQUEST): JsonResponse
    {
        return response()->json(
            $this->buildResponse(false, $status, $message, $data),
            $status
        );
    }

    public function unauthorized(?string $message = null): JsonResponse
    {
        return response()->json(
            $this->buildResponse(false, Status::HTTP_UNAUTHORIZED, $message ?? __('lang.unauthorized')),
            Status::HTTP_UNAUTHORIZED
        );
    }

    public function forbidden(?string $message = null): JsonResponse
    {
        return response()->json(
            $this->buildResponse(false, Status::HTTP_FORBIDDEN, $message ?? __('lang.forbidden')),
            Status::HTTP_FORBIDDEN
        );
    }

    public function notFound(?string $message = null): JsonResponse
    {
        return response()->json(
            $this->buildResponse(false, Status::HTTP_NOT_FOUND, $message ?? __('lang.not_found')),
            Status::HTTP_NOT_FOUND
        );
    }

    public function internalError(?string $message = null): JsonResponse
    {
        return response()->json(
            $this->buildResponse(false, Status::HTTP_INTERNAL_SERVER_ERROR, $message ?? __('lang.server_error')),
            Status::HTTP_INTERNAL_SERVER_ERROR
        );
    }

    /**************** */

    /**
     * Authorize an action via Gate/Policies — same signature as Laravel's own
     * AuthorizesRequests::authorize(), so every existing call in the Doctor/Chat
     * controllers ($this->authorize('update', $booking)) keeps working unchanged.
     *
     * Throws AuthorizationException on failure instead of returning a response,
     * matching Laravel's convention: it lets the request short-circuit right where
     * the check happens, rather than forcing every caller to check a return value.
     * Render it centrally (see the bootstrap/app.php snippet below) so it comes
     * back through $this->forbidden() and matches this trait's response envelope.
     *
     * @throws AuthorizationException
     */
    public function authorize(string $ability, mixed $arguments = []): void
    {
        Gate::authorize($ability, $arguments);
    }

    /**
     * Optional: only needed if some code wants to short-circuit and respond
     * immediately instead of throwing (rare — most code should just call
     * authorize() above and let the exception bubble up).
     */
    protected function authorizeOrRespond(string $ability, mixed $arguments = []): ?JsonResponse
    {
        if (Gate::denies($ability, $arguments)) {
            return $this->forbidden();
        }

        return null;
    }
}
