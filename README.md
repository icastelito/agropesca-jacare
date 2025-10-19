# Agropesca Jacare

Sistema de gestão para produtores rurais, propriedades, unidades de produção e rebanhos.

## Stack Tecnológica

- Backend: Laravel 10 + PHP 8.2
- Frontend: Vue 3 + Inertia.js + TypeScript
- UI: PrimeVue 3
- Database: PostgreSQL 15
- Build: Vite 5
- Container: Docker + Docker Compose

## Requisitos

- Docker e Docker Compose (recomendado)
- PHP 8.2+ (instalação manual)
- PostgreSQL 15+ (instalação manual)
- Node.js 20+ (instalação manual)
- Composer 2+

## Instalação

### Docker (Recomendado)

```bash
# Clonar repositório
git clone <repository-url>
cd agropesca-jacare

# Copiar arquivo de ambiente
cp .env.docker .env

# Iniciar containers
docker compose -f docker-compose.dev.yml up -d

# Executar migrations
docker compose -f docker-compose.dev.yml exec app php artisan migrate --seed

# Gerar chave da aplicação
docker compose -f docker-compose.dev.yml exec app php artisan key:generate
```

Acessar: http://localhost:8080

### Instalação Manual

```bash
# Instalar dependências
composer install
npm install

# Configurar ambiente
cp .env.example .env
php artisan key:generate

# Configurar banco de dados no .env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=agropesca_jacare
DB_USERNAME=postgres
DB_PASSWORD=postgres

# Executar migrations
php artisan migrate --seed

# Build do frontend
npm run build

# Iniciar servidor de desenvolvimento
php artisan serve
npm run dev
```

Acessar: http://localhost:8000

## Funcionalidades

### Módulos Principais

- Produtores Rurais: Cadastro completo com CPF/CNPJ, contatos e endereço
- Propriedades: Gestão de fazendas com localização e área total (hectares)
- Unidades de Produção: Controle de culturas e áreas plantadas
- Rebanhos: Gerenciamento de animais por espécie e quantidade
- Documentos: Upload e gestão de arquivos anexos (PDF, imagens)
- Dashboard: Visualizações gráficas e estatísticas
- Relatórios: Exportação em Excel e PDF

### Recursos Técnicos

- Busca avançada com filtros em tempo real
- Interface responsiva (desktop e mobile)
- Autenticação com Laravel Sanctum
- API RESTful completa
- Testes unitários e de integração
- Sistema de logs e auditoria
- Otimização de performance com cache
- Suporte a múltiplos temas (claro/escuro)

## Estrutura do Projeto

```
agropesca-jacare/
├── app/                    # Backend Laravel
│   ├── Http/
│   │   ├── Controllers/    # Controllers Web e API
│   │   ├── Requests/       # Form Requests
│   │   └── Resources/      # API Resources
│   ├── Models/             # Eloquent Models
│   ├── Helpers/            # Classes auxiliares
│   └── Exports/            # Classes de exportação
├── resources/
│   ├── js/                 # Frontend Vue.js
│   │   ├── Pages/          # Páginas Inertia.js
│   │   ├── Layouts/        # Layouts
│   │   └── Components/     # Componentes Vue
│   └── views/              # Blade templates
├── database/
│   ├── migrations/         # Database migrations
│   └── seeders/            # Database seeders
├── docker/                 # Configurações Docker
│   ├── nginx/              # Nginx configs
│   ├── php/                # PHP-FPM configs
│   ├── supervisor/         # Supervisor configs
│   └── scripts/            # Scripts de inicialização
├── routes/                 # Rotas (web, api)
├── tests/                  # Testes automatizados
└── docs/                   # Documentação adicional
```

## Comandos Úteis

### Docker

```bash
# Iniciar ambiente de desenvolvimento
docker compose -f docker-compose.dev.yml up -d

# Ver logs
docker compose -f docker-compose.dev.yml logs -f app

# Executar comandos Artisan
docker compose -f docker-compose.dev.yml exec app php artisan <command>

# Acessar shell do container
docker compose -f docker-compose.dev.yml exec app sh

# Parar containers
docker compose -f docker-compose.dev.yml down
```

### Artisan

```bash
# Migrations
php artisan migrate
php artisan migrate:fresh --seed

# Seeds (dados de teste)
php artisan db:seed                    # Executar todos os seeders
php artisan db:seed --class=ProdutorRuralSeeder
php artisan db:seed --class=PropriedadeSeeder
php artisan db:seed --class=UnidadeProducaoSeeder
php artisan db:seed --class=RebanhoSeeder

# Cache
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan cache:clear

# Testes
php artisan test                       # Todos os testes
php artisan test --testsuite=Feature   # Testes de integração
php artisan test --testsuite=Unit      # Testes unitários
php artisan test --filter=AuthApiTest  # Teste específico
```

### NPM

```bash
# Desenvolvimento (com HMR)
npm run dev

# Build para produção
npm run build

# Testes
npm run test
```

## Acessos Desenvolvimento

| Serviço       | URL                      | Credenciais           |
|---------------|--------------------------|----------------------|
| Aplicação     | http://localhost:8080    | -                    |
| Vite HMR      | http://localhost:5173    | -                    |
| pgAdmin       | http://localhost:5050    | admin@example.com / admin |
| PostgreSQL    | localhost:5432           | postgres / postgres  |

## Deploy

### Produção Docker

```bash
# Build da imagem Docker
docker build -t agropesca-jacare:latest .

# Executar container
docker run -d \
  -p 8080:8080 \
  -e APP_ENV=production \
  -e DB_CONNECTION=pgsql \
  -e DB_HOST=<db-host> \
  -e DB_DATABASE=<db-name> \
  -e DB_USERNAME=<db-user> \
  -e DB_PASSWORD=<db-password> \
  agropesca-jacare:latest
```

## Testes

### Testes PHP (PHPUnit)

```bash
# Executar todos os testes
php artisan test

# Testes por tipo
php artisan test --testsuite=Feature   # API e integração
php artisan test --testsuite=Unit      # Unitários

# Testes específicos
php artisan test --filter=AuthApiTest
php artisan test --filter=ProdutorRuralApiTest
php artisan test --filter=PropriedadeApiTest
php artisan test --filter=DashboardApiTest

# Com coverage
php artisan test --coverage
```

### Testes Disponíveis

**Feature (API):**
- `AuthApiTest`: Autenticação (login, logout, registro)
- `ProdutorRuralApiTest`: CRUD de produtores rurais
- `PropriedadeApiTest`: CRUD de propriedades
- `DashboardApiTest`: Endpoints de estatísticas

**Unit:**
- Validações de models e helpers

## Seeds

O sistema inclui seeders para popular o banco com dados de teste:

```bash
# Executar todos (ordem: ProdutorRural > Propriedade > UnidadeProducao > Rebanho)
php artisan db:seed

# Executar seeder específico
php artisan db:seed --class=ProdutorRuralSeeder
php artisan db:seed --class=PropriedadeSeeder
php artisan db:seed --class=UnidadeProducaoSeeder
php artisan db:seed --class=RebanhoSeeder

# Resetar e popular novamente
php artisan migrate:fresh --seed
```

**Dados gerados:**
- 50 produtores rurais com CPF/CNPJ válidos
- 100 propriedades distribuídas por municípios
- 200 unidades de produção com culturas variadas
- 150 rebanhos com diferentes espécies

## Exemplos de Relatórios

### Dashboard - Estatísticas Gerais

- Total de produtores rurais cadastrados
- Total de propriedades registradas
- Área total em hectares
- Distribuição de propriedades por município
- Distribuição de animais por espécie
- Distribuição de hectares por cultura

### Exportação Excel - Propriedades

Colunas exportadas:
- Produtor Rural
- Nome da Propriedade
- Município
- Estado
- Área Total (ha)
- Unidades de Produção
- Data de Cadastro

### Exportação PDF - Rebanhos

Informações incluídas:
- Dados do produtor
- Lista de rebanhos por espécie
- Quantidade de animais
- Propriedade vinculada
- Observações

## Observações Gerais

### Segurança

- Todas as senhas são criptografadas com bcrypt
- Proteção CSRF habilitada
- Headers de segurança configurados
- Validação de entrada em todos os formulários
- Sanitização de dados antes de salvar no banco

### Performance

- Cache de rotas, configurações e views em produção
- Lazy loading de componentes Vue
- Paginação em todas as listagens
- Índices de banco de dados otimizados
- Compressão Gzip habilitada

### Banco de Dados

- Usa UUID como chave primária em todas as tabelas
- Soft deletes habilitado nos modelos principais
- Timestamps automáticos (created_at, updated_at)
- Relacionamentos otimizados com eager loading
- Suporte a busca com unaccent (PostgreSQL)

### API

- Versionamento (v1)
- Autenticação via Sanctum (token-based)
- Responses padronizadas (JSON)
- Validação de requisições
- Rate limiting configurado
- Documentação Postman disponível


## Licença

Uso exclusivo da Agropesca Jacaré.

## Suporte

Para problemas técnicos:
1. Verificar documentação em /docs
2. Consultar logs da aplicação
3. Abrir issue no repositório
