<?php

declare(strict_types=1);

namespace App\Services;

use App\Entities\User;
use App\Repositories\UserRepository;
use App\Config\Database;
use App\Services\SecurityLogService;
use App\Services\TokenCacheService;

class AuthService
{
    private UserRepository $userRepository;
    private JwtService $jwtService;
    private SecurityLogService $securityLogService;
    private TokenCacheService $tokenCacheService;

    public function __construct()
    {
        $entityManager = Database::getEntityManager();
        $this->userRepository = new UserRepository($entityManager);
        $this->jwtService = new JwtService();
        $this->securityLogService = new SecurityLogService();
        $this->tokenCacheService = new TokenCacheService();
    }

    /**
     * Autentica um usuário com email e senha
     */
    public function authenticate(string $email, string $password, string $ipAddress = '', string $userAgent = ''): array
    {
        // Verificar tentativas de login falhadas
        $failedAttempts = $this->tokenCacheService->getFailedLoginAttempts($email);
        if ($failedAttempts >= 5) {
            $this->securityLogService->logFailedLogin($email, $ipAddress, $userAgent, 'Muitas tentativas falhadas');
            throw new \App\Exceptions\UnauthorizedException('Muitas tentativas de login falhadas. Tente novamente em alguns minutos.');
        }

        $user = $this->userRepository->findByEmail($email);
        
        if (!$user || !password_verify($password, $user->getPassword())) {
            // Incrementar tentativas falhadas
            $this->tokenCacheService->cacheFailedLoginAttempts($email, $failedAttempts + 1, 3600);
            
            $this->securityLogService->logFailedLogin($email, $ipAddress, $userAgent, 'Credenciais inválidas');
            throw new \App\Exceptions\UnauthorizedException('Credenciais inválidas');
        }

        // Verificar se usuário está ativo
        if (!$user->isActive()) {
            $this->securityLogService->logFailedLogin($email, $ipAddress, $userAgent, 'Usuário inativo');
            throw new \App\Exceptions\UnauthorizedException('Conta desativada');
        }

        // Limpar tentativas falhadas em caso de sucesso
        $this->tokenCacheService->cacheFailedLoginAttempts($email, 0, 1);

        // Atualizar último login
        $user->setLastLoginAt(new \DateTime());
        $this->userRepository->save($user);

        // Log de login bem-sucedido
        $this->securityLogService->logLogin($email, $user->getId(), $ipAddress, $userAgent, true);

        return $this->generateTokens($user);
    }

    /**
     * Registra um novo usuário
     */
    public function register(array $data): User
    {
        // Verificar se email já existe
        if ($this->userRepository->emailExists($data['email'])) {
            throw new \App\Exceptions\ConflictException('Email já cadastrado', [
                'email' => 'Este email já está em uso'
            ]);
        }

        // Criar usuário
        $user = new User();
        $user->setName($data['name'])
             ->setEmail($data['email'])
             ->setPassword(password_hash($data['password'], PASSWORD_DEFAULT));

        $this->userRepository->save($user);

        return $user;
    }

    /**
     * Gera tokens de acesso e refresh
     */
    public function generateTokens(User $user): array
    {
        $payload = [
            'user_id' => $user->getId(),
            'email' => $user->getEmail(),
            'name' => $user->getName(),
        ];

        $accessToken = $this->jwtService->generateToken($payload);
        $refreshToken = $this->jwtService->generateRefreshToken($payload);

        return [
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
            'token_type' => 'Bearer',
            'expires_in' => $_ENV['JWT_EXPIRATION'] ?? 3600
        ];
    }

    /**
     * Renova o token usando refresh token
     */
    public function refreshToken(string $refreshToken): array
    {
        $decoded = $this->jwtService->validateToken($refreshToken);
        
        if (!isset($decoded->type) || $decoded->type !== 'refresh') {
            throw new \App\Exceptions\UnauthorizedException('Token de refresh inválido');
        }

        $user = $this->userRepository->find($decoded->user_id);
        if (!$user) {
            throw new \App\Exceptions\UnauthorizedException('Usuário não encontrado');
        }

        return $this->generateTokens($user);
    }

    /**
     * Obtém o usuário pelo token
     */
    public function getUserFromToken(string $token): User
    {
        $decoded = $this->jwtService->validateToken($token);
        
        $user = $this->userRepository->find($decoded->user_id);
        if (!$user) {
            throw new \App\Exceptions\UnauthorizedException('Usuário não encontrado');
        }

        return $user;
    }

    /**
     * Valida credenciais sem gerar token
     */
    public function validateCredentials(string $email, string $password): bool
    {
        $user = $this->userRepository->findByEmail($email);
        return $user && password_verify($password, $user->getPassword());
    }
}
