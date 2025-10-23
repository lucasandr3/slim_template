<?php

declare(strict_types=1);

namespace App\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use App\Services\ApiResponseService;

class ApiVersionMiddleware
{
    private string $currentVersion;
    private array $supportedVersions;

    public function __construct(string $currentVersion = 'v1', array $supportedVersions = ['v1'])
    {
        $this->currentVersion = $currentVersion;
        $this->supportedVersions = $supportedVersions;
    }

    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        $path = $request->getUri()->getPath();
        
        // Extrair versão da URL (/api/v1/...)
        if (preg_match('/^\/api\/(v\d+)\//', $path, $matches)) {
            $requestedVersion = $matches[1];
            
            if (!in_array($requestedVersion, $this->supportedVersions)) {
                $response = new \Slim\Psr7\Response();
                return ApiResponseService::error(
                    $response,
                    'Versão da API não suportada. Versões disponíveis: ' . implode(', ', $this->supportedVersions),
                    400
                );
            }
            
            // Adicionar versão ao request
            $request = $request->withAttribute('api_version', $requestedVersion);
        } else {
            // Usar versão padrão se não especificada
            $request = $request->withAttribute('api_version', $this->currentVersion);
        }

        return $handler->handle($request);
    }

    /**
     * Middleware para versão específica
     */
    public static function version(string $version): self
    {
        return new self($version, [$version]);
    }

    /**
     * Middleware para múltiplas versões
     */
    public static function versions(array $versions): self
    {
        return new self($versions[0] ?? 'v1', $versions);
    }
}
