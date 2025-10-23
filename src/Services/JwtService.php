<?php

declare(strict_types=1);

namespace App\Services;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;

class JwtService
{
    private string $secret;
    private int $expiration;

    public function __construct()
    {
        $this->secret = $_ENV['JWT_SECRET'] ?? 'default-secret';
        $this->expiration = (int)($_ENV['JWT_EXPIRATION'] ?? 3600);
    }

    /**
     * Gera um token JWT para o usuário
     */
    public function generateToken(array $payload): string
    {
        $payload = array_merge($payload, [
            'iat' => time(),
            'exp' => time() + $this->expiration
        ]);

        return JWT::encode($payload, $this->secret, 'HS256');
    }

    /**
     * Valida e decodifica um token JWT
     */
    public function validateToken(string $token): object
    {
        try {
            return JWT::decode($token, new Key($this->secret, 'HS256'));
        } catch (ExpiredException $e) {
            throw new \App\Exceptions\UnauthorizedException('Token expirado');
        } catch (SignatureInvalidException $e) {
            throw new \App\Exceptions\UnauthorizedException('Token inválido');
        } catch (\Exception $e) {
            throw new \App\Exceptions\UnauthorizedException('Erro na validação do token');
        }
    }

    /**
     * Extrai o token do header Authorization
     */
    public function extractTokenFromHeader(string $authHeader): string
    {
        if (empty($authHeader)) {
            throw new \App\Exceptions\UnauthorizedException('Token de acesso não fornecido');
        }

        if (!str_starts_with($authHeader, 'Bearer ')) {
            throw new \App\Exceptions\UnauthorizedException('Formato de token inválido');
        }

        return str_replace('Bearer ', '', $authHeader);
    }

    /**
     * Gera um token de refresh
     */
    public function generateRefreshToken(array $payload): string
    {
        $payload = array_merge($payload, [
            'type' => 'refresh',
            'iat' => time(),
            'exp' => time() + ($this->expiration * 24) // 24x mais tempo que o token normal
        ]);

        return JWT::encode($payload, $this->secret, 'HS256');
    }
}
