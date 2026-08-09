#!/bin/sh

# Encerra imediatamente quando um comando falhar (-e) ou quando uma variável
# não definida for utilizada (-u). Isso evita iniciar uma aplicação incompleta.
set -eu

# Garante que os comandos Artisan sejam executados a partir da raiz do Laravel.
cd /var/www/html

# Cria os diretórios graváveis usados pelo framework. A opção -p não gera erro
# quando os diretórios já existem, tornando a inicialização repetível.
mkdir -p \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache \
    database

# Garante que o arquivo SQLite exista. Quando a pasta database está montada
# pelo Compose, este arquivo fica persistido no computador hospedeiro.
touch database/database.sqlite

# O PHP-FPM executa como www-data e precisa escrever no banco, logs, sessões,
# cache e views compiladas do Laravel.
chown -R www-data:www-data storage bootstrap/cache database

# Aplica somente as migrations ainda pendentes. --force permite a execução no
# ambiente production e --no-interaction impede perguntas durante o startup.
php artisan migrate --force --no-interaction

# Remove caches antigos de configuração, rotas, eventos e views. Isso garante
# que as variáveis fornecidas pelo Docker Compose sejam consideradas.
php artisan optimize:clear

# Reaplica as permissões porque os comandos Artisan podem criar novos arquivos.
chown -R www-data:www-data storage bootstrap/cache database

# Substitui o script pelo comando definido no Dockerfile/Compose (php-fpm).
# O uso de exec permite que o PHP-FPM receba corretamente sinais de parada.
exec "$@"
