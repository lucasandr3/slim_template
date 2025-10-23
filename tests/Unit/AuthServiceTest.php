<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Services\JwtService;
use App\Services\TokenCacheService;
use App\Services\SecurityLogService;
use App\Services\VerificationService;
use App\Entities\User;
use App\Entities\VerificationToken;

class AuthServiceTest extends TestCase
{
    private JwtService $jwtService;

    protected function setUp(): void
    {
        $this->jwtService = new JwtService();
    }

    public function testJwtTokenGeneration(): void
    {
        $payload = [
            'user_id' => 1,
            'email' => 'test@example.com',
            'name' => 'Test User'
        ];

        $token = $this->jwtService->generateToken($payload);
        
        $this->assertIsString($token);
        $this->assertNotEmpty($token);
    }

    public function testJwtTokenValidation(): void
    {
        $payload = [
            'user_id' => 1,
            'email' => 'test@example.com',
            'name' => 'Test User'
        ];

        $token = $this->jwtService->generateToken($payload);
        $decoded = $this->jwtService->validateToken($token);
        
        $this->assertEquals(1, $decoded->user_id);
        $this->assertEquals('test@example.com', $decoded->email);
        $this->assertEquals('Test User', $decoded->name);
    }

    public function testJwtTokenExtraction(): void
    {
        $token = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.test';
        
        $extracted = $this->jwtService->extractTokenFromHeader('Bearer ' . $token);
        
        $this->assertEquals($token, $extracted);
    }

    public function testJwtTokenExtractionWithoutBearer(): void
    {
        $this->expectException(\App\Exceptions\UnauthorizedException::class);
        $this->jwtService->extractTokenFromHeader('Invalid ' . 'token');
    }

    public function testJwtTokenExtractionEmpty(): void
    {
        $this->expectException(\App\Exceptions\UnauthorizedException::class);
        $this->jwtService->extractTokenFromHeader('');
    }

    public function testRefreshTokenGeneration(): void
    {
        $payload = [
            'user_id' => 1,
            'email' => 'test@example.com',
            'name' => 'Test User'
        ];

        $refreshToken = $this->jwtService->generateRefreshToken($payload);
        
        $this->assertIsString($refreshToken);
        $this->assertNotEmpty($refreshToken);
        
        $decoded = $this->jwtService->validateToken($refreshToken);
        $this->assertEquals('refresh', $decoded->type);
    }

    public function testTokenCacheService(): void
    {
        $cacheService = new TokenCacheService();
        
        // Test blacklist
        $token = 'test-token-123';
        $cacheService->blacklistToken($token);
        $this->assertTrue($cacheService->isTokenBlacklisted($token));
        
        // Test user data cache
        $userId = 1;
        $userData = ['id' => 1, 'name' => 'Test User'];
        $cacheService->cacheUserData($userId, $userData);
        
        $cachedData = $cacheService->getCachedUserData($userId);
        $this->assertEquals($userData, $cachedData);
        
        // Test rate limit cache
        $ipAddress = '192.168.1.1';
        $cacheService->cacheRateLimit($ipAddress, 5, 60);
        $this->assertEquals(5, $cacheService->getRateLimitCount($ipAddress));
        
        // Test failed login attempts
        $email = 'test@example.com';
        $cacheService->cacheFailedLoginAttempts($email, 3, 60);
        $this->assertEquals(3, $cacheService->getFailedLoginAttempts($email));
    }

    public function testUserEntity(): void
    {
        $user = new User();
        $user->setName('Test User')
             ->setEmail('test@example.com')
             ->setPassword('hashed_password')
             ->setRole('admin')
             ->setPermissions(['read', 'write', 'delete']);

        $this->assertEquals('Test User', $user->getName());
        $this->assertEquals('test@example.com', $user->getEmail());
        $this->assertEquals('admin', $user->getRole());
        $this->assertTrue($user->isAdmin());
        $this->assertTrue($user->isModerator());
        $this->assertTrue($user->hasPermission('read'));
        $this->assertTrue($user->hasPermission('write'));
        $this->assertTrue($user->hasPermission('delete'));
        $this->assertFalse($user->hasPermission('super_admin'));
    }

    public function testVerificationTokenEntity(): void
    {
        $token = new VerificationToken();
        $token->setToken('test-token-123')
              ->setType('email_verification')
              ->setEmail('test@example.com')
              ->setExpiresAt(new \DateTime('+1 hour'));

        $this->assertEquals('test-token-123', $token->getToken());
        $this->assertEquals('email_verification', $token->getType());
        $this->assertEquals('test@example.com', $token->getEmail());
        $this->assertFalse($token->isExpired());
        $this->assertFalse($token->isUsed());
        $this->assertTrue($token->isValid());

        // Test expiration
        $token->setExpiresAt(new \DateTime('-1 hour'));
        $this->assertTrue($token->isExpired());
        $this->assertFalse($token->isValid());

        // Test usage
        $token->markAsUsed();
        $this->assertTrue($token->isUsed());
        $this->assertFalse($token->isValid());
    }
}
