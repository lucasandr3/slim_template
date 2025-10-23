<?php

declare(strict_types=1);

namespace App\Services;

use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\CacheItem;

class TokenCacheService
{
    private FilesystemAdapter $cache;
    private int $defaultTtl;

    public function __construct()
    {
        $this->cache = new FilesystemAdapter('tokens', 0, sys_get_temp_dir() . '/cache');
        $this->defaultTtl = (int)($_ENV['CACHE_TTL'] ?? 3600);
    }

    /**
     * Adiciona token à blacklist
     */
    public function blacklistToken(string $token, int $ttl = null): void
    {
        $ttl = $ttl ?? $this->defaultTtl;
        $cacheItem = $this->cache->getItem('blacklist_' . hash('sha256', $token));
        $cacheItem->set(true);
        $cacheItem->expiresAfter($ttl);
        $this->cache->save($cacheItem);
    }

    /**
     * Verifica se token está na blacklist
     */
    public function isTokenBlacklisted(string $token): bool
    {
        $cacheItem = $this->cache->getItem('blacklist_' . hash('sha256', $token));
        return $cacheItem->isHit();
    }

    /**
     * Cacheia dados do usuário
     */
    public function cacheUserData(int $userId, array $userData, int $ttl = null): void
    {
        $ttl = $ttl ?? $this->defaultTtl;
        $cacheItem = $this->cache->getItem('user_' . $userId);
        $cacheItem->set($userData);
        $cacheItem->expiresAfter($ttl);
        $this->cache->save($cacheItem);
    }

    /**
     * Obtém dados do usuário do cache
     */
    public function getCachedUserData(int $userId): ?array
    {
        $cacheItem = $this->cache->getItem('user_' . $userId);
        return $cacheItem->isHit() ? $cacheItem->get() : null;
    }

    /**
     * Remove dados do usuário do cache
     */
    public function removeCachedUserData(int $userId): void
    {
        $this->cache->deleteItem('user_' . $userId);
    }

    /**
     * Cacheia rate limit por IP
     */
    public function cacheRateLimit(string $ipAddress, int $attempts, int $windowSeconds): void
    {
        $cacheItem = $this->cache->getItem('rate_limit_' . hash('sha256', $ipAddress));
        $cacheItem->set($attempts);
        $cacheItem->expiresAfter($windowSeconds);
        $this->cache->save($cacheItem);
    }

    /**
     * Obtém contagem de rate limit por IP
     */
    public function getRateLimitCount(string $ipAddress): int
    {
        $cacheItem = $this->cache->getItem('rate_limit_' . hash('sha256', $ipAddress));
        return $cacheItem->isHit() ? $cacheItem->get() : 0;
    }

    /**
     * Cacheia tentativas de login falhadas
     */
    public function cacheFailedLoginAttempts(string $email, int $attempts, int $windowSeconds): void
    {
        $cacheItem = $this->cache->getItem('failed_login_' . hash('sha256', $email));
        $cacheItem->set($attempts);
        $cacheItem->expiresAfter($windowSeconds);
        $this->cache->save($cacheItem);
    }

    /**
     * Obtém tentativas de login falhadas
     */
    public function getFailedLoginAttempts(string $email): int
    {
        $cacheItem = $this->cache->getItem('failed_login_' . hash('sha256', $email));
        return $cacheItem->isHit() ? $cacheItem->get() : 0;
    }

    /**
     * Limpa cache expirado
     */
    public function cleanup(): void
    {
        $this->cache->prune();
    }

    /**
     * Limpa todo o cache
     */
    public function clearAll(): void
    {
        $this->cache->clear();
    }

    /**
     * Obtém estatísticas do cache
     */
    public function getStats(): array
    {
        return [
            'cache_directory' => sys_get_temp_dir() . '/cache',
            'default_ttl' => $this->defaultTtl,
        ];
    }
}
