<?php

declare(strict_types=1);

namespace App\Exceptions;

class ValidationException extends ApiException
{
    public function __construct(string $message = 'Dados de entrada inválidos', array $details = [])
    {
        parent::__construct(
            message: $message,
            httpStatusCode: 422,
            errorCode: 'VALIDATION_ERROR',
            details: $details,
            isPublic: true
        );
    }
}
