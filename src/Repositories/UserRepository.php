<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Entities\User;
use Doctrine\ORM\EntityManager;

class UserRepository extends BaseRepository
{
    public function __construct(EntityManager $entityManager)
    {
        parent::__construct($entityManager);
    }

    protected function getEntityClass(): string
    {
        return User::class;
    }

    public function findByEmail(string $email): ?User
    {
        return $this->findOneBy(['email' => $email]);
    }

    public function emailExists(string $email): bool
    {
        return $this->findByEmail($email) !== null;
    }
}
