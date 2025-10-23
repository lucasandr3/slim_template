<?php

declare(strict_types=1);

namespace App\Services;

use Psr\Http\Message\ResponseInterface as Response;
use App\Http\Resources\Resource;
use App\Http\Resources\ResourceCollection;

class ApiResponseService
{
    public static function success(Response $response, mixed $data = null, string $message = 'Sucesso', int $statusCode = 200): Response
    {
        // Processar dados se for Resource ou ResourceCollection
        $processedData = self::processData($data);

        $responseData = [
            'success' => true,
            'message' => $message,
            'timestamp' => date('Y-m-d H:i:s'),
            'data' => $processedData
        ];

        $response->getBody()->write(json_encode($responseData));
        
        return $response
            ->withStatus($statusCode)
            ->withHeader('Content-Type', 'application/json');
    }

    /**
     * Processa dados para Resources
     */
    private static function processData(mixed $data): mixed
    {
        if ($data instanceof Resource) {
            return $data->toArray();
        }

        if ($data instanceof ResourceCollection) {
            return $data->toArray();
        }

        return $data;
    }

    /**
     * Resposta com Resource
     */
    public static function resource(Response $response, Resource $resource, string $message = 'Sucesso', int $statusCode = 200): Response
    {
        return self::success($response, $resource, $message, $statusCode);
    }

    /**
     * Resposta com ResourceCollection
     */
    public static function collection(Response $response, ResourceCollection $collection, string $message = 'Sucesso', int $statusCode = 200): Response
    {
        return self::success($response, $collection, $message, $statusCode);
    }

    public static function error(Response $response, string $message = 'Erro', int $statusCode = 400, array $details = []): Response
    {
        $responseData = [
            'success' => false,
            'error' => [
                'message' => $message,
                'code' => self::getErrorCodeFromStatusCode($statusCode),
                'timestamp' => date('Y-m-d H:i:s'),
                'path' => $_SERVER['REQUEST_URI'] ?? '/'
            ]
        ];

        if (!empty($details)) {
            $responseData['error']['details'] = $details;
        }

        if ($_ENV['APP_DEBUG'] === 'true') {
            $responseData['error']['debug'] = [
                'status_code' => $statusCode
            ];
        }

        $response->getBody()->write(json_encode($responseData));
        
        return $response
            ->withStatus($statusCode)
            ->withHeader('Content-Type', 'application/json');
    }

    public static function validationError(Response $response, array $errors): Response
    {
        return self::error(
            $response,
            'Dados de entrada inválidos',
            422,
            ['validation_errors' => $errors]
        );
    }

    public static function notFound(Response $response, string $resource = 'Recurso'): Response
    {
        return self::error(
            $response,
            "{$resource} não encontrado",
            404
        );
    }

    public static function unauthorized(Response $response, string $message = 'Não autorizado'): Response
    {
        return self::error($response, $message, 401);
    }

    public static function forbidden(Response $response, string $message = 'Acesso negado'): Response
    {
        return self::error($response, $message, 403);
    }

    public static function conflict(Response $response, string $message = 'Conflito de dados'): Response
    {
        return self::error($response, $message, 409);
    }

    private static function getErrorCodeFromStatusCode(int $statusCode): string
    {
        return match ($statusCode) {
            400 => 'BAD_REQUEST',
            401 => 'UNAUTHORIZED',
            403 => 'FORBIDDEN',
            404 => 'NOT_FOUND',
            405 => 'METHOD_NOT_ALLOWED',
            409 => 'CONFLICT',
            422 => 'VALIDATION_ERROR',
            500 => 'INTERNAL_ERROR',
            default => 'UNKNOWN_ERROR'
        };
    }
}
