FROM ubuntu:24.04

# Evitar prompts interactivos durante la instalación
ENV DEBIAN_FRONTEND=noninteractive
ENV TZ=UTC

# Instalar dependencias del sistema
RUN apt-get update && apt-get install -y \
    software-properties-common \
    curl \
    ca-certificates \
    gnupg \
    && add-apt-repository ppa:ondrej/php -y \
    && apt-get update

# Instalar Nginx y PHP 8.1 con extensiones necesarias
RUN apt-get install -y \
    nginx \
    php8.1-fpm \
    php8.1-cli \
    php8.1-common \
    php8.1-mysql \
    php8.1-zip \
    php8.1-gd \
    php8.1-mbstring \
    php8.1-curl \
    php8.1-xml \
    php8.1-bcmath \
    php8.1-intl \
    php8.1-soap \
    php8.1-dom \
    unzip \
    git \
    supervisor \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Configurar directorio de trabajo
WORKDIR /var/www/html

# Copiar archivos de la aplicación
COPY . /var/www/html

# Instalar dependencias de Composer
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Configurar permisos
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

# Copiar configuración de Nginx
COPY docker/nginx/default.conf /etc/nginx/sites-available/default

# Copiar configuración de PHP-FPM
RUN sed -i 's/listen = \/run\/php\/php8.1-fpm.sock/listen = 127.0.0.1:9000/' /etc/php/8.1/fpm/pool.d/www.conf

# Copiar configuración de Supervisor
COPY docker/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Exponer puerto 80
EXPOSE 80

# Crear script de inicio
COPY docker/start.sh /start.sh
RUN chmod +x /start.sh

# Comando de inicio
CMD ["/start.sh"]
