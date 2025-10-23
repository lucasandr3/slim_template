<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Entities\User;

class UserResource extends Resource
{
    protected function transform($user): array
    {
        if (!$user instanceof User) {
            return [];
        }

        return [
            'id' => $user->getId(),
            'name' => $user->getName(),
            'email' => $user->getEmail(),
            'email_verified_at' => $user->getEmailVerifiedAt()?->format('Y-m-d H:i:s'),
            'created_at' => $user->getCreatedAt()->format('Y-m-d H:i:s'),
            'updated_at' => $user->getUpdatedAt()->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Resource público (sem dados sensíveis)
     */
    public function public(): array
    {
        return $this->only(['id', 'name', 'email']);
    }

    /**
     * Resource para perfil do usuário
     */
    public function profile(): array
    {
        return $this->except(['password', 'remember_token']);
    }

    /**
     * Resource para autenticação
     */
    public function auth(): array
    {
        return $this->only(['id', 'name', 'email', 'created_at']);
    }
}
