FROM serversideup/php:8.3-fpm-nginx

USER root
# Instala Node.js e NPM (necessário para o Frontend)
RUN apt-get update && apt-get install -y nodejs npm

# Define o usuário padrão do servidor web (Correção aqui!)
USER www-data

WORKDIR /var/www/html

# Copia os arquivos já dando permissão para o usuário correto
COPY --chown=www-data:www-data . .

# Instala dependências do PHP
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Instala dependências do JS e constrói o Frontend
RUN npm install && npm run build
