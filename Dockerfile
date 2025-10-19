# Dockerfile Multi-stage para Produção
# Build otimizado para Railway e ambientes de produção

# Stage 1: Build do Frontend
FROM node:20-alpine AS frontend-builder

WORKDIR /app

# Copiar apenas os arquivos de dependências primeiro (cache layer)
COPY package*.json ./

# Instalar dependências do Node
RUN npm ci --only=production

# Copiar o código fonte
COPY . .

# Build dos assets com Vite
RUN npm run build

# Stage 2: Setup do PHP/Laravel
FROM php:8.2-fpm-alpine AS php-base

# Instalar dependências do sistema
RUN apk add --no-cache \
    nginx \
    supervisor \
    postgresql-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    curl \
    oniguruma-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev

# Instalar extensões PHP necessárias
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
    pdo \
    pdo_pgsql \
    pgsql \
    mbstring \
    zip \
    exif \
    pcntl \
    bcmath \
    gd \
    opcache

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Stage 3: Produção
FROM php-base AS production

# Configurações de PHP para produção
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# Copiar configurações customizadas
COPY docker/php/php-fpm.conf /usr/local/etc/php-fpm.d/www.conf
COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini
COPY docker/nginx/nginx.conf /etc/nginx/nginx.conf
COPY docker/nginx/default.conf /etc/nginx/http.d/default.conf
COPY docker/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Copiar código da aplicação
COPY --chown=www-data:www-data . /var/www/html

# Copiar assets buildados do frontend
COPY --from=frontend-builder --chown=www-data:www-data /app/public/build /var/www/html/public/build

# Instalar dependências do Composer (apenas produção)
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

# Criar diretórios necessários e ajustar permissões
RUN mkdir -p \
    storage/framework/cache \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 775 storage bootstrap/cache

# Limpar cache do Composer e arquivos temporários
RUN rm -rf /root/.composer /tmp/*

# Executar seeds (DESCOMENTE para o primeiro deploy, depois comente novamente)
# RUN php artisan db:seed --force

# Expor porta
EXPOSE 8080

# Healthcheck
HEALTHCHECK --interval=30s --timeout=3s --start-period=40s --retries=3 \
    CMD curl -f http://localhost:8080/health || exit 1

# Usar supervisor para gerenciar nginx e php-fpm
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
