<?php

declare(strict_types=1);

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Services\SecurityLogService;
use App\Services\TokenCacheService;
use App\Services\VerificationService;
use App\Services\ApiResponseService;
use App\Repositories\UserRepository;
use App\Config\Database;

class AdminController
{
    private SecurityLogService $securityLogService;
    private TokenCacheService $tokenCacheService;
    private VerificationService $verificationService;
    private UserRepository $userRepository;

    public function __construct()
    {
        $this->securityLogService = new SecurityLogService();
        $this->tokenCacheService = new TokenCacheService();
        $this->verificationService = new VerificationService();
        
        $entityManager = Database::getEntityManager();
        $this->userRepository = new UserRepository($entityManager);
    }

    /**
     * Dashboard de administração
     */
    public function dashboard(Request $request, Response $response): Response
    {
        $stats = [
            'security' => $this->securityLogService->getSecurityStats(30),
            'cache' => $this->tokenCacheService->getStats(),
            'users' => $this->getUserStats(),
            'system' => $this->getSystemStats(),
        ];

        return ApiResponseService::success($response, $stats, 'Dashboard de administração');
    }

    /**
     * Logs de segurança
     */
    public function securityLogs(Request $request, Response $response): Response
    {
        $queryParams = $request->getQueryParams();
        $limit = (int)($queryParams['limit'] ?? 50);
        $offset = (int)($queryParams['offset'] ?? 0);
        
        $logs = $this->securityLogService->getUserLogs(0, $limit); // 0 = todos os usuários
        
        return ApiResponseService::success($response, $logs, 'Logs de segurança');
    }

    /**
     * Estatísticas de usuários
     */
    public function userStats(Request $request, Response $response): Response
    {
        $stats = $this->getUserStats();
        
        return ApiResponseService::success($response, $stats, 'Estatísticas de usuários');
    }

    /**
     * Limpeza de cache
     */
    public function clearCache(Request $request, Response $response): Response
    {
        $this->tokenCacheService->clearAll();
        
        return ApiResponseService::success($response, null, 'Cache limpo com sucesso');
    }

    /**
     * Limpeza de logs antigos
     */
    public function cleanupLogs(Request $request, Response $response): Response
    {
        $queryParams = $request->getQueryParams();
        $days = (int)($queryParams['days'] ?? 90);
        
        $deleted = $this->securityLogService->cleanupOldLogs($days);
        
        return ApiResponseService::success($response, ['deleted' => $deleted], 'Logs antigos removidos');
    }

    /**
     * Limpeza de tokens expirados
     */
    public function cleanupTokens(Request $request, Response $response): Response
    {
        $deleted = $this->verificationService->cleanupExpiredTokens();
        
        return ApiResponseService::success($response, ['deleted' => $deleted], 'Tokens expirados removidos');
    }

    /**
     * Health check detalhado
     */
    public function healthCheck(Request $request, Response $response): Response
    {
        $health = [
            'status' => 'ok',
            'timestamp' => date('Y-m-d H:i:s'),
            'version' => $_ENV['APP_VERSION'] ?? '1.0.0',
            'environment' => $_ENV['APP_ENV'] ?? 'production',
            'database' => $this->checkDatabaseConnection(),
            'cache' => $this->checkCacheConnection(),
            'memory_usage' => memory_get_usage(true),
            'memory_peak' => memory_get_peak_usage(true),
            'uptime' => $this->getUptime(),
        ];

        $status = $health['database'] && $health['cache'] ? 200 : 503;
        
        return ApiResponseService::success($response, $health, 'Health check detalhado', $status);
    }

    /**
     * Obtém estatísticas de usuários
     */
    private function getUserStats(): array
    {
        $qb = $this->userRepository->createQueryBuilder('u');
        $qb->select('COUNT(u.id) as total');
        $total = (int) $qb->getQuery()->getSingleScalarResult();

        $qb = $this->userRepository->createQueryBuilder('u');
        $qb->select('COUNT(u.id) as active')
           ->where('u.isActive = :active')
           ->setParameter('active', true);
        $active = (int) $qb->getQuery()->getSingleScalarResult();

        $qb = $this->userRepository->createQueryBuilder('u');
        $qb->select('COUNT(u.id) as verified')
           ->where('u.emailVerifiedAt IS NOT NULL');
        $verified = (int) $qb->getQuery()->getSingleScalarResult();

        return [
            'total' => $total,
            'active' => $active,
            'inactive' => $total - $active,
            'verified' => $verified,
            'unverified' => $total - $verified,
        ];
    }

    /**
     * Obtém estatísticas do sistema
     */
    private function getSystemStats(): array
    {
        return [
            'php_version' => PHP_VERSION,
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'post_max_size' => ini_get('post_max_size'),
            'timezone' => date_default_timezone_get(),
        ];
    }

    /**
     * Verifica conexão com banco de dados
     */
    private function checkDatabaseConnection(): bool
    {
        try {
            $entityManager = Database::getEntityManager();
            $connection = $entityManager->getConnection();
            $connection->executeQuery('SELECT 1');
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Verifica conexão com cache
     */
    private function checkCacheConnection(): bool
    {
        try {
            $this->tokenCacheService->getStats();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Obtém tempo de atividade
     */
    private function getUptime(): string
    {
        if (function_exists('sys_getloadavg')) {
            $uptime = shell_exec('uptime');
            return trim($uptime);
        }
        
        return 'N/A';
    }
}
