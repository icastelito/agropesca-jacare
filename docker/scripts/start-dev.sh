#!/bin/sh

# Script de inicialização para o container Laravel (Desenvolvimento)
# Autor: Sistema de Deploy
# Data: 2025-10-22

set -e

echo "🚀 Iniciando aplicação Laravel (Modo Desenvolvimento)..."

# Criar diretórios de logs se não existirem
echo "📁 Criando diretórios de logs..."
mkdir -p /var/www/html/storage/logs/queries
mkdir -p /var/www/html/storage/framework/cache/data
mkdir -p /var/www/html/storage/framework/sessions
mkdir -p /var/www/html/storage/framework/views
mkdir -p /var/www/html/bootstrap/cache

# Ajustar permissões (mais permissivo em dev)
echo "🔐 Ajustando permissões..."
chown -R www-data:www-data /var/www/html/storage 2>/dev/null || true
chown -R www-data:www-data /var/www/html/bootstrap/cache 2>/dev/null || true
chmod -R 777 /var/www/html/storage 2>/dev/null || true
chmod -R 777 /var/www/html/bootstrap/cache 2>/dev/null || true

# Aguardar banco de dados estar disponível
echo "⏳ Aguardando banco de dados..."
timeout=60
counter=0
until php artisan db:show 2>/dev/null; do
    if [ $counter -ge $timeout ]; then
        echo "❌ Timeout aguardando banco de dados!"
        exit 1
    fi
    echo "   Banco de dados não disponível, tentando novamente em 2s..."
    sleep 2
    counter=$((counter + 2))
done

echo "✅ Banco de dados conectado!"

# Executar migrations (se necessário)
if [ "${RUN_MIGRATIONS}" = "true" ]; then
    echo "🗄️  Executando migrations..."
    php artisan migrate --force
fi

# Limpar caches em desenvolvimento
echo "🧹 Limpando caches..."
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

# Criar link simbólico para storage (se não existir)
if [ ! -L "/var/www/html/public/storage" ]; then
    echo "🔗 Criando link simbólico para storage..."
    php artisan storage:link
fi

echo "✨ Aplicação iniciada com sucesso!"
echo "📊 Iniciando Supervisor..."

# Iniciar supervisor
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
