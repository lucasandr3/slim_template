<?php

declare(strict_types=1);

namespace App\Exceptions;

class UnauthorizedException extends ApiException
{
    public function __construct(string $message = 'Não autorizado')
    {
        parent::__construct(
            message: $message,
            httpStatusCode: 401,
            errorCode: 'UNAUTHORIZED',
            isPublic: true
        );
    }
}
