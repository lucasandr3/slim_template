<?php

declare(strict_types=1);

namespace App\Exceptions;

class ForbiddenException extends ApiException
{
    public function __construct(string $message = 'Acesso negado')
    {
        parent::__construct(
            message: $message,
            httpStatusCode: 403,
            errorCode: 'FORBIDDEN',
            isPublic: true
        );
    }
}
