<?php

declare(strict_types=1);

use Slim\App;
use App\Controllers\HomeController;
use App\Controllers\AuthController;
use App\Controllers\VerificationController;
use App\Controllers\AdminController;
use App\Middleware\AuthMiddleware;
use App\Middleware\RateLimitMiddleware;
use App\Middleware\CorsMiddleware;
use App\Middleware\RoleMiddleware;
use App\Middleware\ApiVersionMiddleware;

return function (App $app) {
    // Middleware global
    $app->add(new CorsMiddleware());
    
    // Rotas públicas
    $app->group('', function ($group) {
        // Rota de teste
        $group->get('/', [HomeController::class, 'index']);
        
        // Rota de health check
        $group->get('/health', [HomeController::class, 'health']);
        
        // Rotas de autenticação com rate limiting
        $group->group('/auth', function ($authGroup) {
            $authGroup->post('/login', [AuthController::class, 'login'])
                ->add(new RateLimitMiddleware(5, 60)); // 5 tentativas por minuto
            
            $authGroup->post('/register', [AuthController::class, 'register'])
                ->add(new RateLimitMiddleware(3, 60)); // 3 registros por minuto
            
            $authGroup->post('/refresh', [AuthController::class, 'refresh']);
            $authGroup->post('/logout', [AuthController::class, 'logout']);
            
            // Rotas de verificação de email
            $authGroup->post('/send-verification', [VerificationController::class, 'sendVerificationEmail'])
                ->add(new RateLimitMiddleware(3, 60)); // 3 emails por minuto
            
            $authGroup->post('/verify-email', [VerificationController::class, 'verifyEmail']);
            
            // Rotas de reset de senha
            $authGroup->post('/forgot-password', [VerificationController::class, 'forgotPassword'])
                ->add(new RateLimitMiddleware(3, 60)); // 3 tentativas por minuto
            
            $authGroup->post('/reset-password', [VerificationController::class, 'resetPassword']);
            
            // Validação de token
            $authGroup->post('/validate-token', [VerificationController::class, 'validateToken']);
        });
    });

    // Rotas protegidas por autenticação com versionamento
    $app->group('/api', function ($group) {
        $group->get('/profile', [AuthController::class, 'profile']);
        
        // Rotas de administração (apenas para admins)
        $group->group('/admin', function ($adminGroup) {
            $adminGroup->get('/dashboard', [AdminController::class, 'dashboard']);
            $adminGroup->get('/security-logs', [AdminController::class, 'securityLogs']);
            $adminGroup->get('/user-stats', [AdminController::class, 'userStats']);
            $adminGroup->get('/health', [AdminController::class, 'healthCheck']);
            $adminGroup->post('/clear-cache', [AdminController::class, 'clearCache']);
            $adminGroup->post('/cleanup-logs', [AdminController::class, 'cleanupLogs']);
            $adminGroup->post('/cleanup-tokens', [AdminController::class, 'cleanupTokens']);
        })->add(RoleMiddleware::admin());
        
        // Aqui você pode adicionar outras rotas protegidas
        // Exemplo:
        // $group->group('/users', function ($userGroup) {
        //     $userGroup->get('', [UserController::class, 'index']);
        //     $userGroup->get('/{id}', [UserController::class, 'show']);
        // });
    })->add(new AuthMiddleware())->add(ApiVersionMiddleware::versions(['v1', 'v2']));
};
