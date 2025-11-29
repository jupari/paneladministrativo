<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Throwable;

class AppValidationException extends Exception
{
    protected int $status;
    protected array $errors;

    public function __construct(
        string $message = 'Validation error',
        int $status = 422,
        array $errors = [],
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $status, $previous);
        $this->status = $status;
        $this->errors = $errors;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    /** Devuelve respuesta JSON cuando se lanza la excepción */
    public function render($request): JsonResponse
    {
        $payload = ['message' => $this->getMessage()];
        if (!empty($this->errors)) {
            $payload['errors'] = $this->errors; // opcional: detalle por campo
        }
        return response()->json($payload, $this->status);
    }
}
