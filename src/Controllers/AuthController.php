<?php

declare(strict_types=1);

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Exceptions\ValidationException;
use App\Exceptions\UnauthorizedException;
use App\Exceptions\ConflictException;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use App\Services\AuthService;
use App\Services\ApiResponseService;
use App\Http\Resources\UserResource;

class AuthController
{
    private AuthService $authService;

    public function __construct()
    {
        $this->authService = new AuthService();
    }

    public function login(Request $request, Response $response): Response
    {
        $formRequest = new LoginRequest($request);
        $data = $formRequest->validated();

        $ipAddress = $this->getClientIp($request);
        $userAgent = $request->getHeaderLine('User-Agent');

        $tokens = $this->authService->authenticate($data['email'], $data['password'], $ipAddress, $userAgent);
        $user = $this->authService->getUserFromToken($tokens['access_token']);

        return ApiResponseService::success($response, [
            'access_token' => $tokens['access_token'],
            'refresh_token' => $tokens['refresh_token'],
            'token_type' => $tokens['token_type'],
            'expires_in' => $tokens['expires_in'],
            'user' => UserResource::make($user)->auth()
        ], 'Login realizado com sucesso');
    }

    public function register(Request $request, Response $response): Response
    {
        $formRequest = new RegisterRequest($request);
        $data = $formRequest->validated();

        $user = $this->authService->register($data);

        return ApiResponseService::resource($response, UserResource::make($user), 'Usuário criado com sucesso', 201);
    }

    public function profile(Request $request, Response $response): Response
    {
        $userData = $request->getAttribute('user');
        $user = $this->authService->getUserFromToken($request->getHeaderLine('Authorization'));

        return ApiResponseService::resource($response, UserResource::make($user)->profile(), 'Perfil do usuário');
    }

    public function refresh(Request $request, Response $response): Response
    {
        $authHeader = $request->getHeaderLine('Authorization');
        $refreshToken = str_replace('Bearer ', '', $authHeader);
        
        $tokens = $this->authService->refreshToken($refreshToken);

        return ApiResponseService::success($response, $tokens, 'Token renovado com sucesso');
    }

    public function logout(Request $request, Response $response): Response
    {
        return ApiResponseService::success($response, null, 'Logout realizado com sucesso');
    }

    /**
     * Obtém IP do cliente
     */
    private function getClientIp(Request $request): string
    {
        $headers = [
            'HTTP_CF_CONNECTING_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        ];

        foreach ($headers as $header) {
            if (!empty($_SERVER[$header])) {
                $ips = explode(',', $_SERVER[$header]);
                return trim($ips[0]);
            }
        }

        return '127.0.0.1';
    }
}
