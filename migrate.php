<?php

declare(strict_types=1);

use App\Commands\MigrationCommand;
use Dotenv\Dotenv;

require_once __DIR__ . '/vendor/autoload.php';

// Carregar variáveis de ambiente
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Executar migração
MigrationCommand::run();
