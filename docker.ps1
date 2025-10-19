# Script PowerShell para gerenciar Docker no Windows
# Substitui Makefile para usuários Windows sem Make

param(
    [Parameter(Position=0)]
    [string]$Command = "help"
)

function Show-Help {
    Write-Host "`n=== AGROPESCA JACARÉ - DOCKER MANAGER ===" -ForegroundColor Cyan
    Write-Host "`nUso: .\docker.ps1 <comando>`n"
    
    Write-Host "DESENVOLVIMENTO:" -ForegroundColor Yellow
    Write-Host "  dev-up           - Inicia ambiente de desenvolvimento"
    Write-Host "  dev-down         - Para ambiente de desenvolvimento"
    Write-Host "  dev-build        - Build do ambiente de desenvolvimento"
    Write-Host "  dev-rebuild      - Rebuild completo do dev"
    Write-Host "  dev-logs         - Mostra logs do ambiente de desenvolvimento"
    Write-Host "  dev-shell        - Acessa o shell do container dev"
    Write-Host "  dev-artisan      - Executa comando artisan (ex: .\docker.ps1 dev-artisan migrate)"
    Write-Host "  dev-npm          - Executa comando npm"
    Write-Host "  dev-composer     - Executa comando composer"
    Write-Host "  dev-test         - Roda testes no ambiente dev"
    
    Write-Host "`nPRODUÇÃO:" -ForegroundColor Yellow
    Write-Host "  prod-up          - Inicia ambiente de produção"
    Write-Host "  prod-down        - Para ambiente de produção"
    Write-Host "  prod-build       - Build do ambiente de produção"
    Write-Host "  prod-logs        - Mostra logs do ambiente de produção"
    
    Write-Host "`nUTILITÁRIOS:" -ForegroundColor Yellow
    Write-Host "  migrate          - Roda migrations no dev"
    Write-Host "  migrate-fresh    - Recria banco de dados no dev"
    Write-Host "  seed             - Roda seeders no dev"
    Write-Host "  tinker           - Abre tinker no dev"
    Write-Host "  optimize         - Otimiza caches no dev"
    Write-Host "  clear-cache      - Limpa todos os caches no dev"
    Write-Host "  ps               - Lista containers rodando"
    Write-Host "  clean            - Remove todos os containers, volumes e imagens"
    Write-Host "  prune            - Remove recursos Docker não utilizados"
    
    Write-Host "`nEXEMPLOS:" -ForegroundColor Green
    Write-Host "  .\docker.ps1 dev-up"
    Write-Host "  .\docker.ps1 dev-artisan migrate"
    Write-Host "  .\docker.ps1 dev-npm install"
    Write-Host "`n"
}

function Invoke-DockerCompose {
    param([string]$ComposeFile, [string[]]$Arguments)
    docker compose -f $ComposeFile @Arguments
}

switch ($Command) {
    "help" { Show-Help }
    
    # DESENVOLVIMENTO
    "dev-up" {
        Write-Host "Iniciando ambiente de desenvolvimento..." -ForegroundColor Green
        Invoke-DockerCompose -ComposeFile "docker-compose.dev.yml" -Arguments @("up", "-d")
        Write-Host "`nAplicação disponível em: http://localhost:8080" -ForegroundColor Cyan
        Write-Host "Vite HMR em: http://localhost:5173" -ForegroundColor Cyan
        Write-Host "pgAdmin em: http://localhost:5050" -ForegroundColor Cyan
        Write-Host "Mailpit em: http://localhost:8025`n" -ForegroundColor Cyan
    }
    
    "dev-down" {
        Write-Host "Parando ambiente de desenvolvimento..." -ForegroundColor Yellow
        Invoke-DockerCompose -ComposeFile "docker-compose.dev.yml" -Arguments @("down")
    }
    
    "dev-build" {
        Write-Host "Build do ambiente de desenvolvimento..." -ForegroundColor Green
        Invoke-DockerCompose -ComposeFile "docker-compose.dev.yml" -Arguments @("build", "--no-cache")
    }
    
    "dev-rebuild" {
        Write-Host "Rebuild completo do ambiente de desenvolvimento..." -ForegroundColor Yellow
        Invoke-DockerCompose -ComposeFile "docker-compose.dev.yml" -Arguments @("down", "-v")
        Invoke-DockerCompose -ComposeFile "docker-compose.dev.yml" -Arguments @("build", "--no-cache")
        Invoke-DockerCompose -ComposeFile "docker-compose.dev.yml" -Arguments @("up", "-d")
    }
    
    "dev-logs" {
        Write-Host "Mostrando logs..." -ForegroundColor Cyan
        Invoke-DockerCompose -ComposeFile "docker-compose.dev.yml" -Arguments @("logs", "-f")
    }
    
    "dev-shell" {
        Write-Host "Acessando shell do container..." -ForegroundColor Cyan
        Invoke-DockerCompose -ComposeFile "docker-compose.dev.yml" -Arguments @("exec", "app", "sh")
    }
    
    "dev-artisan" {
        $artisanArgs = $args
        if ($artisanArgs.Count -eq 0) {
            Write-Host "Uso: .\docker.ps1 dev-artisan <comando>" -ForegroundColor Red
            Write-Host "Exemplo: .\docker.ps1 dev-artisan migrate" -ForegroundColor Yellow
            exit 1
        }
        Write-Host "Executando: php artisan $artisanArgs" -ForegroundColor Cyan
        Invoke-DockerCompose -ComposeFile "docker-compose.dev.yml" -Arguments @("exec", "app", "php", "artisan") + $artisanArgs
    }
    
    "dev-npm" {
        $npmArgs = $args
        if ($npmArgs.Count -eq 0) {
            Write-Host "Uso: .\docker.ps1 dev-npm <comando>" -ForegroundColor Red
            exit 1
        }
        Write-Host "Executando: npm $npmArgs" -ForegroundColor Cyan
        Invoke-DockerCompose -ComposeFile "docker-compose.dev.yml" -Arguments @("exec", "app", "npm") + $npmArgs
    }
    
    "dev-composer" {
        $composerArgs = $args
        if ($composerArgs.Count -eq 0) {
            Write-Host "Uso: .\docker.ps1 dev-composer <comando>" -ForegroundColor Red
            exit 1
        }
        Write-Host "Executando: composer $composerArgs" -ForegroundColor Cyan
        Invoke-DockerCompose -ComposeFile "docker-compose.dev.yml" -Arguments @("exec", "app", "composer") + $composerArgs
    }
    
    "dev-test" {
        Write-Host "Executando testes..." -ForegroundColor Green
        Invoke-DockerCompose -ComposeFile "docker-compose.dev.yml" -Arguments @("exec", "app", "php", "artisan", "test")
    }
    
    # PRODUÇÃO
    "prod-up" {
        Write-Host "Iniciando ambiente de produção..." -ForegroundColor Green
        Invoke-DockerCompose -ComposeFile "docker-compose.prod.yml" -Arguments @("up", "-d")
    }
    
    "prod-down" {
        Write-Host "Parando ambiente de produção..." -ForegroundColor Yellow
        Invoke-DockerCompose -ComposeFile "docker-compose.prod.yml" -Arguments @("down")
    }
    
    "prod-build" {
        Write-Host "Build do ambiente de produção..." -ForegroundColor Green
        Invoke-DockerCompose -ComposeFile "docker-compose.prod.yml" -Arguments @("build", "--no-cache")
    }
    
    "prod-logs" {
        Write-Host "Mostrando logs..." -ForegroundColor Cyan
        Invoke-DockerCompose -ComposeFile "docker-compose.prod.yml" -Arguments @("logs", "-f")
    }
    
    # UTILITÁRIOS
    "migrate" {
        Write-Host "Executando migrations..." -ForegroundColor Green
        Invoke-DockerCompose -ComposeFile "docker-compose.dev.yml" -Arguments @("exec", "app", "php", "artisan", "migrate")
    }
    
    "migrate-fresh" {
        Write-Host "Recriando banco de dados..." -ForegroundColor Yellow
        Invoke-DockerCompose -ComposeFile "docker-compose.dev.yml" -Arguments @("exec", "app", "php", "artisan", "migrate:fresh", "--seed")
    }
    
    "seed" {
        Write-Host "Executando seeders..." -ForegroundColor Green
        Invoke-DockerCompose -ComposeFile "docker-compose.dev.yml" -Arguments @("exec", "app", "php", "artisan", "db:seed")
    }
    
    "tinker" {
        Write-Host "Abrindo Tinker..." -ForegroundColor Cyan
        Invoke-DockerCompose -ComposeFile "docker-compose.dev.yml" -Arguments @("exec", "app", "php", "artisan", "tinker")
    }
    
    "optimize" {
        Write-Host "Otimizando caches..." -ForegroundColor Green
        Invoke-DockerCompose -ComposeFile "docker-compose.dev.yml" -Arguments @("exec", "app", "php", "artisan", "optimize")
    }
    
    "clear-cache" {
        Write-Host "Limpando caches..." -ForegroundColor Yellow
        Invoke-DockerCompose -ComposeFile "docker-compose.dev.yml" -Arguments @("exec", "app", "php", "artisan", "optimize:clear")
    }
    
    "ps" {
        Write-Host "`nDEVELOPMENT:" -ForegroundColor Yellow
        Invoke-DockerCompose -ComposeFile "docker-compose.dev.yml" -Arguments @("ps")
        Write-Host "`nPRODUCTION:" -ForegroundColor Yellow
        Invoke-DockerCompose -ComposeFile "docker-compose.prod.yml" -Arguments @("ps")
    }
    
    "clean" {
        Write-Host "Removendo todos os containers, volumes e imagens..." -ForegroundColor Red
        $confirmation = Read-Host "Tem certeza? (S/N)"
        if ($confirmation -eq 'S' -or $confirmation -eq 's') {
            Invoke-DockerCompose -ComposeFile "docker-compose.dev.yml" -Arguments @("down", "-v", "--rmi", "all")
            Invoke-DockerCompose -ComposeFile "docker-compose.prod.yml" -Arguments @("down", "-v", "--rmi", "all")
            Write-Host "Limpeza concluída!" -ForegroundColor Green
        }
    }
    
    "prune" {
        Write-Host "Removendo recursos Docker não utilizados..." -ForegroundColor Yellow
        docker system prune -af --volumes
    }
    
    default {
        Write-Host "Comando não reconhecido: $Command" -ForegroundColor Red
        Show-Help
    }
}
