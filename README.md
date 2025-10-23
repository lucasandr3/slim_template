# API Base de Autenticação Avançada - Slim Framework 4

Uma API REST robusta e completa construída com Slim Framework 4, focada em autenticação JWT avançada, sistema de roles/permissões, verificação de email, reset de senha, logs de segurança e monitoramento.

## 🚀 Características Principais

- **Slim Framework 4** - Framework PHP moderno e eficiente
- **Autenticação JWT Avançada** - Sistema seguro com access/refresh tokens
- **Sistema de Roles/Permissões** - Controle granular de acesso
- **Verificação de Email** - Sistema completo de verificação
- **Reset de Senha** - Recuperação segura com tokens temporários
- **Logs de Segurança** - Monitoramento completo de atividades
- **Cache Inteligente** - Blacklist de tokens e cache de dados
- **Rate Limiting** - Proteção contra ataques de força bruta
- **API Versioning** - Suporte a múltiplas versões da API
- **Monitoramento** - Dashboard de administração e health checks
- **Testes Automatizados** - Suite completa de testes
- **Doctrine ORM** - Mapeamento objeto-relacional robusto
- **Validação de Dados** - Validação robusta com Respect/Validation
- **Estrutura MVC** - Organização clara e escalável
- **Middleware Personalizado** - CORS, autenticação, roles e tratamento de exceções
- **PSR-4 Autoloading** - Padrão moderno de carregamento de classes
- **Type Declarations** - Tipagem estrita para melhor performance e segurança

## 📁 Estrutura do Projeto

```
src/
├── Commands/           # Comandos CLI personalizados
├── Config/            # Configurações (Database, Routes)
├── Controllers/       # Controladores da aplicação
├── Entities/          # Entidades Doctrine ORM
├── Exceptions/        # Exceções personalizadas
├── Http/
│   ├── Requests/     # Classes de validação de requisições
│   └── Resources/    # Transformadores de dados (API Resources)
├── Middleware/        # Middlewares personalizados
├── Repositories/      # Repositórios para acesso a dados
└── Services/         # Serviços da aplicação
```

## 🛠️ Instalação

1. **Clone o repositório:**
```bash
git clone <seu-repositorio>
cd logicheck_api
```

2. **Instale as dependências:**
```bash
composer install
```

3. **Configure o ambiente:**
```bash
cp env.example .env
```

4. **Configure as variáveis de ambiente no arquivo `.env`:**
```env
DB_HOST=localhost
DB_PORT=3306
DB_NAME=logicheck_api
DB_USER=root
DB_PASSWORD=

JWT_SECRET=sua_chave_secreta_jwt_aqui
JWT_EXPIRATION=3600
```

5. **Execute as migrações:**
```bash
composer migrate
```

6. **Inicie o servidor:**
```bash
composer start
```

## 🔐 Endpoints de Autenticação

### POST /auth/register
Registra um novo usuário.

**Body:**
```json
{
    "name": "João Silva",
    "email": "joao@email.com",
    "password": "123456"
}
```

### POST /auth/login
Realiza login do usuário.

**Body:**
```json
{
    "email": "joao@email.com",
    "password": "123456"
}
```

**Response:**
```json
{
    "message": "Login realizado com sucesso",
    "data": {
        "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
        "user": {
            "id": 1,
            "name": "João Silva",
            "email": "joao@email.com",
            "created_at": "2024-01-01 12:00:00"
        }
    }
}
```

### POST /auth/refresh
Renova o token JWT.

**Headers:**
```
Authorization: Bearer <token>
```

### POST /auth/logout
Realiza logout (invalidar token no cliente).

**Headers:**
```
Authorization: Bearer <token>
```

### GET /api/profile
Obtém dados do perfil do usuário autenticado.

**Headers:**
```
Authorization: Bearer <token>
```

## 🛡️ Middleware de Autenticação

Todas as rotas protegidas devem incluir o header:
```
Authorization: Bearer <token>
```

## 📝 Comandos Disponíveis

```bash
# Iniciar servidor
composer start

# Executar migrações
composer migrate

# Comandos Doctrine
composer doctrine

# Validação de código
composer cs-check
composer cs-fix

# Testes
composer test
```

## 🔧 Configuração do Banco de Dados

O projeto usa Doctrine ORM com suporte a:
- MySQL/MariaDB
- PostgreSQL
- SQLite

Configure a conexão no arquivo `.env` e execute as migrações.

## 📚 Próximos Passos

Este projeto serve como base para outros projetos. Você pode:

1. **Adicionar novas entidades** seguindo o padrão existente
2. **Implementar novos endpoints** usando a estrutura MVC
3. **Adicionar novos middlewares** conforme necessário
4. **Expandir o sistema de autenticação** com roles/permissões
5. **Implementar testes automatizados**

## 🤝 Contribuição

1. Fork o projeto
2. Crie uma branch para sua feature (`git checkout -b feature/AmazingFeature`)
3. Commit suas mudanças (`git commit -m 'Add some AmazingFeature'`)
4. Push para a branch (`git push origin feature/AmazingFeature`)
5. Abra um Pull Request

## 📄 Licença

Este projeto está sob a licença MIT. Veja o arquivo `LICENSE` para mais detalhes.