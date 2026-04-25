FROM serversideup/php:8.4-fpm-nginx

USER root

# Install Node.js 20 LTS via NodeSource (Ubuntu default is too old for Vite)
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

# Install PHP dependencies (separate layer for Docker cache)
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts

# Install Node dependencies (separate layer for Docker cache)
COPY package.json package-lock.json vite.config.js postcss.config.js ./
COPY resources/ ./resources/
RUN npm ci

# Copy full application source
COPY --chown=www-data:www-data . .

# Build Vite assets and run Laravel post-install hooks
RUN npm run build \
    && composer run-script post-autoload-dump

# Ensure storage and cache dirs are writable
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

USER www-data
