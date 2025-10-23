<?php

declare(strict_types=1);

namespace App\Services;

use App\Entities\VerificationToken;
use App\Entities\User;
use App\Repositories\VerificationTokenRepository;
use App\Repositories\UserRepository;
use App\Config\Database;

class VerificationService
{
    private VerificationTokenRepository $tokenRepository;
    private UserRepository $userRepository;

    public function __construct()
    {
        $entityManager = Database::getEntityManager();
        $this->tokenRepository = new VerificationTokenRepository($entityManager);
        $this->userRepository = new UserRepository($entityManager);
    }

    /**
     * Gera token de verificação de email
     */
    public function generateEmailVerificationToken(string $email): VerificationToken
    {
        // Invalidar tokens anteriores
        $this->tokenRepository->invalidateUserTokens($email, 'email_verification');

        $token = new VerificationToken();
        $token->setToken($this->generateSecureToken())
              ->setType('email_verification')
              ->setEmail($email)
              ->setExpiresAt(new \DateTime('+24 hours'));

        $this->tokenRepository->save($token);

        return $token;
    }

    /**
     * Gera token de reset de senha
     */
    public function generatePasswordResetToken(string $email): ?VerificationToken
    {
        // Verificar se o usuário existe
        $user = $this->userRepository->findByEmail($email);
        if (!$user) {
            return null;
        }

        // Invalidar tokens anteriores
        $this->tokenRepository->invalidateUserTokens($email, 'password_reset');

        $token = new VerificationToken();
        $token->setToken($this->generateSecureToken())
              ->setType('password_reset')
              ->setEmail($email)
              ->setExpiresAt(new \DateTime('+1 hour'));

        $this->tokenRepository->save($token);

        return $token;
    }

    /**
     * Verifica token de email
     */
    public function verifyEmailToken(string $token): bool
    {
        $tokenEntity = $this->tokenRepository->findValidToken($token, 'email_verification');
        
        if (!$tokenEntity) {
            return false;
        }

        $user = $this->userRepository->findByEmail($tokenEntity->getEmail());
        if (!$user) {
            return false;
        }

        // Marcar email como verificado
        $user->setEmailVerifiedAt(new \DateTime());
        $this->userRepository->save($user);

        // Marcar token como usado
        $tokenEntity->markAsUsed();
        $this->tokenRepository->save($tokenEntity);

        return true;
    }

    /**
     * Reseta senha usando token
     */
    public function resetPasswordWithToken(string $token, string $newPassword): bool
    {
        $tokenEntity = $this->tokenRepository->findValidToken($token, 'password_reset');
        
        if (!$tokenEntity) {
            return false;
        }

        $user = $this->userRepository->findByEmail($tokenEntity->getEmail());
        if (!$user) {
            return false;
        }

        // Atualizar senha
        $user->setPassword(password_hash($newPassword, PASSWORD_DEFAULT));
        $this->userRepository->save($user);

        // Marcar token como usado
        $tokenEntity->markAsUsed();
        $this->tokenRepository->save($tokenEntity);

        return true;
    }

    /**
     * Valida token sem usar
     */
    public function validateToken(string $token, string $type): bool
    {
        $tokenEntity = $this->tokenRepository->findValidToken($token, $type);
        return $tokenEntity !== null;
    }

    /**
     * Limpa tokens expirados
     */
    public function cleanupExpiredTokens(): int
    {
        return $this->tokenRepository->deleteExpiredTokens();
    }

    /**
     * Gera token seguro
     */
    private function generateSecureToken(): string
    {
        return bin2hex(random_bytes(32));
    }
}
