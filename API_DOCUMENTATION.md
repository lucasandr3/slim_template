# Documentação da API - Base de Autenticação

## Visão Geral

Esta API fornece um sistema completo de autenticação JWT com endpoints para registro, login, refresh de tokens e gerenciamento de perfil de usuário.

## Base URL

```
http://localhost:8000
```

## Autenticação

A API usa JWT (JSON Web Tokens) para autenticação. Inclua o token no header `Authorization`:

```
Authorization: Bearer <seu_token>
```

## Endpoints

### 1. Registro de Usuário

**POST** `/auth/register`

Registra um novo usuário no sistema.

**Rate Limit:** 3 requisições por minuto

**Body:**
```json
{
    "name": "João Silva",
    "email": "joao@email.com",
    "password": "123456"
}
```

**Resposta de Sucesso (201):**
```json
{
    "message": "Usuário criado com sucesso",
    "data": {
        "id": 1,
        "name": "João Silva",
        "email": "joao@email.com",
        "email_verified_at": null,
        "created_at": "2024-01-01 12:00:00",
        "updated_at": "2024-01-01 12:00:00"
    }
}
```

**Resposta de Erro (422):**
```json
{
    "message": "Dados de entrada inválidos",
    "errors": {
        "email": ["Este email já está em uso"]
    }
}
```

### 2. Login

**POST** `/auth/login`

Autentica um usuário e retorna tokens de acesso.

**Rate Limit:** 5 requisições por minuto

**Body:**
```json
{
    "email": "joao@email.com",
    "password": "123456"
}
```

**Resposta de Sucesso (200):**
```json
{
    "message": "Login realizado com sucesso",
    "data": {
        "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
        "refresh_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
        "token_type": "Bearer",
        "expires_in": 3600,
        "user": {
            "id": 1,
            "name": "João Silva",
            "email": "joao@email.com",
            "created_at": "2024-01-01 12:00:00"
        }
    }
}
```

**Resposta de Erro (401):**
```json
{
    "message": "Credenciais inválidas"
}
```

### 3. Refresh Token

**POST** `/auth/refresh`

Renova o token de acesso usando o refresh token.

**Headers:**
```
Authorization: Bearer <refresh_token>
```

**Resposta de Sucesso (200):**
```json
{
    "message": "Token renovado com sucesso",
    "data": {
        "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
        "refresh_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
        "token_type": "Bearer",
        "expires_in": 3600
    }
}
```

### 4. Logout

**POST** `/auth/logout`

Realiza logout do usuário (token deve ser invalidado no cliente).

**Headers:**
```
Authorization: Bearer <access_token>
```

**Resposta de Sucesso (200):**
```json
{
    "message": "Logout realizado com sucesso",
    "data": null
}
```

### 5. Perfil do Usuário

**GET** `/api/profile`

Obtém informações do perfil do usuário autenticado.

**Headers:**
```
Authorization: Bearer <access_token>
```

**Resposta de Sucesso (200):**
```json
{
    "message": "Perfil do usuário",
    "data": {
        "id": 1,
        "name": "João Silva",
        "email": "joao@email.com",
        "email_verified_at": null,
        "created_at": "2024-01-01 12:00:00",
        "updated_at": "2024-01-01 12:00:00"
    }
}
```

### 6. Health Check

**GET** `/health`

Verifica o status da API.

**Resposta de Sucesso (200):**
```json
{
    "message": "API funcionando corretamente",
    "data": {
        "status": "ok",
        "timestamp": "2024-01-01T12:00:00Z",
        "version": "1.0.0"
    }
}
```

## Códigos de Status HTTP

- **200** - Sucesso
- **201** - Criado com sucesso
- **401** - Não autorizado
- **422** - Dados inválidos
- **429** - Muitas requisições (Rate Limit)
- **500** - Erro interno do servidor

## Validações

### Registro
- `name`: Obrigatório, mínimo 2 caracteres, máximo 255
- `email`: Obrigatório, formato de email válido, máximo 255 caracteres
- `password`: Obrigatório, mínimo 6 caracteres, máximo 255

### Login
- `email`: Obrigatório, formato de email válido
- `password`: Obrigatório

## Rate Limiting

- **Login:** 5 tentativas por minuto
- **Registro:** 3 tentativas por minuto
- **Outras rotas:** 100 requisições por minuto

## Exemplos de Uso

### JavaScript (Fetch API)

```javascript
// Login
const loginResponse = await fetch('http://localhost:8000/auth/login', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
    },
    body: JSON.stringify({
        email: 'joao@email.com',
        password: '123456'
    })
});

const loginData = await loginResponse.json();
const token = loginData.data.access_token;

// Usar token em requisições protegidas
const profileResponse = await fetch('http://localhost:8000/api/profile', {
    headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json',
    }
});

const profileData = await profileResponse.json();
```

### cURL

```bash
# Login
curl -X POST http://localhost:8000/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"joao@email.com","password":"123456"}'

# Perfil (substitua TOKEN pelo token recebido)
curl -X GET http://localhost:8000/api/profile \
  -H "Authorization: Bearer TOKEN"
```

## Segurança

- Senhas são hasheadas com `password_hash()` do PHP
- Tokens JWT são assinados com chave secreta
- Rate limiting implementado para prevenir ataques
- Validação rigorosa de entrada
- Headers CORS configurados
- Logs de segurança implementados
