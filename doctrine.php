<?php

declare(strict_types=1);

use Doctrine\ORM\Tools\Console\ConsoleRunner;
use Doctrine\ORM\Tools\Console\EntityManagerProvider\SingleManagerProvider;
use App\Config\Database;
use Dotenv\Dotenv;

require_once __DIR__ . '/vendor/autoload.php';

// Carregar variáveis de ambiente
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Inicializar o Doctrine
Database::init();
$entityManager = Database::getEntityManager();

// Configurar o Console Runner do Doctrine
ConsoleRunner::run(
    new SingleManagerProvider($entityManager)
);
