<?php

declare(strict_types=1);

namespace App\Commands;

use App\Config\Database;

class MigrationCommand
{
    public static function run(): void
    {
        echo "Iniciando migração do banco de dados...\n";
        
        try {
            Database::init();
            Database::createTables();
            
            echo "✅ Migração concluída com sucesso!\n";
            echo "Tabelas criadas:\n";
            echo "- users\n";
            echo "- products\n";
            
        } catch (\Exception $e) {
            echo "❌ Erro na migração: " . $e->getMessage() . "\n";
            exit(1);
        }
    }
}
