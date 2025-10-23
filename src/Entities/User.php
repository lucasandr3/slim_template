<?php

declare(strict_types=1);

namespace App\Entities;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\DBAL\Types\Types;

#[ORM\Entity]
#[ORM\Table(name: 'users')]
class User
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private string $name;

    #[ORM\Column(type: Types::STRING, length: 255, unique: true)]
    private string $email;

    #[ORM\Column(name: 'email_verified_at', type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTime $emailVerifiedAt = null;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private string $password;

    #[ORM\Column(name: 'remember_token', type: Types::STRING, length: 100, nullable: true)]
    private ?string $rememberToken = null;

    #[ORM\Column(type: Types::STRING, length: 50)]
    private string $role = 'user';

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $permissions = null;

    #[ORM\Column(name: 'is_active', type: Types::BOOLEAN)]
    private bool $isActive = true;

    #[ORM\Column(name: 'last_login_at', type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTime $lastLoginAt = null;

    #[ORM\Column(name: 'created_at', type: Types::DATETIME_MUTABLE)]
    private \DateTime $createdAt;

    #[ORM\Column(name: 'updated_at', type: Types::DATETIME_MUTABLE)]
    private \DateTime $updatedAt;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        $this->updatedAt = new \DateTime();
        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        $this->updatedAt = new \DateTime();
        return $this;
    }

    public function getEmailVerifiedAt(): ?\DateTime
    {
        return $this->emailVerifiedAt;
    }

    public function setEmailVerifiedAt(?\DateTime $emailVerifiedAt): self
    {
        $this->emailVerifiedAt = $emailVerifiedAt;
        $this->updatedAt = new \DateTime();
        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;
        $this->updatedAt = new \DateTime();
        return $this;
    }

    public function getRememberToken(): ?string
    {
        return $this->rememberToken;
    }

    public function setRememberToken(?string $rememberToken): self
    {
        $this->rememberToken = $rememberToken;
        $this->updatedAt = new \DateTime();
        return $this;
    }

    public function getRole(): string
    {
        return $this->role;
    }

    public function setRole(string $role): self
    {
        $this->role = $role;
        $this->updatedAt = new \DateTime();
        return $this;
    }

    public function getPermissions(): ?array
    {
        return $this->permissions ?? [];
    }

    public function setPermissions(?array $permissions): self
    {
        $this->permissions = $permissions;
        $this->updatedAt = new \DateTime();
        return $this;
    }

    public function addPermission(string $permission): self
    {
        $permissions = $this->getPermissions();
        if (!in_array($permission, $permissions)) {
            $permissions[] = $permission;
            $this->setPermissions($permissions);
        }
        return $this;
    }

    public function removePermission(string $permission): self
    {
        $permissions = $this->getPermissions();
        $key = array_search($permission, $permissions);
        if ($key !== false) {
            unset($permissions[$key]);
            $this->setPermissions(array_values($permissions));
        }
        return $this;
    }

    public function hasPermission(string $permission): bool
    {
        return in_array($permission, $this->getPermissions());
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;
        $this->updatedAt = new \DateTime();
        return $this;
    }

    public function getLastLoginAt(): ?\DateTime
    {
        return $this->lastLoginAt;
    }

    public function setLastLoginAt(?\DateTime $lastLoginAt): self
    {
        $this->lastLoginAt = $lastLoginAt;
        $this->updatedAt = new \DateTime();
        return $this;
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isModerator(): bool
    {
        return in_array($this->role, ['admin', 'moderator']);
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTime $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): \DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTime $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'email_verified_at' => $this->emailVerifiedAt?->format('Y-m-d H:i:s'),
            'role' => $this->role,
            'permissions' => $this->getPermissions(),
            'is_active' => $this->isActive,
            'last_login_at' => $this->lastLoginAt?->format('Y-m-d H:i:s'),
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt->format('Y-m-d H:i:s'),
        ];
    }
}
