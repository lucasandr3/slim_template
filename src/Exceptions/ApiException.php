<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;
use Throwable;

class ApiException extends Exception
{
    protected int $httpStatusCode;
    protected string $errorCode;
    protected array $details;
    protected bool $isPublic;

    public function __construct(
        string $message = '',
        int $httpStatusCode = 500,
        string $errorCode = 'INTERNAL_ERROR',
        array $details = [],
        bool $isPublic = false,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, 0, $previous);
        
        $this->httpStatusCode = $httpStatusCode;
        $this->errorCode = $errorCode;
        $this->details = $details;
        $this->isPublic = $isPublic;
    }

    public function getHttpStatusCode(): int
    {
        return $this->httpStatusCode;
    }

    public function getErrorCode(): string
    {
        return $this->errorCode;
    }

    public function getDetails(): array
    {
        return $this->details;
    }

    public function isPublic(): bool
    {
        return $this->isPublic;
    }

    public function toArray(): array
    {
        $response = [
            'error' => [
                'code' => $this->errorCode,
                'message' => $this->message,
                'timestamp' => date('Y-m-d H:i:s'),
                'path' => $_SERVER['REQUEST_URI'] ?? '/'
            ]
        ];

        if ($this->isPublic && !empty($this->details)) {
            $response['error']['details'] = $this->details;
        }

        if ($_ENV['APP_DEBUG'] === 'true') {
            $response['error']['debug'] = [
                'file' => $this->getFile(),
                'line' => $this->getLine(),
                'trace' => $this->getTraceAsString()
            ];
        }

        return $response;
    }
}
