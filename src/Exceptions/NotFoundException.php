<?php

declare(strict_types=1);

namespace App\Exceptions;

class NotFoundException extends ApiException
{
    public function __construct(string $message = 'Recurso não encontrado', string $resource = '')
    {
        $details = [];
        if (!empty($resource)) {
            $details['resource'] = $resource;
        }

        parent::__construct(
            message: $message,
            httpStatusCode: 404,
            errorCode: 'NOT_FOUND',
            details: $details,
            isPublic: true
        );
    }
}
