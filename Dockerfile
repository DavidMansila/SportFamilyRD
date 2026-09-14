# Render solo despliega PHP via Docker (no tiene runtime nativo de PHP como
# tiene Node/Python). Esta imagen compila el SPA de Vue, instala las
# dependencias de PHP y sirve todo con el servidor embebido de Laravel: para
# el trafico de un sitio comunitario en el free tier de Render es suficiente,
# y evita configurar nginx/php-fpm aparte.

# Etapa 1: compilar los assets del SPA (Vue + Vite)
# Las variables VITE_* se compilan DENTRO del bundle de JS en este paso (no
# se leen en tiempo de ejecucion), asi que hay que pasarlas como build args.
# Render pasa automaticamente cada Environment Variable configurada en el
# dashboard como ARG si el Dockerfile declara un ARG con el mismo nombre.
FROM node:20-alpine AS frontend
ARG VITE_APP_NAME
ARG VITE_SUPABASE_URL
ARG VITE_SUPABASE_ANON_KEY
ARG VITE_PUSHER_APP_KEY
ARG VITE_PUSHER_APP_CLUSTER
ENV VITE_APP_NAME=$VITE_APP_NAME \
    VITE_SUPABASE_URL=$VITE_SUPABASE_URL \
    VITE_SUPABASE_ANON_KEY=$VITE_SUPABASE_ANON_KEY \
    VITE_PUSHER_APP_KEY=$VITE_PUSHER_APP_KEY \
    VITE_PUSHER_APP_CLUSTER=$VITE_PUSHER_APP_CLUSTER
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
RUN mkdir -p bootstrap/cache \
    && rm -f bootstrap/cache/*.php \
    && composer install --no-dev --optimize-autoloader --no-interaction \
    && php artisan storage:link || true

ENV PORT=10000
EXPOSE 10000

# Las MIGRACIONES corren al arrancar el contenedor.
#
# Antes esto se hacia a mano llamando a GET /api/internal/artisan?cmd=migrate
# con un token en la URL: una consola de administracion remota expuesta a
# internet, con el secreto quedando escrito en los logs de acceso de Render y
# filtrandose por la cabecera Referer. Esa ruta se elimino; el arranque del
# contenedor es donde corresponde hacerlo.
#
# --force es obligatorio en produccion (si no, migrate pide confirmacion
# interactiva y aqui no hay terminal). --isolated evita que dos instancias
# arrancando a la vez ejecuten la misma migracion en paralelo.
#
# config:cache solo lee variables de entorno (no rutas): route:cache NO se usa
# porque routes/api.php tiene rutas con closures y Laravel no puede cachearlas.
#
# PENDIENTE (hallazgo B-6, severidad baja): "artisan serve" envuelve el servidor
# embebido de PHP -un solo proceso, sin concurrencia real ni reinicio ante
# fallo- y la documentacion de Laravel lo desaconseja explicitamente en
# produccion. Lo correcto es FrankenPHP o PHP-FPM + nginx. No se cambio aqui
# porque es un cambio de runtime que hay que VERIFICAR construyendo la imagen,
# y romper el arranque del contenedor bloquearia el despliegue entero.
# EL ORDEN IMPORTA: primero migrar, despues cachear la configuracion.
#
# CACHE_STORE=file va forzado solo para la migracion, porque --isolated toma un
# bloqueo atomico a traves del cache: si CACHE_STORE fuera "database", ese
# bloqueo necesitaria la tabla 'cache'... que todavia no existe en el primer
# despliegue, porque la crea justamente esta migracion. El driver de fichero no
# toca la base de datos y rompe ese circulo.
#
# Y tiene que ir ANTES de config:cache: una vez cacheada la configuracion,
# env() deja de leerse y la variable en linea no tendria ningun efecto.
CMD CACHE_STORE=file php artisan migrate --force --isolated \
 && php artisan config:cache \
 && php artisan serve --host=0.0.0.0 --port=$PORT
