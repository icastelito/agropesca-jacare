# Agropesca Jacaré - Sistema de Gestão Rural

> Projeto desenvolvido como teste técnico demonstrando competências em desenvolvimento full-stack moderno.

## Descrição do Projeto

Sistema web completo para gerenciamento de produtores rurais, propriedades agrícolas, unidades de produção e rebanhos. Implementa funcionalidades CRUD completas, dashboard com estatísticas, geração de relatórios e exportação de dados.

### Objetivo do Teste Técnico

Demonstrar domínio em:
- Arquitetura full-stack com Laravel e Vue.js
- Design de API RESTful
- Modelagem de banco de dados relacional
- Containerização com Docker
- Testes automatizados
- Boas práticas de código e documentação técnica

## Stack Tecnológica

**Backend:**
- Laravel 10.49.1
- PHP 8.2
- PostgreSQL 15
- Laravel Sanctum (autenticação API)
- Laravel Inertia (SSR)

**Frontend:**
- Vue 3 (Composition API)
- TypeScript
- Inertia.js
- PrimeVue 3
- Vite 5

**DevOps:**
- Docker 24+
- Docker Compose
- Nginx 1.28
- Supervisor

**Bibliotecas:**
- Maatwebsite/Excel
- Barryvdh/DomPDF
- Faker (dados de teste)

## Requisitos do Sistema

- Docker 24+ e Docker Compose (instalação recomendada)
- PHP 8.2+ (instalação manual)
- PostgreSQL 15+ (instalação manual)
- Node.js 20+ (instalação manual)
- Composer 2+

## Instalação e Execução

### Método 1: Docker (Recomendado)

```bash
# 1. Clonar repositório
git clone <repository-url>
cd agropesca-jacare

# 2. Configurar ambiente
cp .env.docker .env

# 3. Iniciar containers
docker compose -f docker-compose.dev.yml up -d

# 4. Executar migrations e seeders
docker compose -f docker-compose.dev.yml exec app php artisan migrate --seed

# 5. Gerar chave da aplicação
docker compose -f docker-compose.dev.yml exec app php artisan key:generate
```

**Acessos:**
- Aplicação: http://localhost:8080
- Vite HMR: http://localhost:5173
- pgAdmin: http://localhost:5050 (admin@example.com / admin)

### Método 2: Instalação Manual

```bash
# 1. Instalar dependências
composer install
npm install

# 2. Configurar ambiente
cp .env.example .env
php artisan key:generate

# 3. Configurar banco de dados (.env)
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=agropesca_jacare
DB_USERNAME=postgres
DB_PASSWORD=postgres

# 4. Criar banco de dados
createdb agropesca_jacare

# 5. Executar migrations e seeders
php artisan migrate --seed

# 6. Build do frontend
npm run build

# 7. Iniciar servidores de desenvolvimento
php artisan serve          # Terminal 1
npm run dev                # Terminal 2
```

Acessar: http://localhost:8000

## Migrations e Seeds

### Executar Migrations

As migrations criam a estrutura do banco de dados (tabelas, índices, foreign keys).

**Com Docker:**
```bash
# Executar migrations
docker compose -f docker-compose.dev.yml exec app php artisan migrate

# Reverter última migration
docker compose -f docker-compose.dev.yml exec app php artisan migrate:rollback

# Resetar banco (apaga tudo e recria)
docker compose -f docker-compose.dev.yml exec app php artisan migrate:fresh

# Verificar status das migrations
docker compose -f docker-compose.dev.yml exec app php artisan migrate:status
```

**Sem Docker:**
```bash
# Executar migrations
php artisan migrate

# Reverter última migration
php artisan migrate:rollback

# Resetar banco (apaga tudo e recria)
php artisan migrate:fresh

# Verificar status das migrations
php artisan migrate:status
```

### Executar Seeds

Os seeders populam o banco com dados de teste para desenvolvimento.

**Com Docker:**
```bash
# Executar todos os seeders
docker compose -f docker-compose.dev.yml exec app php artisan db:seed

# Executar seeder específico
docker compose -f docker-compose.dev.yml exec app php artisan db:seed --class=ProdutorRuralSeeder
docker compose -f docker-compose.dev.yml exec app php artisan db:seed --class=PropriedadeSeeder
docker compose -f docker-compose.dev.yml exec app php artisan db:seed --class=UnidadeProducaoSeeder
docker compose -f docker-compose.dev.yml exec app php artisan db:seed --class=RebanhoSeeder

# Resetar banco e executar seeds (tudo de uma vez)
docker compose -f docker-compose.dev.yml exec app php artisan migrate:fresh --seed
```

**Sem Docker:**
```bash
# Executar todos os seeders
php artisan db:seed

# Executar seeder específico
php artisan db:seed --class=ProdutorRuralSeeder
php artisan db:seed --class=PropriedadeSeeder
php artisan db:seed --class=UnidadeProducaoSeeder
php artisan db:seed --class=RebanhoSeeder

# Resetar banco e executar seeds (tudo de uma vez)
php artisan migrate:fresh --seed
```

### Dados Gerados pelos Seeds

Os seeders criam dados realistas para teste:

- **200 produtores rurais**: CPF/CNPJ válidos, emails únicos, telefones formatados
- **383 propriedades**: Distribuídas por municípios brasileiros reais, áreas variadas
- **653 unidades de produção**: Culturas diversas (soja, milho, café, etc.)
- **412 rebanhos**: Espécies variadas (bovino, suíno, caprino, etc.)

**Ordem de execução:**
1. `ProdutorRuralSeeder` (cria produtores)
2. `PropriedadeSeeder` (cria propriedades vinculadas aos produtores)
3. `UnidadeProducaoSeeder` (cria unidades vinculadas às propriedades)
4. `RebanhoSeeder` (cria rebanhos vinculados às propriedades)

**Observação:** Use `migrate:fresh --seed` com cuidado em produção, pois apaga todos os dados!

## Funcionalidades Implementadas

### Módulos do Sistema

**1. Produtores Rurais**
- Cadastro completo com validação de CPF/CNPJ
- Gestão de contatos (telefone, email)
- Soft deletes para auditoria
- Busca e filtragem avançada

**2. Propriedades**
- Vinculação com produtor rural
- Localização (município, UF)
- Área total em hectares
- Inscrição estadual
- Relacionamento 1:N com unidades e rebanhos

**3. Unidades de Produção**
- Controle de culturas plantadas
- Área plantada por cultura
- Vinculação com propriedade

**4. Rebanhos**
- Gestão por espécie animal
- Controle de quantidade
- Vinculação com propriedade

**5. Documentos**
- Upload de arquivos (até 100MB)
- Relacionamento polimórfico (produtores e propriedades)
- Armazenamento em disco local
- Categorização de documentos

**6. Dashboard**
- Estatísticas gerais do sistema
- Gráficos de distribuição por município
- Análise de rebanhos por espécie
- Hectares por cultura

**7. Relatórios**
- Propriedades por município
- Animais por espécie
- Hectares por cultura
- Exportação em Excel e PDF

### Recursos Técnicos

- API RESTful completa (v1)
- Autenticação stateless (Sanctum) e stateful (Session)
- Interface responsiva (desktop/mobile)
- Tema claro e escuro
- Busca com suporte a acentuação (unaccent)
- Paginação em todas as listagens
- Validação de formulários (frontend e backend)
- Tratamento de erros padronizado
- Sistema de logs e auditoria

## Arquitetura do Projeto

### Estrutura de Diretórios

```
agropesca-jacare/
├── app/
│   ├── Http/
│   │   ├── Controllers/           # Controllers Web e API
│   │   │   ├── Api/V1/            # API RESTful v1
│   │   │   └── Auth/              # Autenticação
│   │   ├── Requests/              # Validação de formulários
│   │   ├── Resources/             # Transformadores de dados API
│   │   └── Middleware/            # Middlewares customizados
│   ├── Models/                    # Eloquent Models
│   │   └── Traits/                # Traits (HasUuid, etc)
│   ├── Exports/                   # Classes de exportação Excel
│   └── Helpers/                   # Funções auxiliares
├── database/
│   ├── migrations/                # Migrations do banco
│   ├── seeders/                   # Seeders (dados de teste)
│   └── factories/                 # Factories (Faker)
├── resources/
│   ├── js/
│   │   ├── Pages/                 # Páginas Inertia.js
│   │   ├── Components/            # Componentes Vue
│   │   └── Layouts/               # Layouts da aplicação
│   ├── css/                       # Estilos globais
│   └── views/                     # Blade templates
├── routes/
│   ├── web.php                    # Rotas Web (Inertia)
│   └── api.php                    # Rotas API RESTful
├── tests/
│   ├── Feature/                   # Testes de integração
│   └── Unit/                      # Testes unitários
├── docker/                        # Configurações Docker
│   ├── nginx/                     # Nginx configs
│   ├── php/                       # PHP-FPM configs
│   └── supervisor/                # Supervisor configs
└── docs/                          # Documentação técnica
```

### Padrões Implementados

**Backend:**
- MVC (Model-View-Controller)
- Repository Pattern (implícito via Eloquent)
- Service Layer (para lógica de negócio complexa)
- Form Request Validation
- API Resources (transformação de dados)
- Eloquent ORM (com relacionamentos)

**Frontend:**
- Component-based architecture (Vue SFC)
- Composition API (Vue 3)
- Layouts compartilhados (Inertia)
- Props drilling minimizado
- Reactive state management

**Database:**
- UUID como chave primária
- Soft deletes para auditoria
- Timestamps automáticos
- Índices otimizados
- Foreign keys com constraints

## Banco de Dados

### Modelo de Dados

**Entidades Principais:**
- `users` - Usuários do sistema (UUID)
- `produtores_rurais` - Produtores cadastrados (UUID)
- `propriedades` - Propriedades rurais (UUID)
- `unidades_producao` - Unidades de produção (UUID)
- `rebanhos` - Rebanhos (UUID)
- `documentos` - Arquivos anexados (UUID, polimórfico)

### Relacionamentos

```
produtores_rurais (1) ──< (N) propriedades
propriedades (1) ──< (N) unidades_producao
propriedades (1) ──< (N) rebanhos
propriedades (1) ──< (N) documentos (polimórfico)
produtores_rurais (1) ──< (N) documentos (polimórfico)
```

### Extensões PostgreSQL

- `uuid-ossp`: Geração de UUIDs
- `unaccent`: Busca sem acentuação

## API RESTful

### Autenticação

**Endpoints:**
```
POST   /api/v1/login       - Autenticar e receber token
POST   /api/v1/logout      - Revogar token
POST   /api/v1/register    - Registrar novo usuário
GET    /api/v1/me          - Dados do usuário autenticado
```

### Recursos Disponíveis

**Produtores Rurais:**
```
GET    /api/v1/produtores-rurais           - Listar (paginado)
POST   /api/v1/produtores-rurais           - Criar
GET    /api/v1/produtores-rurais/{id}      - Visualizar
PUT    /api/v1/produtores-rurais/{id}      - Atualizar
DELETE /api/v1/produtores-rurais/{id}      - Deletar
```

**Propriedades:**
```
GET    /api/v1/propriedades                - Listar
POST   /api/v1/propriedades                - Criar
GET    /api/v1/propriedades/{id}           - Visualizar
PUT    /api/v1/propriedades/{id}           - Atualizar
DELETE /api/v1/propriedades/{id}           - Deletar
```

**Unidades de Produção e Rebanhos:**
- Seguem mesma estrutura RESTful

**Dashboard:**
```
GET    /api/v1/dashboard                   - Estatísticas gerais
```

**Relatórios:**
```
GET    /api/v1/relatorios/propriedades-por-municipio
GET    /api/v1/relatorios/animais-por-especie
GET    /api/v1/relatorios/hectares-por-cultura
```

**Exportações:**
```
GET    /api/v1/exportar/propriedades/excel
GET    /api/v1/exportar/rebanhos/pdf/{produtor_id}
GET    /api/v1/exportar/produtores/excel
GET    /api/v1/exportar/consolidado/excel
```

**Documentos:**
```
POST   /api/v1/documentos/{tipo}/{id}                - Upload
GET    /api/v1/documentos/{documento_id}/download    - Download
DELETE /api/v1/documentos/{documento_id}             - Deletar
```

### Filtros de Busca

Suportados em listagens:
- `search`: Busca textual
- `sort_by`: Campo para ordenação
- `sort_order`: Direção (asc, desc)
- `per_page`: Itens por página

### Rate Limiting

- API pública: 60 requests/minuto
- API autenticada: 120 requests/minuto

### Responses Padronizadas

**Sucesso:**
```json
{
  "success": true,
  "data": {...},
  "message": "Operação realizada com sucesso"
}
```

**Erro:**
```json
{
  "success": false,
  "message": "Mensagem de erro",
  "errors": {...}
}
```

## Testes Automatizados

### Executar Testes

```bash
# Todos os testes
php artisan test

# Testes de integração
php artisan test --testsuite=Feature

# Testes unitários
php artisan test --testsuite=Unit

# Teste específico
php artisan test --filter=AuthApiTest

# Com coverage
php artisan test --coverage
```

### Suites de Testes Implementadas

**Feature Tests (Integração):**
- `AuthApiTest`: Login, logout, registro, me
- `ProdutorRuralApiTest`: CRUD completo de produtores
- `PropriedadeApiTest`: CRUD completo de propriedades
- `DashboardApiTest`: Endpoints de estatísticas

**Unit Tests:**
- Validações de models
- Helper functions
- Transformadores de dados


### Ambiente de Testes

- SQLite em memória (rápido e isolado)
- Migrations automáticas
- Factories com dados realistas
- RefreshDatabase trait

## Comandos Úteis

### Docker

```bash
# Iniciar ambiente
docker compose -f docker-compose.dev.yml up -d

# Parar ambiente
docker compose -f docker-compose.dev.yml down

# Ver logs
docker compose -f docker-compose.dev.yml logs -f app

# Executar comandos Artisan
docker compose -f docker-compose.dev.yml exec app php artisan <command>

# Acessar shell do container
docker compose -f docker-compose.dev.yml exec app sh
```

### Artisan

```bash
# Migrations
php artisan migrate
php artisan migrate:fresh --seed
php artisan migrate:rollback

# Cache (produção)
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Limpar cache (desenvolvimento)
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Testes
php artisan test
php artisan test --testsuite=Feature
```

### NPM

```bash
# Desenvolvimento (com HMR)
npm run dev

# Build para produção
npm run build

# Análise do bundle
npm run build -- --mode=analyze
```

### Acessos (Docker)

| Serviço       | URL                      | Credenciais             |
|---------------|--------------------------|-------------------------|
| Aplicação     | http://localhost:8080    | -                       |
| Vite HMR      | http://localhost:5173    | -                       |
| pgAdmin       | http://localhost:5050    | admin@example.com/admin |
| PostgreSQL    | localhost:5432           | postgres/postgres       |

## Deploy em Produção

### Docker

```bash
# 1. Build da imagem
docker build -t agropesca-jacare:latest .

# 2. Executar container
docker run -d \
  -p 8080:8080 \
  -e APP_ENV=production \
  -e APP_DEBUG=false \
  -e DB_CONNECTION=pgsql \
  -e DB_HOST=<db-host> \
  -e DB_DATABASE=<db-name> \
  -e DB_USERNAME=<db-user> \
  -e DB_PASSWORD=<db-password> \
  agropesca-jacare:latest
```

### Variáveis de Ambiente

**Obrigatórias:**
- `APP_KEY`: Gerado via `php artisan key:generate`
- `DB_CONNECTION`, `DB_HOST`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`

**Recomendadas:**
- `APP_ENV=production`
- `APP_DEBUG=false`
- `LOG_CHANNEL=stack`
- `SESSION_DRIVER=database`
- `CACHE_DRIVER=redis` (se disponível)

### Otimizações

```bash
# Cache de configuração
php artisan config:cache

# Cache de rotas
php artisan route:cache

# Cache de views
php artisan view:cache

# Otimizar autoload
composer install --optimize-autoloader --no-dev
```## Decisões Técnicas

### UUID como Primary Key

**Vantagens:**
- Segurança (não expõe quantidade de registros)
- Distribuição (evita conflitos em sistemas distribuídos)
- Integrações externas facilitadas

**Implementação:**
- Trait `HasUuid` em todos os models
- Extensão `uuid-ossp` do PostgreSQL
- Migrations configuradas com `$table->uuid('id')->primary()`

### Inertia.js

**Justificativa:**
- Desenvolver SPA sem API separada
- Simplicidade do MVC tradicional
- SSR nativo para SEO
- Menos boilerplate que REST

### PrimeVue

**Justificativa:**
- Biblioteca madura e completa
- Temas customizáveis
- Acessibilidade integrada
- Documentação abrangente

### Docker Multi-stage

**Benefícios:**
- Imagens otimizadas (menor tamanho)
- Separação de build e runtime
- Cache de layers eficiente
- Reprodutibilidade

## Aspectos de Segurança

### Implementados

- **Autenticação**: Bcrypt para senhas, tokens Sanctum
- **CSRF Protection**: Tokens em formulários web
- **XSS Protection**: Escape automático de HTML
- **SQL Injection**: Eloquent ORM + Prepared Statements
- **Mass Assignment**: Fillable/Guarded em Models
- **Rate Limiting**: Throttle middleware configurado
- **CORS**: Configuração apropriada para API
- **Headers de Segurança**: X-Frame-Options, X-Content-Type-Options

### Validações

- CPF/CNPJ: Formato e dígitos verificadores
- Email: Único e normalizado (lowercase)
- Foreign Keys: Validação de existência
- File Upload: Tipo MIME e tamanho (100MB)

## Performance e Otimização

### Backend

- Eager loading de relacionamentos
- Paginação em todas as listagens (15 itens/página)
- Índices de banco de dados estratégicos
- Query optimization (N+1 prevention)
- Cache de configurações (produção)

### Frontend

- Lazy loading de componentes Vue
- Code splitting (Vite)
- Tree shaking automático
- Minificação de assets
- Gzip compression (Nginx)

### Database

- Índices em foreign keys
- Índices em campos de busca (email, cpf_cnpj, municipio)
- UUID v4 otimizado
- Soft deletes para auditoria sem perda de referências

## Documentação Adicional

### Arquivos Técnicos

- `DOCUMENTACAO-TECNICA.md`: Documentação completa da arquitetura
- `DOCKER-README.md`: Guia detalhado do ambiente Docker
- `DEPLOY-CHECKLIST.md`: Checklist de deploy em produção
- `docs/`: Documentação específica de módulos

### Testes de API

- Coleção Postman disponível em `postman/Agropesca-Jacare-API.postman_collection.json`
- Endpoints documentados com exemplos de request/response

## Conhecimentos Demonstrados

### Backend
- Laravel avançado (Eloquent ORM, Policies, Events)
- Design de API RESTful
- Autenticação stateless (Sanctum) e stateful (Session)
- Form Request Validation
- API Resources (transformação de dados)
- Migrations e seeders profissionais

### Frontend
- Vue 3 moderno (Composition API)
- TypeScript para type safety
- Inertia.js para SSR-like routing
- PrimeVue (biblioteca UI completa)
- Vite (build tool otimizado)

### Database
- Modelagem relacional avançada
- Relacionamentos complexos (1:N, polimórfico)
- Índices estratégicos
- Extensões PostgreSQL (uuid-ossp, unaccent)

### DevOps
- Docker multi-stage builds
- Docker Compose para orquestração
- Nginx como reverse proxy
- Supervisor para processos
- Configurações de produção

### Qualidade
- Testes automatizados (Feature e Unit)
- Validações em múltiplas camadas
- Tratamento de erros padronizado
- Logs estruturados
- Clean code e documentação

---

**Projeto desenvolvido como teste técnico - 2025**

**Autor**: [Seu Nome]  
**Contato**: [Seu Email]  
**GitHub**: [Seu GitHub]