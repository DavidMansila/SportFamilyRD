# Render solo despliega PHP via Docker (no tiene runtime nativo de PHP como
# tiene Node/Python). Esta imagen compila el SPA de Vue, instala las
# dependencias de PHP y sirve todo con el servidor embebido de Laravel: para
# el trafico de un sitio comunitario en el free tier de Render es suficiente,
# y evita configurar nginx/php-fpm aparte.

# Etapa 1: compilar los assets del SPA (Vue + Vite)
FROM node:20-alpine AS frontend
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci
COPY . .
RUN npm run build

# Etapa 2: imagen final que corre en Render
FROM php:8.2-cli-alpine
RUN apk add --no-cache postgresql-dev libzip-dev oniguruma-dev icu-dev \
    && docker-php-ext-install pdo_pgsql pgsql mbstring zip

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY . .
COPY --from=frontend /app/public/build ./public/build

# --no-dev: los paquetes de require-dev (Pail, Pint, PHPUnit...) no se
# instalan. bootstrap/cache/*.php (si existieran, commiteados por error) se
# borran antes: son cache de un composer install viejo con dev deps y si
# quedan pisan el autodescubrimiento de paquetes -> "Class ...PailServiceProvider
# not found" al arrancar, porque el service provider cacheado ya no esta en vendor/.
RUN rm -f bootstrap/cache/*.php \
    && composer install --no-dev --optimize-autoloader --no-interaction \
    && php artisan storage:link || true

ENV PORT=10000
EXPOSE 10000

# config:cache solo lee variables de entorno (no rutas): route:cache NO se usa
# porque routes/api.php tiene rutas con closures y Laravel no puede cachearlas.
CMD php artisan config:cache && php artisan serve --host=0.0.0.0 --port=$PORT
