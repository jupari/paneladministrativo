#!/bin/bash

# Crear directorios necesarios si no existen
mkdir -p /var/www/html/storage/framework/cache
mkdir -p /var/www/html/storage/framework/sessions
mkdir -p /var/www/html/storage/framework/views
mkdir -p /var/www/html/storage/logs

# Establecer permisos
chown -R www-data:www-data /var/www/html/storage
chown -R www-data:www-data /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage
chmod -R 775 /var/www/html/bootstrap/cache

# Verificar si existe .env, si no, crear desde .env.example
if [ ! -f /var/www/html/.env ]; then
    echo "Archivo .env no encontrado. Creando desde .env.example..."
    cp /var/www/html/.env.example /var/www/html/.env
fi

# Generar key de Laravel si no existe
if ! grep -q "APP_KEY=base64:" /var/www/html/.env; then
    echo "Generando APP_KEY..."
    php artisan key:generate --force
fi

# Optimizaciones de Laravel
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Crear directorios de logs si no existen
mkdir -p /var/log/supervisor

# Iniciar Supervisor (gestiona Nginx y PHP-FPM)
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
