<?php

declare(strict_types=1);

namespace App\Entities;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\DBAL\Types\Types;

#[ORM\Entity]
#[ORM\Table(name: 'verification_tokens')]
class VerificationToken
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private string $token;

    #[ORM\Column(type: Types::STRING, length: 100)]
    private string $type; // 'email_verification', 'password_reset'

    #[ORM\Column(type: Types::STRING, length: 255)]
    private string $email;

    #[ORM\Column(name: 'expires_at', type: Types::DATETIME_MUTABLE)]
    private \DateTime $expiresAt;

    #[ORM\Column(name: 'used_at', type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTime $usedAt = null;

    #[ORM\Column(name: 'created_at', type: Types::DATETIME_MUTABLE)]
    private \DateTime $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function setToken(string $token): self
    {
        $this->token = $token;
        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;
        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getExpiresAt(): \DateTime
    {
        return $this->expiresAt;
    }

    public function setExpiresAt(\DateTime $expiresAt): self
    {
        $this->expiresAt = $expiresAt;
        return $this;
    }

    public function getUsedAt(): ?\DateTime
    {
        return $this->usedAt;
    }

    public function setUsedAt(?\DateTime $usedAt): self
    {
        $this->usedAt = $usedAt;
        return $this;
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

    public function isExpired(): bool
    {
        return $this->expiresAt < new \DateTime();
    }

    public function isUsed(): bool
    {
        return $this->usedAt !== null;
    }

    public function isValid(): bool
    {
        return !$this->isExpired() && !$this->isUsed();
    }

    public function markAsUsed(): self
    {
        $this->usedAt = new \DateTime();
        return $this;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'token' => $this->token,
            'type' => $this->type,
            'email' => $this->email,
            'expires_at' => $this->expiresAt->format('Y-m-d H:i:s'),
            'used_at' => $this->usedAt?->format('Y-m-d H:i:s'),
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
        ];
    }
}
