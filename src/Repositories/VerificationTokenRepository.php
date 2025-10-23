<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Entities\VerificationToken;
use Doctrine\ORM\EntityManager;

class VerificationTokenRepository extends BaseRepository
{
    public function __construct(EntityManager $entityManager)
    {
        parent::__construct($entityManager);
    }

    protected function getEntityClass(): string
    {
        return VerificationToken::class;
    }

    public function findByToken(string $token): ?VerificationToken
    {
        return $this->findOneBy(['token' => $token]);
    }

    public function findValidToken(string $token, string $type): ?VerificationToken
    {
        $tokenEntity = $this->findByToken($token);
        
        if (!$tokenEntity || $tokenEntity->getType() !== $type || !$tokenEntity->isValid()) {
            return null;
        }

        return $tokenEntity;
    }

    public function findByEmailAndType(string $email, string $type): array
    {
        return $this->findBy(['email' => $email, 'type' => $type]);
    }

    public function deleteExpiredTokens(): int
    {
        $qb = $this->createQueryBuilder('vt');
        $qb->delete()
           ->where('vt.expiresAt < :now')
           ->setParameter('now', new \DateTime());

        return $qb->getQuery()->execute();
    }

    public function invalidateUserTokens(string $email, string $type): int
    {
        $qb = $this->createQueryBuilder('vt');
        $qb->update()
           ->set('vt.usedAt', ':now')
           ->where('vt.email = :email')
           ->andWhere('vt.type = :type')
           ->andWhere('vt.usedAt IS NULL')
           ->setParameter('now', new \DateTime())
           ->setParameter('email', $email)
           ->setParameter('type', $type);

        return $qb->getQuery()->execute();
    }
}
