# Script para corrigir permissões do storage no container Docker
# Uso: .\fix-storage-permissions.ps1

Write-Host "🔧 Corrigindo permissões do storage..." -ForegroundColor Cyan

# Verificar se o Docker está rodando
try {
    $dockerRunning = docker ps 2>&1
    if ($LASTEXITCODE -ne 0) {
        Write-Host "❌ Docker não está rodando ou não está acessível" -ForegroundColor Red
        exit 1
    }
} catch {
    Write-Host "❌ Erro ao verificar Docker: $_" -ForegroundColor Red
    exit 1
}

# Determinar qual docker-compose usar
$composeFile = "docker-compose.dev.yml"
if (Test-Path "docker-compose.yml") {
    Write-Host "Qual ambiente você quer corrigir?" -ForegroundColor Yellow
    Write-Host "1) Desenvolvimento (docker-compose.dev.yml)" -ForegroundColor White
    Write-Host "2) Produção (docker-compose.prod.yml)" -ForegroundColor White
    Write-Host "3) Padrão (docker-compose.yml)" -ForegroundColor White
    
    $choice = Read-Host "Escolha uma opção (1-3)"
    
    switch ($choice) {
        "1" { $composeFile = "docker-compose.dev.yml" }
        "2" { $composeFile = "docker-compose.prod.yml" }
        "3" { $composeFile = "docker-compose.yml" }
        default { 
            Write-Host "Opção inválida. Usando desenvolvimento." -ForegroundColor Yellow
            $composeFile = "docker-compose.dev.yml"
        }
    }
}

Write-Host "📦 Usando: $composeFile" -ForegroundColor Green

# Verificar se o container está rodando
$containerName = docker compose -f $composeFile ps -q app 2>&1
if ($LASTEXITCODE -ne 0 -or [string]::IsNullOrEmpty($containerName)) {
    Write-Host "⚠️  Container não está rodando. Iniciando..." -ForegroundColor Yellow
    docker compose -f $composeFile up -d
    Start-Sleep -Seconds 5
}

Write-Host "📁 Criando diretórios necessários..." -ForegroundColor Cyan
docker compose -f $composeFile exec app mkdir -p /var/www/html/storage/logs/queries
docker compose -f $composeFile exec app mkdir -p /var/www/html/storage/framework/cache/data
docker compose -f $composeFile exec app mkdir -p /var/www/html/storage/framework/sessions
docker compose -f $composeFile exec app mkdir -p /var/www/html/storage/framework/views
docker compose -f $composeFile exec app mkdir -p /var/www/html/bootstrap/cache

Write-Host "🔐 Ajustando permissões..." -ForegroundColor Cyan
docker compose -f $composeFile exec app chown -R www-data:www-data /var/www/html/storage
docker compose -f $composeFile exec app chown -R www-data:www-data /var/www/html/bootstrap/cache
docker compose -f $composeFile exec app chmod -R 775 /var/www/html/storage
docker compose -f $composeFile exec app chmod -R 775 /var/www/html/bootstrap/cache

Write-Host "✅ Permissões corrigidas com sucesso!" -ForegroundColor Green

# Verificar
Write-Host "`n📊 Verificando diretórios criados:" -ForegroundColor Cyan
docker compose -f $composeFile exec app ls -la /var/www/html/storage/logs/

Write-Host "`n🧪 Testando criação de arquivo de log..." -ForegroundColor Cyan
$testResult = docker compose -f $composeFile exec app sh -c "touch /var/www/html/storage/logs/queries/test-$(Get-Date -Format 'yyyy-MM-dd').log && echo 'OK' || echo 'FAIL'" 2>&1

if ($testResult -like "*OK*") {
    Write-Host "✅ Teste de escrita: SUCESSO" -ForegroundColor Green
    docker compose -f $composeFile exec app rm -f /var/www/html/storage/logs/queries/test-*.log
} else {
    Write-Host "❌ Teste de escrita: FALHOU" -ForegroundColor Red
    Write-Host "Detalhes: $testResult" -ForegroundColor Yellow
}

Write-Host "`n🎉 Processo concluído!" -ForegroundColor Green
Write-Host "💡 Dica: Se o problema persistir, considere reconstruir o container:" -ForegroundColor Cyan
Write-Host "   docker compose -f $composeFile down" -ForegroundColor White
Write-Host "   docker compose -f $composeFile build --no-cache" -ForegroundColor White
Write-Host "   docker compose -f $composeFile up -d" -ForegroundColor White
