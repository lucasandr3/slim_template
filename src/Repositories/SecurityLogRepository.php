<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Entities\SecurityLog;
use Doctrine\ORM\EntityManager;

class SecurityLogRepository extends BaseRepository
{
    public function __construct(EntityManager $entityManager)
    {
        parent::__construct($entityManager);
    }

    protected function getEntityClass(): string
    {
        return SecurityLog::class;
    }

    public function findByAction(string $action): array
    {
        return $this->findBy(['action' => $action], ['createdAt' => 'DESC']);
    }

    public function findByUser(int $userId): array
    {
        return $this->findBy(['userId' => $userId], ['createdAt' => 'DESC']);
    }

    public function findByEmail(string $email): array
    {
        return $this->findBy(['email' => $email], ['createdAt' => 'DESC']);
    }

    public function findByIpAddress(string $ipAddress): array
    {
        return $this->findBy(['ipAddress' => $ipAddress], ['createdAt' => 'DESC']);
    }

    public function findFailedLogins(string $email, int $minutes = 60): array
    {
        $qb = $this->createQueryBuilder('sl');
        $qb->where('sl.email = :email')
           ->andWhere('sl.action = :action')
           ->andWhere('sl.success = :success')
           ->andWhere('sl.createdAt > :since')
           ->setParameter('email', $email)
           ->setParameter('action', 'login')
           ->setParameter('success', false)
           ->setParameter('since', new \DateTime("-{$minutes} minutes"))
           ->orderBy('sl.createdAt', 'DESC');

        return $qb->getQuery()->getResult();
    }

    public function countFailedLogins(string $email, int $minutes = 60): int
    {
        $qb = $this->createQueryBuilder('sl');
        $qb->select('COUNT(sl.id)')
           ->where('sl.email = :email')
           ->andWhere('sl.action = :action')
           ->andWhere('sl.success = :success')
           ->andWhere('sl.createdAt > :since')
           ->setParameter('email', $email)
           ->setParameter('action', 'login')
           ->setParameter('success', false)
           ->setParameter('since', new \DateTime("-{$minutes} minutes"));

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    public function deleteOldLogs(int $days = 90): int
    {
        $qb = $this->createQueryBuilder('sl');
        $qb->delete()
           ->where('sl.createdAt < :cutoff')
           ->setParameter('cutoff', new \DateTime("-{$days} days"));

        return $qb->getQuery()->execute();
    }
}
