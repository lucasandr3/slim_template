<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Exceptions\ApiException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Exception\HttpNotFoundException;
use Slim\Exception\HttpMethodNotAllowedException;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpUnauthorizedException;
use Slim\Exception\HttpForbiddenException;
use Slim\Exception\HttpNotFoundException as SlimNotFoundException;
use Slim\Exception\HttpInternalServerErrorException;
use Throwable;

class ExceptionHandlerMiddleware
{
    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        try {
            return $handler->handle($request);
        } catch (ApiException $e) {
            return $this->handleApiException($e);
        } catch (HttpNotFoundException $e) {
            return $this->handleSlimException($e, 404, 'NOT_FOUND', 'Rota não encontrada');
        } catch (HttpMethodNotAllowedException $e) {
            return $this->handleSlimException($e, 405, 'METHOD_NOT_ALLOWED', 'Método não permitido');
        } catch (HttpBadRequestException $e) {
            return $this->handleSlimException($e, 400, 'BAD_REQUEST', 'Requisição inválida');
        } catch (HttpUnauthorizedException $e) {
            return $this->handleSlimException($e, 401, 'UNAUTHORIZED', 'Não autorizado');
        } catch (HttpForbiddenException $e) {
            return $this->handleSlimException($e, 403, 'FORBIDDEN', 'Acesso negado');
        } catch (HttpInternalServerErrorException $e) {
            return $this->handleSlimException($e, 500, 'INTERNAL_ERROR', 'Erro interno do servidor');
        } catch (Throwable $e) {
            return $this->handleGenericException($e);
        }
    }

    private function handleApiException(ApiException $e): Response
    {
        $response = new \Slim\Psr7\Response();
        $response->getBody()->write(json_encode($e->toArray()));
        
        return $response
            ->withStatus($e->getHttpStatusCode())
            ->withHeader('Content-Type', 'application/json');
    }

    private function handleSlimException(Throwable $e, int $statusCode, string $errorCode, string $message): Response
    {
        $response = new \Slim\Psr7\Response();
        
        $errorData = [
            'error' => [
                'code' => $errorCode,
                'message' => $message,
                'timestamp' => date('Y-m-d H:i:s'),
                'path' => $_SERVER['REQUEST_URI'] ?? '/'
            ]
        ];

        if ($_ENV['APP_DEBUG'] === 'true') {
            $errorData['error']['debug'] = [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ];
        }

        $response->getBody()->write(json_encode($errorData));
        
        return $response
            ->withStatus($statusCode)
            ->withHeader('Content-Type', 'application/json');
    }

    private function handleGenericException(Throwable $e): Response
    {
        $response = new \Slim\Psr7\Response();
        
        $errorData = [
            'error' => [
                'code' => 'INTERNAL_ERROR',
                'message' => 'Erro interno do servidor',
                'timestamp' => date('Y-m-d H:i:s'),
                'path' => $_SERVER['REQUEST_URI'] ?? '/'
            ]
        ];

        if ($_ENV['APP_DEBUG'] === 'true') {
            $errorData['error']['debug'] = [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ];
        }

        $response->getBody()->write(json_encode($errorData));
        
        return $response
            ->withStatus(500)
            ->withHeader('Content-Type', 'application/json');
    }
}
