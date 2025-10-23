<?php

declare(strict_types=1);

namespace App\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use App\Services\JwtService;
use App\Services\ApiResponseService;

class AuthMiddleware
{
    private JwtService $jwtService;

    public function __construct()
    {
        $this->jwtService = new JwtService();
    }

    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        $authHeader = $request->getHeaderLine('Authorization');
        
        try {
            $token = $this->jwtService->extractTokenFromHeader($authHeader);
            $decoded = $this->jwtService->validateToken($token);
            
            // Adicionar dados do usuário ao request
            $request = $request->withAttribute('user', $decoded);
            
            return $handler->handle($request);
            
        } catch (\App\Exceptions\UnauthorizedException $e) {
            $response = new \Slim\Psr7\Response();
            return ApiResponseService::error($response, $e->getMessage(), 401);
        } catch (\Exception $e) {
            $response = new \Slim\Psr7\Response();
            return ApiResponseService::error($response, 'Erro na autenticação', 401);
        }
    }
}
