<?php

declare(strict_types=1);

namespace App\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use App\Services\ApiResponseService;

class RoleMiddleware
{
    private array $allowedRoles;
    private array $requiredPermissions;

    public function __construct(array $allowedRoles = [], array $requiredPermissions = [])
    {
        $this->allowedRoles = $allowedRoles;
        $this->requiredPermissions = $requiredPermissions;
    }

    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        $user = $request->getAttribute('user');
        
        if (!$user) {
            $response = new \Slim\Psr7\Response();
            return ApiResponseService::error($response, 'Usuário não autenticado', 401);
        }

        // Verificar roles
        if (!empty($this->allowedRoles)) {
            $userRole = $user->role ?? 'user';
            if (!in_array($userRole, $this->allowedRoles)) {
                $response = new \Slim\Psr7\Response();
                return ApiResponseService::error($response, 'Acesso negado: role insuficiente', 403);
            }
        }

        // Verificar permissões
        if (!empty($this->requiredPermissions)) {
            $userPermissions = $user->permissions ?? [];
            foreach ($this->requiredPermissions as $permission) {
                if (!in_array($permission, $userPermissions)) {
                    $response = new \Slim\Psr7\Response();
                    return ApiResponseService::error($response, 'Acesso negado: permissão insuficiente', 403);
                }
            }
        }

        return $handler->handle($request);
    }

    /**
     * Middleware para administradores
     */
    public static function admin(): self
    {
        return new self(['admin']);
    }

    /**
     * Middleware para moderadores e administradores
     */
    public static function moderator(): self
    {
        return new self(['admin', 'moderator']);
    }

    /**
     * Middleware para permissões específicas
     */
    public static function permission(array $permissions): self
    {
        return new self([], $permissions);
    }

    /**
     * Middleware para role específica
     */
    public static function role(array $roles): self
    {
        return new self($roles);
    }
}
