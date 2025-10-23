<?php

declare(strict_types=1);

namespace App\Services;

use App\Entities\SecurityLog;
use App\Repositories\SecurityLogRepository;
use App\Config\Database;

class SecurityLogService
{
    private SecurityLogRepository $logRepository;

    public function __construct()
    {
        $entityManager = Database::getEntityManager();
        $this->logRepository = new SecurityLogRepository($entityManager);
    }

    /**
     * Registra tentativa de login
     */
    public function logLogin(string $email, ?int $userId, string $ipAddress, string $userAgent, bool $success, array $metadata = []): void
    {
        $log = new SecurityLog();
        $log->setAction('login')
            ->setEmail($email)
            ->setUserId($userId)
            ->setIpAddress($ipAddress)
            ->setUserAgent($userAgent)
            ->setSuccess($success)
            ->setMetadata($metadata);

        $this->logRepository->save($log);
    }

    /**
     * Registra logout
     */
    public function logLogout(?int $userId, string $ipAddress, string $userAgent): void
    {
        $log = new SecurityLog();
        $log->setAction('logout')
            ->setUserId($userId)
            ->setIpAddress($ipAddress)
            ->setUserAgent($userAgent)
            ->setSuccess(true);

        $this->logRepository->save($log);
    }

    /**
     * Registra registro de usuário
     */
    public function logRegistration(string $email, int $userId, string $ipAddress, string $userAgent): void
    {
        $log = new SecurityLog();
        $log->setAction('register')
            ->setEmail($email)
            ->setUserId($userId)
            ->setIpAddress($ipAddress)
            ->setUserAgent($userAgent)
            ->setSuccess(true);

        $this->logRepository->save($log);
    }

    /**
     * Registra tentativa de reset de senha
     */
    public function logPasswordReset(string $email, string $ipAddress, string $userAgent, bool $success): void
    {
        $log = new SecurityLog();
        $log->setAction('password_reset')
            ->setEmail($email)
            ->setIpAddress($ipAddress)
            ->setUserAgent($userAgent)
            ->setSuccess($success);

        $this->logRepository->save($log);
    }

    /**
     * Registra verificação de email
     */
    public function logEmailVerification(string $email, int $userId, string $ipAddress, bool $success): void
    {
        $log = new SecurityLog();
        $log->setAction('email_verification')
            ->setEmail($email)
            ->setUserId($userId)
            ->setIpAddress($ipAddress)
            ->setSuccess($success);

        $this->logRepository->save($log);
    }

    /**
     * Registra login falhado
     */
    public function logFailedLogin(string $email, string $ipAddress, string $userAgent, string $reason = ''): void
    {
        $metadata = [];
        if ($reason) {
            $metadata['reason'] = $reason;
        }

        $this->logLogin($email, null, $ipAddress, $userAgent, false, $metadata);
    }

    /**
     * Verifica se há muitas tentativas de login falhadas
     */
    public function hasTooManyFailedLogins(string $email, int $maxAttempts = 5, int $minutes = 60): bool
    {
        $failedCount = $this->logRepository->countFailedLogins($email, $minutes);
        return $failedCount >= $maxAttempts;
    }

    /**
     * Obtém logs de segurança por usuário
     */
    public function getUserLogs(int $userId, int $limit = 50): array
    {
        return $this->logRepository->findByUser($userId);
    }

    /**
     * Obtém logs de segurança por IP
     */
    public function getIpLogs(string $ipAddress, int $limit = 50): array
    {
        return $this->logRepository->findByIpAddress($ipAddress);
    }

    /**
     * Limpa logs antigos
     */
    public function cleanupOldLogs(int $days = 90): int
    {
        return $this->logRepository->deleteOldLogs($days);
    }

    /**
     * Obtém estatísticas de segurança
     */
    public function getSecurityStats(int $days = 30): array
    {
        $qb = $this->logRepository->createQueryBuilder('sl');
        $qb->select('sl.action, COUNT(sl.id) as count, sl.success')
           ->where('sl.createdAt > :since')
           ->setParameter('since', new \DateTime("-{$days} days"))
           ->groupBy('sl.action, sl.success')
           ->orderBy('count', 'DESC');

        $results = $qb->getQuery()->getResult();

        $stats = [];
        foreach ($results as $result) {
            $action = $result['action'];
            $success = $result['success'] ? 'success' : 'failed';
            $stats[$action][$success] = (int) $result['count'];
        }

        return $stats;
    }
}
