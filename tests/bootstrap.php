<?php

declare(strict_types=1);

// Bootstrap para testes - configuração mínima sem dependências de banco
require_once __DIR__ . '/../vendor/autoload.php';

// Definir variáveis de ambiente para testes
$_ENV['APP_ENV'] = 'testing';
$_ENV['APP_DEBUG'] = 'true';
$_ENV['JWT_SECRET'] = 'test-secret-key-for-testing-only-minimum-32-chars';
$_ENV['JWT_EXPIRATION'] = '3600';
$_ENV['CACHE_DRIVER'] = 'array';
$_ENV['DB_CONNECTION'] = 'sqlite';
$_ENV['DB_DATABASE'] = ':memory:';

// Configurar timezone
date_default_timezone_set('UTC');

// Configurar error reporting para testes
error_reporting(E_ALL);
ini_set('display_errors', '1');
