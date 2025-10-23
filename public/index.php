<?php

declare(strict_types=1);

use DI\ContainerBuilder;
use Slim\Factory\AppFactory;
use Slim\Middleware\ErrorMiddleware;
use App\Middleware\CorsMiddleware;
use App\Middleware\ExceptionHandlerMiddleware;
use App\Config\Database;
use Dotenv\Dotenv;

require_once __DIR__ . '/../vendor/autoload.php';

// Carregar variáveis de ambiente
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// Configurar container DI
$containerBuilder = new ContainerBuilder();

// Configurações do container
$containerBuilder->addDefinitions([
    'settings' => [
        'displayErrorDetails' => $_ENV['APP_DEBUG'] === 'true',
        'logErrorDetails' => true,
        'logErrors' => true,
    ],
]);

$container = $containerBuilder->build();

// Criar aplicação Slim
AppFactory::setContainer($container);
$app = AppFactory::create();

// Configurar conexão com banco de dados
Database::init();

// Middleware de parsing JSON (deve vir primeiro)
$app->addBodyParsingMiddleware();

// Middleware de CORS
$app->add(new CorsMiddleware());

// Middleware de tratamento de exceções personalizado
$app->add(new ExceptionHandlerMiddleware());

// Middleware de roteamento
$app->addRoutingMiddleware();

// Middleware de tratamento de erros padrão (fallback)
$errorMiddleware = $app->addErrorMiddleware(
    $_ENV['APP_DEBUG'] === 'true',
    true,
    true
);

// Registrar rotas
$routes = require_once __DIR__ . '/../src/Config/routes.php';
$routes($app);

// Executar aplicação
$app->run();
