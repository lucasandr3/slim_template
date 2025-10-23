# Testes da API Base de Autenticação

## Estrutura de Testes

O projeto possui uma estrutura completa de testes organizada em três categorias:

### 📁 Diretórios de Teste

```
tests/
├── Unit/           # Testes unitários
├── Integration/    # Testes de integração
├── Feature/        # Testes de funcionalidade/API
└── bootstrap.php   # Configuração de testes
```

## 🧪 Tipos de Testes

### 1. Testes Unitários (`tests/Unit/`)

Testam componentes individuais isoladamente:

- **AuthServiceTest**: Testa serviços de autenticação
  - Geração e validação de tokens JWT
  - Cache de tokens e blacklist
  - Entidades User e VerificationToken
  - Métodos de roles e permissões

**Executar:**
```bash
composer test:unit
```

### 2. Testes de Integração (`tests/Integration/`)

Testam a integração entre componentes:

- **AuthIntegrationTest**: Testa fluxos completos
  - Fluxo de autenticação completo
  - Cache de tokens e rate limiting
  - Validação de tokens e expiração
  - Sistema de roles e permissões

**Executar:**
```bash
composer test:integration
```

### 3. Testes de Feature (`tests/Feature/`)

Testam funcionalidades da API:

- **ApiFeatureTest**: Testa endpoints da API
  - Endpoints de autenticação
  - Endpoints de verificação de email
  - Endpoints de reset de senha
  - Endpoints protegidos
  - Versionamento da API
  - Rate limiting e CORS

**Executar:**
```bash
composer test:feature
```

## 🚀 Comandos de Teste

```bash
# Executar todos os testes
composer test

# Executar apenas testes unitários
composer test:unit

# Executar apenas testes de integração
composer test:integration

# Executar apenas testes de feature
composer test:feature

# Executar testes com cobertura
composer test:coverage
```

## ⚙️ Configuração

### PHPUnit (`phpunit.xml`)

```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit bootstrap="tests/bootstrap.php" colors="true">
    <testsuites>
        <testsuite name="Unit">
            <directory>tests/Unit</directory>
        </testsuite>
        <testsuite name="Integration">
            <directory>tests/Integration</directory>
        </testsuite>
        <testsuite name="Feature">
            <directory>tests/Feature</directory>
        </testsuite>
    </testsuites>
</phpunit>
```

### Bootstrap (`tests/bootstrap.php`)

Configuração mínima para testes sem dependências de banco:

```php
<?php
require_once __DIR__ . '/../vendor/autoload.php';

// Variáveis de ambiente para testes
$_ENV['APP_ENV'] = 'testing';
$_ENV['JWT_SECRET'] = 'test-secret-key-for-testing-only';
$_ENV['CACHE_DRIVER'] = 'array';

date_default_timezone_set('UTC');
```

## 📊 Cobertura de Testes

### Componentes Testados

✅ **Serviços**
- JwtService (geração, validação, extração de tokens)
- TokenCacheService (blacklist, cache de usuários, rate limiting)
- SecurityLogService (logs de segurança, estatísticas)

✅ **Entidades**
- User (roles, permissões, métodos auxiliares)
- VerificationToken (validação, expiração, uso)

✅ **Endpoints da API**
- Autenticação (login, registro, refresh, logout)
- Verificação de email (envio, verificação)
- Reset de senha (solicitação, reset)
- Endpoints protegidos (profile, admin)
- Versionamento da API
- Rate limiting e CORS

✅ **Middleware**
- AuthMiddleware (validação de tokens)
- RoleMiddleware (controle de acesso)
- RateLimitMiddleware (proteção contra ataques)
- ApiVersionMiddleware (versionamento)

## 🔧 Executando Testes

### Pré-requisitos

1. **Instalar dependências:**
```bash
composer install
```

2. **Configurar ambiente:**
```bash
cp env.example .env
```

### Execução

```bash
# Todos os testes
composer test

# Testes específicos
composer test:unit
composer test:integration  
composer test:feature

# Com cobertura de código
composer test:coverage
```

## 📈 Resultados Esperados

### Testes Unitários
- ✅ 9 testes, 34 asserções
- ✅ Tempo: ~0.088s
- ✅ Memória: 8MB

### Testes de Feature
- ✅ 9 testes, 15 asserções
- ✅ Tempo: ~0.047s
- ✅ Memória: 10MB

### Testes de Integração
- ✅ 7 testes, 21 asserções
- ✅ Tempo: ~0.1s
- ✅ Memória: 12MB

## 🐛 Resolução de Problemas

### Erro: "Test directory not found"
```bash
mkdir -p tests/Unit tests/Integration tests/Feature
```

### Erro: "Database connection failed"
Os testes de integração usam mocks para evitar dependências de banco de dados.

### Erro: "Class not found"
```bash
composer dump-autoload
```

## 📝 Adicionando Novos Testes

### 1. Teste Unitário
```php
<?php
namespace App\Tests\Unit;

use PHPUnit\Framework\TestCase;

class NewServiceTest extends TestCase
{
    public function testNewFeature(): void
    {
        $this->assertTrue(true);
    }
}
```

### 2. Teste de Integração
```php
<?php
namespace App\Tests\Integration;

use PHPUnit\Framework\TestCase;

class NewIntegrationTest extends TestCase
{
    public function testIntegration(): void
    {
        $this->assertTrue(true);
    }
}
```

### 3. Teste de Feature
```php
<?php
namespace App\Tests\Feature;

use PHPUnit\Framework\TestCase;

class NewFeatureTest extends TestCase
{
    public function testApiEndpoint(): void
    {
        $this->assertTrue(true);
    }
}
```

## 🎯 Boas Práticas

1. **Nomenclatura**: Use nomes descritivos para testes
2. **Isolamento**: Cada teste deve ser independente
3. **Mocks**: Use mocks para dependências externas
4. **Asserções**: Use asserções específicas e claras
5. **Cobertura**: Mantenha alta cobertura de código
6. **Performance**: Mantenha testes rápidos e eficientes

## 📚 Recursos Adicionais

- [PHPUnit Documentation](https://phpunit.readthedocs.io/)
- [Slim Framework Testing](https://www.slimframework.com/docs/v4/start/testing.html)
- [Doctrine Testing](https://www.doctrine-project.org/projects/doctrine-orm/en/current/reference/testing.html)
