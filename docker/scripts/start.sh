#!/bin/sh

# Script de inicialização para o container Laravel
# Autor: Sistema de Deploy
# Data: 2025-10-22

set -e

echo "🚀 Iniciando aplicação Laravel..."

# Criar diretórios de logs se não existirem
echo "📁 Criando diretórios de logs..."
mkdir -p /var/www/html/storage/logs/queries
mkdir -p /var/www/html/storage/framework/cache/data
mkdir -p /var/www/html/storage/framework/sessions
mkdir -p /var/www/html/storage/framework/views
mkdir -p /var/www/html/bootstrap/cache

# Ajustar permissões
echo "🔐 Ajustando permissões..."
chown -R www-data:www-data /var/www/html/storage
chown -R www-data:www-data /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage
chmod -R 775 /var/www/html/bootstrap/cache

# Aguardar banco de dados estar disponível
echo "⏳ Aguardando banco de dados..."
until php artisan db:show 2>/dev/null; do
    echo "   Banco de dados não disponível, tentando novamente em 2s..."
    sleep 2
done

echo "✅ Banco de dados conectado!"

# Executar migrations (se necessário)
if [ "${RUN_MIGRATIONS}" = "true" ]; then
    echo "🗄️  Executando migrations..."
    php artisan migrate --force
fi

# Limpar e cachear configurações
echo "🔧 Otimizando aplicação..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Criar link simbólico para storage (se não existir)
if [ ! -L "/var/www/html/public/storage" ]; then
    echo "🔗 Criando link simbólico para storage..."
    php artisan storage:link
fi

echo "✨ Aplicação iniciada com sucesso!"
echo "📊 Iniciando Supervisor..."

# Iniciar supervisor
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
