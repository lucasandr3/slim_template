<?php

declare(strict_types=1);

namespace App\Config;

use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;
use Doctrine\DBAL\Connection;

class Database
{
    private static ?EntityManager $entityManager = null;
    private static ?Connection $connection = null;

    public static function init(): void
    {
        if (self::$entityManager === null) {
            // Configuração do Doctrine
            $config = ORMSetup::createAttributeMetadataConfiguration(
                paths: [__DIR__ . '/../Entities'],
                isDevMode: true,
            );

            // Configuração da conexão
            $connectionParams = [
                'driver' => 'pdo_pgsql',
                'host' => $_ENV['DB_HOST'] ?? 'localhost',
                'port' => $_ENV['DB_PORT'] ?? '5432',
                'dbname' => $_ENV['DB_DATABASE'] ?? 'logicheck_api',
                'user' => $_ENV['DB_USERNAME'] ?? 'postgres',
                'password' => $_ENV['DB_PASSWORD'] ?? '',
                'charset' => 'utf8',
            ];

            // Criar conexão
            self::$connection = DriverManager::getConnection($connectionParams, $config);
            
            // Criar EntityManager
            self::$entityManager = new EntityManager(self::$connection, $config);
        }
    }

    public static function getEntityManager(): EntityManager
    {
        if (self::$entityManager === null) {
            self::init();
        }
        
        return self::$entityManager;
    }

    public static function getConnection(): Connection
    {
        if (self::$connection === null) {
            self::init();
        }
        
        return self::$connection;
    }

    public static function createTables(): void
    {
        $connection = self::getConnection();
        
        // Criar tabela de usuários
        $connection->executeStatement("
            CREATE TABLE IF NOT EXISTS users (
                id SERIAL PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                email VARCHAR(255) UNIQUE NOT NULL,
                email_verified_at TIMESTAMP NULL,
                password VARCHAR(255) NOT NULL,
                remember_token VARCHAR(100) NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");

        // Criar tabela de produtos
        $connection->executeStatement("
            CREATE TABLE IF NOT EXISTS products (
                id SERIAL PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                description TEXT NULL,
                price DECIMAL(10,2) NOT NULL,
                stock INTEGER DEFAULT 0,
                active BOOLEAN DEFAULT true,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");
    }
}

