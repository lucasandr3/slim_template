<?php

declare(strict_types=1);

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Services\ApiResponseService;

class HomeController
{
    public function index(Request $request, Response $response): Response
    {
        $data = [
            'message' => 'Bem-vindo à LogicCheck API',
            'version' => '1.0.0',
            'status' => 'online',
            'timestamp' => date('Y-m-d H:i:s')
        ];

        return ApiResponseService::success($response, $data, 'API funcionando corretamente');
    }

    public function health(Request $request, Response $response): Response
    {
        $data = [
            'status' => 'healthy',
            'timestamp' => date('Y-m-d H:i:s'),
            'uptime' => time() - ($_SERVER['REQUEST_TIME'] ?? time())
        ];

        return ApiResponseService::success($response, $data, 'Sistema saudável');
    }
}
