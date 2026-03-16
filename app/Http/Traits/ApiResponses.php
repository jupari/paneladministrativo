<?php

namespace App\Http\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponses
{
    protected function successResponse(mixed $data, string $message = '', int $status = 200): JsonResponse
    {
        $payload = ['data' => $data];
        if ($message) {
            $payload['message'] = $message;
        }
        return response()->json($payload, $status);
    }

    protected function paginatedResponse(\Illuminate\Pagination\LengthAwarePaginator $paginator, mixed $data): JsonResponse
    {
        return response()->json([
            'data' => $data,
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
            ],
        ]);
    }

    protected function errorResponse(string $message, int $status = 400, array $errors = []): JsonResponse
    {
        $payload = ['message' => $message];
        if (!empty($errors)) {
            $payload['errors'] = $errors;
        }
        return response()->json($payload, $status);
    }
}
