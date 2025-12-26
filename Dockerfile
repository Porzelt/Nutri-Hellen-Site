# Usa uma imagem profissional pronta para Laravel (PHP 8.3 + Nginx + Otimizações)
FROM serversideup/php:8.3-fpm-nginx:v3.1

# Muda para root para instalar o Node.js (necessário para o Tailwind)
USER root
RUN apt-get update && apt-get install -y nodejs npm

# Volta para o usuário padrão de segurança
USER webuser

# Define o diretório de trabalho
WORKDIR /var/www/html

# Copia os arquivos do projeto com as permissões certas
COPY --chown=webuser:webuser . .

# Instala as dependências do PHP (Backend)
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Instala as dependências do JS e compila o Tailwind (Frontend)
RUN npm install && npm run build