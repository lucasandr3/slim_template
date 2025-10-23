<?php

declare(strict_types=1);

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Services\VerificationService;
use App\Services\SecurityLogService;
use App\Services\ApiResponseService;
use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Http\Requests\VerifyEmailRequest;

class VerificationController
{
    private VerificationService $verificationService;
    private SecurityLogService $securityLogService;

    public function __construct()
    {
        $this->verificationService = new VerificationService();
        $this->securityLogService = new SecurityLogService();
    }

    /**
     * Envia email de verificação
     */
    public function sendVerificationEmail(Request $request, Response $response): Response
    {
        $formRequest = new VerifyEmailRequest($request);
        $data = $formRequest->validated();

        $token = $this->verificationService->generateEmailVerificationToken($data['email']);
        
        // Aqui você implementaria o envio do email
        // Por enquanto, retornamos o token para desenvolvimento
        $emailSent = $this->sendEmail($data['email'], 'Verificação de Email', [
            'token' => $token->getToken(),
            'expires_at' => $token->getExpiresAt()->format('Y-m-d H:i:s')
        ]);

        if ($emailSent) {
            $this->securityLogService->logEmailVerification(
                $data['email'],
                0, // Será atualizado quando o usuário se registrar
                $this->getClientIp($request),
                true
            );

            return ApiResponseService::success($response, null, 'Email de verificação enviado');
        }

        return ApiResponseService::error($response, 'Erro ao enviar email de verificação', 500);
    }

    /**
     * Verifica email com token
     */
    public function verifyEmail(Request $request, Response $response): Response
    {
        $formRequest = new VerifyEmailRequest($request);
        $data = $formRequest->validated();

        $verified = $this->verificationService->verifyEmailToken($data['token']);

        if ($verified) {
            return ApiResponseService::success($response, null, 'Email verificado com sucesso');
        }

        return ApiResponseService::error($response, 'Token inválido ou expirado', 400);
    }

    /**
     * Solicita reset de senha
     */
    public function forgotPassword(Request $request, Response $response): Response
    {
        $formRequest = new ForgotPasswordRequest($request);
        $data = $formRequest->validated();

        $token = $this->verificationService->generatePasswordResetToken($data['email']);
        
        if (!$token) {
            // Por segurança, não revelamos se o email existe ou não
            return ApiResponseService::success($response, null, 'Se o email existir, você receberá instruções para resetar sua senha');
        }

        // Aqui você implementaria o envio do email
        $emailSent = $this->sendEmail($data['email'], 'Reset de Senha', [
            'token' => $token->getToken(),
            'expires_at' => $token->getExpiresAt()->format('Y-m-d H:i:s')
        ]);

        $this->securityLogService->logPasswordReset(
            $data['email'],
            $this->getClientIp($request),
            $request->getHeaderLine('User-Agent'),
            $emailSent
        );

        return ApiResponseService::success($response, null, 'Se o email existir, você receberá instruções para resetar sua senha');
    }

    /**
     * Reseta senha com token
     */
    public function resetPassword(Request $request, Response $response): Response
    {
        $formRequest = new ResetPasswordRequest($request);
        $data = $formRequest->validated();

        $reset = $this->verificationService->resetPasswordWithToken($data['token'], $data['password']);

        if ($reset) {
            return ApiResponseService::success($response, null, 'Senha alterada com sucesso');
        }

        return ApiResponseService::error($response, 'Token inválido ou expirado', 400);
    }

    /**
     * Valida token sem usar
     */
    public function validateToken(Request $request, Response $response): Response
    {
        $formRequest = new VerifyEmailRequest($request);
        $data = $formRequest->validated();

        $valid = $this->verificationService->validateToken($data['token'], $data['type'] ?? 'email_verification');

        return ApiResponseService::success($response, ['valid' => $valid], 'Token validado');
    }

    /**
     * Simula envio de email (implementar com PHPMailer, SwiftMailer, etc.)
     */
    private function sendEmail(string $email, string $subject, array $data): bool
    {
        // Em produção, implementar envio real de email
        // Por enquanto, apenas log para desenvolvimento
        error_log("Email para {$email}: {$subject} - " . json_encode($data));
        return true;
    }

    /**
     * Obtém IP do cliente
     */
    private function getClientIp(Request $request): string
    {
        $headers = [
            'HTTP_CF_CONNECTING_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        ];

        foreach ($headers as $header) {
            if (!empty($_SERVER[$header])) {
                $ips = explode(',', $_SERVER[$header]);
                return trim($ips[0]);
            }
        }

        return '127.0.0.1';
    }
}
