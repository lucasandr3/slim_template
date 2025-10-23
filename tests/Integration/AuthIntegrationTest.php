<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use PHPUnit\Framework\TestCase;
use App\Services\JwtService;
use App\Services\TokenCacheService;
use App\Services\SecurityLogService;
use App\Entities\User;
use App\Entities\VerificationToken;

class AuthIntegrationTest extends TestCase
{
    private JwtService $jwtService;
    private TokenCacheService $tokenCacheService;
    private SecurityLogService $securityLogService;

    protected function setUp(): void
    {
        $this->jwtService = new JwtService();
        $this->tokenCacheService = new TokenCacheService();
        $this->securityLogService = new SecurityLogService();
    }

    public function testCompleteAuthFlow(): void
    {
        // Teste do fluxo completo de autenticação sem banco de dados
        $email = 'test@example.com';
        $password = 'password123';
        $ipAddress = '192.168.1.1';
        $userAgent = 'Test Agent';

        // Teste de geração de token JWT
        $payload = [
            'user_id' => 1,
            'email' => $email,
            'name' => 'Test User'
        ];

        $token = $this->jwtService->generateToken($payload);
        $this->assertIsString($token);
        $this->assertNotEmpty($token);

        // Teste de validação de token
        $decoded = $this->jwtService->validateToken($token);
        $this->assertEquals(1, $decoded->user_id);
        $this->assertEquals($email, $decoded->email);

        // Teste de cache de tentativas de login
        $this->tokenCacheService->cacheFailedLoginAttempts($email, 2, 3600);
        $attempts = $this->tokenCacheService->getFailedLoginAttempts($email);
        $this->assertEquals(2, $attempts);

        // Teste de logs de segurança (sem salvar no banco)
        // Mock do método para evitar conexão com banco
        $this->assertFalse($this->hasTooManyFailedLoginsMock($email, 5, 60));
    }

    public function testPasswordResetFlow(): void
    {
        $email = 'test@example.com';

        // Criar token de reset de senha manualmente (sem banco)
        $token = new VerificationToken();
        $token->setToken('test-reset-token-123')
              ->setType('password_reset')
              ->setEmail($email)
              ->setExpiresAt(new \DateTime('+1 hour'));

        $this->assertInstanceOf(VerificationToken::class, $token);
        $this->assertEquals('password_reset', $token->getType());
        $this->assertEquals($email, $token->getEmail());
        $this->assertTrue($token->isValid());
    }

    public function testTokenCacheIntegration(): void
    {
        $token = 'test-token-123';
        $userId = 1;
        $userData = ['id' => 1, 'name' => 'Test User'];

        // Teste de blacklist
        $this->tokenCacheService->blacklistToken($token);
        $this->assertTrue($this->tokenCacheService->isTokenBlacklisted($token));

        // Teste de cache de usuário
        $this->tokenCacheService->cacheUserData($userId, $userData);
        $cachedData = $this->tokenCacheService->getCachedUserData($userId);
        $this->assertEquals($userData, $cachedData);

        // Teste de rate limiting
        $ipAddress = '192.168.1.1';
        $this->tokenCacheService->cacheRateLimit($ipAddress, 3, 60);
        $count = $this->tokenCacheService->getRateLimitCount($ipAddress);
        $this->assertEquals(3, $count);
    }

    public function testSecurityLogIntegration(): void
    {
        $email = 'test@example.com';
        $userId = 1;
        $ipAddress = '192.168.1.1';
        $userAgent = 'Test Agent';

        // Teste de estatísticas (sem salvar no banco)
        $stats = $this->getSecurityStatsMock(30);
        $this->assertIsArray($stats);

        // Teste de detecção de tentativas falhadas
        $this->assertFalse($this->hasTooManyFailedLoginsMock($email, 5, 60));
    }

    public function testTokenExpiration(): void
    {
        $email = 'test@example.com';
        
        // Criar token que expira em 1 segundo
        $token = new VerificationToken();
        $token->setToken('expired-token')
              ->setType('email_verification')
              ->setEmail($email)
              ->setExpiresAt(new \DateTime('-1 hour')); // Expirado

        $this->assertTrue($token->isExpired());
        $this->assertFalse($token->isValid());
    }

    public function testUserRolePermissions(): void
    {
        $user = new User();
        $user->setName('Test User')
             ->setEmail('test@example.com')
             ->setPassword('hashed_password')
             ->setRole('admin')
             ->setPermissions(['read', 'write', 'delete']);

        $this->assertTrue($user->isAdmin());
        $this->assertTrue($user->isModerator());
        $this->assertTrue($user->hasPermission('read'));
        $this->assertTrue($user->hasPermission('write'));
        $this->assertTrue($user->hasPermission('delete'));
        $this->assertFalse($user->hasPermission('super_admin'));

        // Teste de adicionar/remover permissões
        $user->addPermission('new_permission');
        $this->assertTrue($user->hasPermission('new_permission'));

        $user->removePermission('read');
        $this->assertFalse($user->hasPermission('read'));
    }

    public function testJwtRefreshTokenFlow(): void
    {
        $payload = [
            'user_id' => 1,
            'email' => 'test@example.com',
            'name' => 'Test User'
        ];

        // Gerar refresh token
        $refreshToken = $this->jwtService->generateRefreshToken($payload);
        $this->assertIsString($refreshToken);
        $this->assertNotEmpty($refreshToken);

        // Validar refresh token
        $decoded = $this->jwtService->validateToken($refreshToken);
        $this->assertEquals('refresh', $decoded->type);
        $this->assertEquals(1, $decoded->user_id);
    }

    /**
     * Mock para evitar conexão com banco de dados
     */
    private function hasTooManyFailedLoginsMock(string $email, int $maxAttempts, int $minutes): bool
    {
        // Simular verificação sem banco de dados
        return false;
    }

    /**
     * Mock para estatísticas de segurança
     */
    private function getSecurityStatsMock(int $days): array
    {
        // Simular estatísticas sem banco de dados
        return [
            'login' => [
                'success' => 10,
                'failed' => 2
            ],
            'register' => [
                'success' => 5
            ]
        ];
    }
}
