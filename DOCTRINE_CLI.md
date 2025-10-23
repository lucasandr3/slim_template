# 🔧 Doctrine CLI - Comandos para Gerenciar Entidades

Este projeto inclui comandos personalizados para facilitar o gerenciamento das entidades Doctrine ORM.

## 📋 Comandos Disponíveis

### 🚀 Comandos Rápidos (Composer Scripts)

```bash
# Criar esquema do banco de dados
composer schema:create

# Atualizar esquema do banco de dados
composer schema:update

# Dropar esquema do banco de dados (CUIDADO!)
composer schema:drop

# Validar mapeamento das entidades
composer schema:validate

# Gerar SQL de criação das tabelas
composer schema:sql

# Listar todas as entidades
composer entities:list
```

### 🔧 Comandos Diretos

```bash
# Usar CLI personalizado
php doctrine-cli.php <comando>

# Usar Doctrine original
php doctrine.php <comando>
```

### 📋 Comandos de Esquema

| Comando | Descrição |
|---------|-----------|
| `create-schema` | Criar todas as tabelas baseadas nas entidades |
| `update-schema` | Atualizar esquema do banco de dados |
| `drop-schema` | Dropar todas as tabelas (requer confirmação) |
| `validate-schema` | Validar mapeamento das entidades |
| `generate-sql` | Gerar SQL de criação das tabelas |

### 📋 Comandos de Entidades

| Comando | Descrição |
|---------|-----------|
| `list-entities` | Listar todas as entidades |
| `show-entity <Entity>` | Mostrar informações de uma entidade específica |

## 🔧 Comandos Doctrine Originais Mais Úteis

```bash
# Descrever mapeamento de uma entidade
php doctrine.php orm:mapping:describe App\Entities\User

# Validar esquema
php doctrine.php orm:validate-schema

# Criar esquema
php doctrine.php orm:schema-tool:create

# Atualizar esquema
php doctrine.php orm:schema-tool:update

# Dropar esquema
php doctrine.php orm:schema-tool:drop

# Gerar migração
php doctrine.php migrations:generate

# Executar migrações
php doctrine.php migrations:migrate

# Status das migrações
php doctrine.php migrations:status

# Listar todas as migrações
php doctrine.php migrations:list
```

## 📝 Exemplos de Uso

### 1. Criar Esquema Inicial
```bash
# Usando composer script (recomendado)
composer schema:create

# Ou diretamente
php doctrine-cli.php create-schema
```

### 2. Listar Entidades
```bash
# Usando composer script
composer entities:list

# Ou diretamente
php doctrine-cli.php list-entities
```

### 3. Ver Detalhes de uma Entidade
```bash
php doctrine-cli.php show-entity App\Entities\User
php doctrine-cli.php show-entity App\Entities\Product
```

### 4. Gerar SQL para Revisão
```bash
composer schema:sql
```

### 5. Validar Mapeamento
```bash
composer schema:validate
```

## ⚠️ Cuidados Importantes

- **`drop-schema`**: Este comando remove TODAS as tabelas do banco de dados. Use com extrema cautela!
- **`update-schema`**: Sempre faça backup antes de atualizar o esquema em produção.
- **Validação**: Sempre execute `validate-schema` antes de aplicar mudanças.

## 🔍 Estrutura das Entidades

As entidades estão localizadas em `src/Entities/`:

- `User.php` - Entidade de usuários
- `Product.php` - Entidade de produtos

## 📊 Configuração do Banco

O banco de dados é configurado através das variáveis de ambiente no arquivo `.env`:

```env
DB_HOST=localhost
DB_PORT=5432
DB_DATABASE=logicheck_api
DB_USERNAME=postgres
DB_PASSWORD=sua_senha
```

## 🚀 Fluxo de Trabalho Recomendado

1. **Desenvolvimento**: Crie/modifique entidades em `src/Entities/`
2. **Validação**: Execute `composer schema:validate`
3. **SQL Review**: Execute `composer schema:sql` para revisar as mudanças
4. **Aplicação**: Execute `composer schema:update` para aplicar mudanças
5. **Verificação**: Execute `composer entities:list` para confirmar

## 🆘 Ajuda

Para ver todos os comandos disponíveis:

```bash
php doctrine-cli.php help
php doctrine.php list
```
