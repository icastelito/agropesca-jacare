# Script para habilitar extensões PostgreSQL no PHP
# Execute como Administrador

$phpIniPath = "C:\ambience variables\php\php.ini"

Write-Host "🔧 Habilitando extensões PostgreSQL no PHP..." -ForegroundColor Cyan
Write-Host ""

if (-not (Test-Path $phpIniPath)) {
    Write-Host "❌ Arquivo php.ini não encontrado em: $phpIniPath" -ForegroundColor Red
    Write-Host "Execute 'php --ini' para localizar o arquivo correto" -ForegroundColor Yellow
    exit 1
}

# Fazer backup
$backupPath = "$phpIniPath.backup_$(Get-Date -Format 'yyyyMMdd_HHmmss')"
Copy-Item $phpIniPath $backupPath
Write-Host "✅ Backup criado: $backupPath" -ForegroundColor Green

# Ler conteúdo
$content = Get-Content $phpIniPath -Raw

# Habilitar extensões
$modified = $false

if ($content -match ";extension=pdo_pgsql") {
    $content = $content -replace ";extension=pdo_pgsql", "extension=pdo_pgsql"
    $modified = $true
    Write-Host "✅ Extensão pdo_pgsql habilitada" -ForegroundColor Green
} elseif ($content -match "extension=pdo_pgsql") {
    Write-Host "ℹ️  Extensão pdo_pgsql já estava habilitada" -ForegroundColor Yellow
} else {
    # Adicionar se não existir
    $content += "`nextension=pdo_pgsql`n"
    $modified = $true
    Write-Host "✅ Extensão pdo_pgsql adicionada" -ForegroundColor Green
}

if ($content -match ";extension=pgsql") {
    $content = $content -replace ";extension=pgsql", "extension=pgsql"
    $modified = $true
    Write-Host "✅ Extensão pgsql habilitada" -ForegroundColor Green
} elseif ($content -match "extension=pgsql") {
    Write-Host "ℹ️  Extensão pgsql já estava habilitada" -ForegroundColor Yellow
} else {
    # Adicionar se não existir
    $content += "extension=pgsql`n"
    $modified = $true
    Write-Host "✅ Extensão pgsql adicionada" -ForegroundColor Green
}

# Salvar se houve modificações
if ($modified) {
    Set-Content $phpIniPath $content -NoNewline
    Write-Host ""
    Write-Host "✅ Arquivo php.ini atualizado com sucesso!" -ForegroundColor Green
} else {
    Write-Host ""
    Write-Host "ℹ️  Nenhuma modificação necessária" -ForegroundColor Yellow
}

Write-Host ""
Write-Host "🔍 Verificando instalação..." -ForegroundColor Cyan

# Verificar módulos
$modules = php -m | Select-String -Pattern "pgsql"

if ($modules) {
    Write-Host ""
    Write-Host "✅ Extensões PostgreSQL instaladas:" -ForegroundColor Green
    $modules | ForEach-Object { Write-Host "   - $_" -ForegroundColor White }
    
    Write-Host ""
    Write-Host "🎉 Configuração concluída com sucesso!" -ForegroundColor Green
    Write-Host ""
    Write-Host "Próximos passos:" -ForegroundColor Cyan
    Write-Host "  1. Feche e reabra o terminal" -ForegroundColor White
    Write-Host "  2. Execute: php artisan migrate:status" -ForegroundColor White
    Write-Host "  3. Execute: php artisan migrate" -ForegroundColor White
} else {
    Write-Host ""
    Write-Host "⚠️  As extensões foram habilitadas, mas não foram detectadas" -ForegroundColor Yellow
    Write-Host ""
    Write-Host "Possíveis causas:" -ForegroundColor Cyan
    Write-Host "  1. As DLLs das extensões não existem no diretório ext/ do PHP" -ForegroundColor White
    Write-Host "  2. Caminho incorreto no php.ini (extension_dir)" -ForegroundColor White
    Write-Host "  3. PHP precisa ser reinstalado com suporte PostgreSQL" -ForegroundColor White
    Write-Host ""
    Write-Host "Soluções:" -ForegroundColor Cyan
    Write-Host "  - Instalar PostgreSQL Client Libraries" -ForegroundColor White
    Write-Host "  - Ou usar Laravel Sail (Docker): composer require laravel/sail --dev" -ForegroundColor White
}

Write-Host ""
Write-Host "📝 Backup salvo em: $backupPath" -ForegroundColor Gray
