<?php

declare(strict_types=1);

namespace App\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use App\Services\ApiResponseService;

class RateLimitMiddleware
{
    private int $maxRequests;
    private int $windowSeconds;
    private array $requests = [];

    public function __construct(?int $maxRequests = null, ?int $windowSeconds = null)
    {
        $this->maxRequests = $maxRequests ?? (int)($_ENV['RATE_LIMIT_REQUESTS'] ?? 100);
        $this->windowSeconds = $windowSeconds ?? (int)($_ENV['RATE_LIMIT_WINDOW'] ?? 60);
    }

    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        $clientIp = $this->getClientIp($request);
        $currentTime = time();

        // Limpar requisições antigas
        $this->cleanOldRequests($currentTime);

        // Verificar limite
        if ($this->isRateLimited($clientIp, $currentTime)) {
            $response = new \Slim\Psr7\Response();
            return ApiResponseService::error(
                $response, 
                'Muitas requisições. Tente novamente em alguns minutos.', 
                429
            );
        }

        // Registrar requisição
        $this->requests[$clientIp][] = $currentTime;

        return $handler->handle($request);
    }

    private function getClientIp(Request $request): string
    {
        $headers = [
            'HTTP_CF_CONNECTING_IP',     // Cloudflare
            'HTTP_X_FORWARDED_FOR',      // Load balancer/proxy
            'HTTP_X_FORWARDED',          // Proxy
            'HTTP_X_CLUSTER_CLIENT_IP',  // Cluster
            'HTTP_FORWARDED_FOR',        // Proxy
            'HTTP_FORWARDED',            // Proxy
            'REMOTE_ADDR'                // Standard
        ];

        foreach ($headers as $header) {
            if (!empty($_SERVER[$header])) {
                $ips = explode(',', $_SERVER[$header]);
                return trim($ips[0]);
            }
        }

        return '127.0.0.1';
    }

    private function cleanOldRequests(int $currentTime): void
    {
        $cutoffTime = $currentTime - $this->windowSeconds;
        
        foreach ($this->requests as $ip => $times) {
            $this->requests[$ip] = array_filter($times, function($time) use ($cutoffTime) {
                return $time > $cutoffTime;
            });
            
            if (empty($this->requests[$ip])) {
                unset($this->requests[$ip]);
            }
        }
    }

    private function isRateLimited(string $clientIp, int $currentTime): bool
    {
        if (!isset($this->requests[$clientIp])) {
            return false;
        }

        $recentRequests = array_filter($this->requests[$clientIp], function($time) use ($currentTime) {
            return $time > ($currentTime - $this->windowSeconds);
        });

        return count($recentRequests) >= $this->maxRequests;
    }
}
