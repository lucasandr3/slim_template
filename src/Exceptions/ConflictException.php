<?php

declare(strict_types=1);

namespace App\Exceptions;

class ConflictException extends ApiException
{
    public function __construct(string $message = 'Conflito de dados', array $details = [])
    {
        parent::__construct(
            message: $message,
            httpStatusCode: 409,
            errorCode: 'CONFLICT',
            details: $details,
            isPublic: true
        );
    }
}
