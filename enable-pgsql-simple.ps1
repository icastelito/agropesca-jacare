# Script simples para habilitar PostgreSQL no PHP
param(
    [string]$phpIniPath = "C:\ambience variables\php\php.ini"
)

Write-Host "Habilitando extensoes PostgreSQL..." -ForegroundColor Cyan

if (-not (Test-Path $phpIniPath)) {
    Write-Host "Erro: php.ini nao encontrado em $phpIniPath" -ForegroundColor Red
    exit 1
}

# Backup
$backup = "$phpIniPath.backup"
Copy-Item $phpIniPath $backup -Force
Write-Host "Backup criado: $backup" -ForegroundColor Green

# Ler e modificar
$content = Get-Content $phpIniPath

$newContent = $content | ForEach-Object {
    if ($_ -match "^;extension=pdo_pgsql") {
        $_ -replace "^;extension=pdo_pgsql", "extension=pdo_pgsql"
        Write-Host "Habilitado: pdo_pgsql" -ForegroundColor Green
    }
    elseif ($_ -match "^;extension=pgsql") {
        $_ -replace "^;extension=pgsql", "extension=pgsql"
        Write-Host "Habilitado: pgsql" -ForegroundColor Green
    }
    else {
        $_
    }
}

# Salvar
$newContent | Set-Content $phpIniPath

Write-Host ""
Write-Host "Verificando instalacao..." -ForegroundColor Cyan
php -m | Select-String "pgsql"

Write-Host ""
Write-Host "Feche e reabra o terminal para aplicar as mudancas" -ForegroundColor Yellow
