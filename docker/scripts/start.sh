#!/bin/sh

# Script de inicialização para Railway/Produção
set -e

echo "🚀 Iniciando aplicação..."

# Usar a porta fornecida pelo Railway, ou 8080 como fallback
export PORT=${PORT:-8080}

echo "📡 Porta configurada: $PORT"

# Atualizar configuração do Nginx com a porta correta
sed -i "s/listen 8080;/listen ${PORT};/g" /etc/nginx/http.d/default.conf
sed -i "s/listen \[::\]:8080;/listen [::]:${PORT};/g" /etc/nginx/http.d/default.conf

# Verificar se há variáveis de ambiente necessárias
if [ -z "$APP_KEY" ]; then
    echo "⚠️  APP_KEY não definida, gerando..."
    php artisan key:generate --force
fi

# Limpar caches antigos
echo "🧹 Limpando caches..."
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

# Executar migrations
echo "🗄️  Executando migrations..."
php artisan migrate --force

# Criar caches otimizados
echo "⚡ Criando caches otimizados..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Ajustar permissões
echo "🔐 Ajustando permissões..."
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

echo "✅ Inicialização completa!"

# Iniciar supervisor
echo "🎬 Iniciando serviços..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
