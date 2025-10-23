# 📁 Pasta `bin/` - Executáveis do Projeto

Esta pasta contém os executáveis do projeto para facilitar o uso dos comandos.

## 🚀 Executáveis Disponíveis

### `doctrine-cli`
CLI personalizado para comandos Doctrine simplificados.

```bash
# Usar diretamente
php bin/doctrine-cli <comando>

# Via composer
composer doctrine-cli <comando>
```

**Comandos disponíveis:**
- `create-schema` - Criar esquema do banco
- `update-schema` - Atualizar esquema
- `drop-schema` - Dropar esquema (CUIDADO!)
- `validate-schema` - Validar mapeamento
- `generate-sql` - Gerar SQL
- `list-entities` - Listar entidades
- `show-entity <Entity>` - Mostrar detalhes da entidade
- `help` - Mostrar ajuda

### `doctrine`
Executável do Doctrine ORM original com todos os comandos.

```bash
# Usar diretamente
php bin/doctrine <comando>

# Via composer
composer doctrine <comando>
```

**Comandos mais úteis:**
- `orm:mapping:describe <Entity>` - Descrever mapeamento
- `orm:schema-tool:create` - Criar esquema
- `orm:schema-tool:update` - Atualizar esquema
- `orm:schema-tool:drop` - Dropar esquema
- `orm:validate-schema` - Validar esquema
- `migrations:generate` - Gerar migração
- `migrations:migrate` - Executar migrações
- `migrations:status` - Status das migrações

### `migrate`
Executável para migração simples do banco de dados.

```bash
# Usar diretamente
php bin/migrate

# Via composer
composer migrate
```

### `serve`
Executável para iniciar o servidor de desenvolvimento.

```bash
# Usar diretamente
php bin/serve

# Via composer
composer start
composer serve
```

## 📋 Comandos Composer Disponíveis

Todos os executáveis também podem ser chamados via Composer:

```bash
# Servidor
composer start
composer serve

# Migração
composer migrate

# Doctrine CLI personalizado
composer doctrine-cli
composer schema:create
composer schema:update
composer schema:drop
composer schema:validate
composer schema:sql
composer entities:list

# Doctrine original
composer doctrine
```

## 🔧 Configuração

Os executáveis são configurados para:
- ✅ Carregar automaticamente as variáveis de ambiente (`.env`)
- ✅ Inicializar o Doctrine ORM
- ✅ Usar o autoloader do Composer
- ✅ Funcionar tanto no Windows quanto no Linux/Mac

## 📝 Exemplos de Uso

### Criar esquema do banco:
```bash
composer schema:create
# ou
php bin/doctrine-cli create-schema
```

### Listar entidades:
```bash
composer entities:list
# ou
php bin/doctrine-cli list-entities
```

### Ver detalhes da entidade User:
```bash
php bin/doctrine-cli show-entity App\Entities\User
```

### Iniciar servidor:
```bash
composer start
# ou
php bin/serve
```

### Usar comandos Doctrine originais:
```bash
php bin/doctrine orm:mapping:describe App\Entities\User
php bin/doctrine migrations:generate
php bin/doctrine migrations:migrate
```

## ⚠️ Notas Importantes

- **Windows**: Use `php bin/executavel` em vez de `./bin/executavel`
- **Linux/Mac**: Pode usar tanto `php bin/executavel` quanto `./bin/executavel`
- **Permissões**: Os arquivos são executáveis por padrão
- **Variáveis de ambiente**: Certifique-se de que o arquivo `.env` está configurado
