# Guía de Docker para Laravel

Esta aplicación Laravel está configurada para ejecutarse en contenedores Docker con Nginx, PHP 8.1 y MySQL 8.0.

## Requisitos Previos

- Docker instalado
- Docker Compose instalado

## Estructura de Archivos Docker

```
.
├── Dockerfile                      # Imagen principal de la aplicación
├── docker-compose.yml              # Orquestación de servicios
├── .dockerignore                   # Archivos excluidos de la imagen
└── docker/
    ├── nginx/
    │   └── default.conf           # Configuración de Nginx
    ├── supervisor/
    │   └── supervisord.conf       # Configuración de Supervisor
    └── start.sh                   # Script de inicio
```

## Construcción y Ejecución

### Opción 1: Usando Docker Compose (Recomendado)

```bash
# Construir y levantar todos los servicios
docker-compose up -d --build

# Ver logs
docker-compose logs -f

# Detener servicios
docker-compose down
```

### Opción 2: Solo Docker

```bash
# Construir la imagen
docker build -t laravel-app .

# Ejecutar el contenedor
docker run -d -p 8080:80 --name laravel-app laravel-app
```

## Servicios Disponibles

Después de ejecutar `docker-compose up -d`:

- **Aplicación Laravel**: http://localhost:8080
- **PHPMyAdmin**: http://localhost:8081
- **MySQL**: localhost:3306

## Configuración del .env

Antes de ejecutar, asegúrate de configurar tu archivo `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=laravel
DB_PASSWORD=secret
```

## Comandos Útiles

### Ejecutar comandos dentro del contenedor

```bash
# Acceder al contenedor
docker-compose exec app bash

# Ejecutar migraciones
docker-compose exec app php artisan migrate

# Limpiar caché
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan route:clear
docker-compose exec app php artisan view:clear

# Instalar dependencias
docker-compose exec app composer install

# Ver logs de Laravel
docker-compose exec app tail -f storage/logs/laravel.log
```

## Troubleshooting

### Permisos

Si tienes problemas de permisos:

```bash
docker-compose exec app chown -R www-data:www-data /var/www/html/storage
docker-compose exec app chmod -R 775 /var/www/html/storage
```

### Reconstruir contenedores

```bash
docker-compose down
docker-compose build --no-cache
docker-compose up -d
```

### Ver logs de servicios

```bash
# Todos los servicios
docker-compose logs -f

# Solo aplicación
docker-compose logs -f app

# Solo base de datos
docker-compose logs -f db
```

## Producción

Para producción, considera:

1. **Variables de entorno**: Usa variables seguras y no las incluyas en el repositorio
2. **Volúmenes**: Configura volúmenes para persistencia de datos
3. **SSL/TLS**: Configura certificados SSL con Nginx
4. **Optimizaciones**: Asegúrate de que APP_DEBUG=false y APP_ENV=production
5. **Backup**: Implementa estrategia de backup para la base de datos

### Ejemplo de producción

```bash
# Construir para producción
docker-compose -f docker-compose.yml -f docker-compose.prod.yml up -d --build
```

## Notas Importantes

- El contenedor usa Supervisor para gestionar Nginx y PHP-FPM
- Los logs se envían a stdout/stderr para fácil acceso con `docker logs`
- El directorio `storage` debe tener permisos de escritura
- Las optimizaciones de Laravel se ejecutan automáticamente al iniciar

## Soporte

Para más información sobre Docker, consulta:
- [Docker Documentation](https://docs.docker.com/)
- [Laravel Deployment](https://laravel.com/docs/10.x/deployment)
