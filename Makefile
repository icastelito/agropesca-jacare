# Makefile para facilitar comandos Docker
# Use: make <comando>

.PHONY: help dev-up dev-down dev-build dev-logs prod-up prod-down prod-build prod-logs clean

help: ## Mostra esta ajuda
	@echo "Comandos disponíveis:"
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-20s\033[0m %s\n", $$1, $$2}'

# === DESENVOLVIMENTO ===

dev-up: ## Inicia ambiente de desenvolvimento
	docker compose -f docker-compose.dev.yml up -d

dev-down: ## Para ambiente de desenvolvimento
	docker compose -f docker-compose.dev.yml down

dev-build: ## Build do ambiente de desenvolvimento
	docker compose -f docker-compose.dev.yml build --no-cache

dev-rebuild: ## Rebuild completo do dev
	docker compose -f docker-compose.dev.yml down -v
	docker compose -f docker-compose.dev.yml build --no-cache
	docker compose -f docker-compose.dev.yml up -d

dev-logs: ## Mostra logs do ambiente de desenvolvimento
	docker compose -f docker-compose.dev.yml logs -f

dev-shell: ## Acessa o shell do container dev
	docker compose -f docker-compose.dev.yml exec app sh

dev-artisan: ## Executa comando artisan no dev (ex: make dev-artisan cmd="migrate")
	docker compose -f docker-compose.dev.yml exec app php artisan $(cmd)

dev-npm: ## Executa comando npm no dev (ex: make dev-npm cmd="install")
	docker compose -f docker-compose.dev.yml exec app npm $(cmd)

dev-composer: ## Executa comando composer no dev (ex: make dev-composer cmd="require pacote")
	docker compose -f docker-compose.dev.yml exec app composer $(cmd)

dev-test: ## Roda testes no ambiente dev
	docker compose -f docker-compose.dev.yml exec app php artisan test

# === PRODUÇÃO ===

prod-up: ## Inicia ambiente de produção
	docker compose -f docker-compose.prod.yml up -d

prod-down: ## Para ambiente de produção
	docker compose -f docker-compose.prod.yml down

prod-build: ## Build do ambiente de produção
	docker compose -f docker-compose.prod.yml build --no-cache

prod-logs: ## Mostra logs do ambiente de produção
	docker compose -f docker-compose.prod.yml logs -f

prod-shell: ## Acessa o shell do container prod
	docker compose -f docker-compose.prod.yml exec app sh

prod-artisan: ## Executa comando artisan no prod (ex: make prod-artisan cmd="migrate")
	docker compose -f docker-compose.prod.yml exec app php artisan $(cmd)

# === LIMPEZA ===

clean: ## Remove todos os containers, volumes e imagens
	docker compose -f docker-compose.dev.yml down -v --rmi all
	docker compose -f docker-compose.prod.yml down -v --rmi all

clean-logs: ## Limpa logs do Laravel
	rm -rf storage/logs/*.log

prune: ## Remove recursos Docker não utilizados
	docker system prune -af --volumes

# === UTILITÁRIOS ===

ps: ## Lista containers rodando
	docker compose -f docker-compose.dev.yml ps
	docker compose -f docker-compose.prod.yml ps

migrate: ## Roda migrations no dev
	docker compose -f docker-compose.dev.yml exec app php artisan migrate

migrate-fresh: ## Recria banco de dados no dev
	docker compose -f docker-compose.dev.yml exec app php artisan migrate:fresh --seed

seed: ## Roda seeders no dev
	docker compose -f docker-compose.dev.yml exec app php artisan db:seed

tinker: ## Abre tinker no dev
	docker compose -f docker-compose.dev.yml exec app php artisan tinker

optimize: ## Otimiza caches no dev
	docker compose -f docker-compose.dev.yml exec app php artisan optimize

clear-cache: ## Limpa todos os caches no dev
	docker compose -f docker-compose.dev.yml exec app php artisan optimize:clear

backup-db: ## Backup do banco de dados dev
	docker compose -f docker-compose.dev.yml exec postgres pg_dump -U postgres agropesca_jacare > backup_$(shell date +%Y%m%d_%H%M%S).sql

restore-db: ## Restaura banco de dados dev (ex: make restore-db file=backup.sql)
	docker compose -f docker-compose.dev.yml exec -T postgres psql -U postgres agropesca_jacare < $(file)
