<?php

declare(strict_types=1);

namespace App\Commands;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Console\Command\SchemaTool\CreateCommand;
use Doctrine\ORM\Tools\Console\Command\SchemaTool\DropCommand;
use Doctrine\ORM\Tools\Console\Command\SchemaTool\UpdateCommand;
use Doctrine\ORM\Tools\SchemaTool;
use App\Config\Database;

class DoctrineCommands
{
    private EntityManager $entityManager;
    private SchemaTool $schemaTool;

    public function __construct()
    {
        Database::init();
        $this->entityManager = Database::getEntityManager();
        $this->schemaTool = new SchemaTool($this->entityManager);
    }

    /**
     * Criar todas as tabelas baseadas nas entidades
     */
    public function createSchema(): void
    {
        echo "🔄 Criando esquema do banco de dados...\n";
        
        try {
            $classes = $this->entityManager->getMetadataFactory()->getAllMetadata();
            $this->schemaTool->createSchema($classes);
            
            echo "✅ Esquema criado com sucesso!\n";
            echo "Tabelas criadas:\n";
            foreach ($classes as $class) {
                echo "- {$class->getTableName()}\n";
            }
            
        } catch (\Exception $e) {
            echo "❌ Erro ao criar esquema: " . $e->getMessage() . "\n";
            exit(1);
        }
    }

    /**
     * Atualizar esquema do banco de dados
     */
    public function updateSchema(): void
    {
        echo "🔄 Atualizando esquema do banco de dados...\n";
        
        try {
            $classes = $this->entityManager->getMetadataFactory()->getAllMetadata();
            $sqls = $this->schemaTool->getUpdateSchemaSql($classes);
            
            if (empty($sqls)) {
                echo "✅ Esquema já está atualizado!\n";
                return;
            }
            
            echo "SQLs que serão executados:\n";
            foreach ($sqls as $sql) {
                echo "- {$sql}\n";
            }
            
            $this->schemaTool->updateSchema($classes);
            echo "✅ Esquema atualizado com sucesso!\n";
            
        } catch (\Exception $e) {
            echo "❌ Erro ao atualizar esquema: " . $e->getMessage() . "\n";
            exit(1);
        }
    }

    /**
     * Dropar todas as tabelas
     */
    public function dropSchema(): void
    {
        echo "⚠️  ATENÇÃO: Esta operação irá DROPAR TODAS as tabelas!\n";
        echo "Digite 'CONFIRMAR' para continuar: ";
        
        $confirmation = trim(fgets(STDIN));
        
        if ($confirmation !== 'CONFIRMAR') {
            echo "❌ Operação cancelada.\n";
            return;
        }
        
        echo "🔄 Dropando esquema do banco de dados...\n";
        
        try {
            $classes = $this->entityManager->getMetadataFactory()->getAllMetadata();
            $this->schemaTool->dropSchema($classes);
            
            echo "✅ Esquema dropado com sucesso!\n";
            
        } catch (\Exception $e) {
            echo "❌ Erro ao dropar esquema: " . $e->getMessage() . "\n";
            exit(1);
        }
    }

    /**
     * Validar mapeamento das entidades
     */
    public function validateSchema(): void
    {
        echo "🔄 Validando mapeamento das entidades...\n";
        
        try {
            $validator = new \Doctrine\ORM\Tools\SchemaValidator($this->entityManager);
            $errors = $validator->validateMapping();
            
            if (empty($errors)) {
                echo "✅ Mapeamento válido!\n";
            } else {
                echo "❌ Erros encontrados no mapeamento:\n";
                foreach ($errors as $error) {
                    echo "- {$error}\n";
                }
            }
            
        } catch (\Exception $e) {
            echo "❌ Erro ao validar esquema: " . $e->getMessage() . "\n";
            exit(1);
        }
    }

    /**
     * Gerar SQL de criação das tabelas
     */
    public function generateSql(): void
    {
        echo "🔄 Gerando SQL de criação das tabelas...\n";
        
        try {
            $classes = $this->entityManager->getMetadataFactory()->getAllMetadata();
            $sqls = $this->schemaTool->getCreateSchemaSql($classes);
            
            echo "SQL gerado:\n";
            echo str_repeat("=", 50) . "\n";
            foreach ($sqls as $sql) {
                echo "{$sql};\n\n";
            }
            echo str_repeat("=", 50) . "\n";
            
        } catch (\Exception $e) {
            echo "❌ Erro ao gerar SQL: " . $e->getMessage() . "\n";
            exit(1);
        }
    }

    /**
     * Listar todas as entidades
     */
    public function listEntities(): void
    {
        echo "📋 Entidades encontradas:\n";
        
        try {
            $classes = $this->entityManager->getMetadataFactory()->getAllMetadata();
            
            foreach ($classes as $class) {
                echo "- {$class->getName()}\n";
                echo "  Tabela: {$class->getTableName()}\n";
                echo "  Campos: " . count($class->getFieldNames()) . "\n";
                echo "\n";
            }
            
        } catch (\Exception $e) {
            echo "❌ Erro ao listar entidades: " . $e->getMessage() . "\n";
            exit(1);
        }
    }

    /**
     * Mostrar informações de uma entidade específica
     */
    public function showEntity(string $entityName): void
    {
        echo "🔍 Informações da entidade: {$entityName}\n";
        
        try {
            $metadata = $this->entityManager->getMetadataFactory()->getMetadataFor($entityName);
            
            echo "Tabela: {$metadata->getTableName()}\n";
            echo "Campos:\n";
            
            foreach ($metadata->getFieldNames() as $fieldName) {
                $fieldMapping = $metadata->getFieldMapping($fieldName);
                echo "  - {$fieldName} ({$fieldMapping['type']})\n";
            }
            
            echo "\nAssociações:\n";
            foreach ($metadata->getAssociationNames() as $associationName) {
                $associationMapping = $metadata->getAssociationMapping($associationName);
                echo "  - {$associationName} ({$associationMapping['type']})\n";
            }
            
        } catch (\Exception $e) {
            echo "❌ Erro ao mostrar entidade: " . $e->getMessage() . "\n";
            exit(1);
        }
    }
}
