# SportFamilyRD

Plataforma web para la comunidad deportiva dominicana: foro, tienda, calendario de
eventos, noticias, directorio de deportes, perfiles de entrenadores y chat en
tiempo real.

Aplicación de página única (SPA) en **Vue 3** sobre una **API REST en Laravel 11**,
con **PostgreSQL** como base de datos.

---

## Índice

- [Stack tecnológico](#stack-tecnológico)
- [Requisitos previos](#requisitos-previos)
- [Instalación y configuración](#instalación-y-configuración)
- [Restaurar la base de datos](#restaurar-la-base-de-datos)
- [Variables de entorno](#variables-de-entorno)
- [Arrancar el proyecto](#arrancar-el-proyecto)
- [Estructura del proyecto](#estructura-del-proyecto)
- [Tareas programadas](#tareas-programadas)
- [Solución de problemas](#solución-de-problemas)

---

## Stack tecnológico

### Backend

| Tecnología | Versión | Uso |
|---|---|---|
| PHP | 8.2+ | Lenguaje base |
| Laravel | 11.x | Framework y API REST |
| Laravel Sanctum | 4.x | Autenticación por tokens |
| Eloquent ORM | — | Capa de acceso a datos |
| Symfony DomCrawler | 7.x | Scraping de noticias y eventos |
| Guzzle | 7.x | Cliente HTTP |
| Pusher PHP Server | 7.x | Emisión de eventos WebSocket |

### Frontend

| Tecnología | Versión | Uso |
|---|---|---|
| Vue | 3.5 | Interfaz de usuario |
| Vue Router | 4.x | Enrutado de la SPA |
| Vuex | 4.x | Estado global |
| Vite | 6.x | Empaquetado y servidor de desarrollo |
| Sass / SCSS | 1.56+ | Hojas de estilo |
| Bootstrap | 5.x | Sistema de rejilla y utilidades |
| Tailwind CSS | 3.4 | Utilidades adicionales |
| Axios | 1.7 | Peticiones HTTP |
| Laravel Echo + Pusher JS | 2.x / 8.x | Chat en tiempo real |
| GSAP | 3.x | Animaciones |

### Base de datos

- **PostgreSQL 14+** (en producción, alojado en Supabase mediante el pooler Supavisor)
- **Row Level Security (RLS)** para el control de acceso a nivel de motor
- 30 tablas, 21 claves foráneas con borrado en cascada, 9 políticas RLS

---

## Requisitos previos

| Herramienta | Versión mínima | Comprobar con |
|---|---|---|
| PHP | 8.2 | `php -v` |
| Composer | 2.x | `composer -V` |
| Node.js | 18 | `node -v` |
| npm | 9 | `npm -v` |
| PostgreSQL | 14 | `psql --version` |

Extensiones de PHP necesarias: `pdo_pgsql`, `pgsql`, `mbstring`, `openssl`, `fileinfo`, `curl`, `zip`.

Verifica que estén activas con:

```bash
php -m | grep -E "pdo_pgsql|mbstring|openssl|fileinfo|curl|zip"
```

> En XAMPP, si falta `pdo_pgsql`, descomenta `extension=pdo_pgsql` y
> `extension=pgsql` en tu `php.ini` y reinicia Apache.

---

## Instalación y configuración

### 1. Clonar el repositorio

```bash
git clone https://github.com/DavidMansila/SPORTFAMILYRD.git
cd SPORTFAMILYRD
```

### 2. Instalar dependencias

```bash
composer install
npm install
```

### 3. Crear el archivo de entorno

```bash
cp .env.example .env      # en Windows PowerShell: Copy-Item .env.example .env
php artisan key:generate
```

`key:generate` rellena `APP_KEY`. Sin esa clave Laravel no puede cifrar las
sesiones y la aplicación devolverá error en la primera petición.

### 4. Crear el enlace simbólico de almacenamiento

Las fotos de perfil y las imágenes de producto se guardan en `storage/app/public`
pero se sirven desde `public/storage`. Sin este enlace **las imágenes no cargan**:

```bash
php artisan storage:link
```

---

## Restaurar la base de datos

Hay dos caminos. Elige **uno**.

### Opción A — Scripts SQL (recomendada para revisar el esquema)

Los scripts están en la carpeta `database/`:

| Archivo | Contenido |
|---|---|
| `database/schema.sql` | Estructura completa: 30 tablas, secuencias, claves foráneas, índices y políticas RLS. **Sin datos.** |
| `database/seed.sql` | Datos de ejemplo: catálogo de 25 deportes, 25 productos y 3 usuarios de demostración. |

**1. Crear la base de datos:**

```bash
createdb -U postgres sportfamilyrd
```

Si `createdb` no está en el `PATH`, hazlo desde `psql`:

```bash
psql -U postgres -c "CREATE DATABASE sportfamilyrd;"
```

**2. Cargar la estructura y luego los datos** (el orden importa: `seed.sql`
inserta en tablas que `schema.sql` debe haber creado antes):

```bash
psql -U postgres -d sportfamilyrd -f database/schema.sql
psql -U postgres -d sportfamilyrd -f database/seed.sql
```

**3. Comprobar que se cargó bien:**

```bash
psql -U postgres -d sportfamilyrd -c "\dt"
psql -U postgres -d sportfamilyrd -c "SELECT count(*) FROM sports;"   # debe devolver 25
```

### Opción B — Migraciones de Laravel

Reconstruye el esquema desde las 33 migraciones y ejecuta los seeders de PHP:

```bash
php artisan migrate --seed
```

Para empezar de cero borrando todo lo existente:

```bash
php artisan migrate:fresh --seed
```

> `migrate:fresh` **elimina todas las tablas** antes de recrearlas. No lo ejecutes
> nunca contra una base de datos con información que quieras conservar.

### Usuarios de demostración

`seed.sql` crea tres cuentas. La contraseña de las tres es `password`:

| Correo | Tipo |
|---|---|
| `admin@sportfamilyrd.test` | Administrador |
| `usuario@sportfamilyrd.test` | Usuario estándar |
| `entrenador@sportfamilyrd.test` | Entrenador |

> Son credenciales de desarrollo. Cámbialas antes de exponer el entorno en
> cualquier red accesible.

### Nota sobre privacidad

`seed.sql` contiene **únicamente** datos de catálogo y usuarios inventados. No
incluye usuarios reales, contraseñas, tokens de acceso, mensajes ni publicaciones
de la base de datos de producción.

Si en algún momento generas un volcado nuevo, mantén ese criterio: las tablas
`users`, `personal_access_tokens`, `sessions`, `messages`, `chats`, `posts` y
`comments` guardan datos personales y credenciales activas, y este repositorio es
público.

---

## Variables de entorno

Todas se configuran en `.env`. Parte siempre de `.env.example`, que está
documentado campo por campo.

### Base de datos

Para **PostgreSQL local**:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=sportfamilyrd
DB_USERNAME=postgres
DB_PASSWORD=tu_contraseña
```

Para **Supabase**, usa el *pooler* Supavisor, no el host directo:

```env
DB_CONNECTION=pgsql
DB_HOST=aws-0-<region>.pooler.supabase.com
DB_PORT=5432
DB_DATABASE=postgres
DB_USERNAME=postgres.<project-ref>
DB_PASSWORD=<contraseña del proyecto>
DB_SSLMODE=require
```

> El host directo `db.<ref>.supabase.co` solo resuelve por IPv6 y falla en la
> mayoría de redes domésticas. Los datos del pooler están en el panel de Supabase:
> *Project Settings → Database → Connection pooling*.

### URL de la aplicación

`APP_URL` debe coincidir con el puerto real donde sirves el proyecto, porque
Laravel lo usa para construir enlaces absolutos (correos de verificación, rutas de
archivos):

```env
APP_URL=http://localhost:8000    # con: php artisan serve
# APP_URL=http://localhost:8080  # con Apache/XAMPP en el puerto 8080
```

### Chat en tiempo real (opcional)

El chat funciona con WebSockets a través de Pusher. Sin estas credenciales el
resto de la aplicación funciona con normalidad; solo los mensajes dejan de
llegar al instante.

```env
BROADCAST_CONNECTION=pusher
PUSHER_APP_ID=<id>
PUSHER_APP_KEY=<key>
PUSHER_APP_SECRET=<secret>
PUSHER_APP_CLUSTER=mt1
VITE_PUSHER_APP_KEY="${PUSHER_APP_KEY}"
VITE_PUSHER_APP_CLUSTER="${PUSHER_APP_CLUSTER}"
```

> `PUSHER_APP_SECRET` es privada y nunca debe llegar al navegador. Las variables
> con prefijo `VITE_` sí se compilan dentro del JavaScript y son visibles para
> cualquiera, así que ahí solo va la clave pública.

Las variables `VITE_` se leen **en tiempo de compilación**: si cambias una,
reinicia `npm run dev`.

---

## Arrancar el proyecto

Necesitas **dos procesos a la vez**, cada uno en su propia terminal:

```bash
# Terminal 1 - servidor de Laravel
php artisan serve

# Terminal 2 - servidor de desarrollo de Vite
npm run dev
```

Abre <http://localhost:8000>.

### Compilar para producción

```bash
npm run build
```

> `npm run build` **borra el archivo `public/hot`**. Ese archivo es el que le
> indica a Laravel que cargue los recursos desde el servidor de Vite. Si después
> de compilar vuelves a desarrollar y ves que tus cambios no aparecen en el
> navegador, reinicia `npm run dev` para regenerarlo.

---

## Estructura del proyecto

```
├── app/
│   ├── Console/Commands/     Comandos Artisan (scraping, expiración de solicitudes)
│   ├── Events/               Eventos de broadcasting (chat, mensajes)
│   ├── Http/Controllers/     19 controladores de la API
│   ├── Http/Middleware/      Autenticación y autorización de canales
│   └── Models/               20 modelos Eloquent
├── database/
│   ├── migrations/           33 migraciones
│   ├── seeders/              Seeders de PHP
│   ├── schema.sql            Estructura completa en SQL
│   └── seed.sql              Datos de ejemplo en SQL
├── resources/
│   ├── js/components/        25 componentes Vue, organizados por módulo
│   └── scss/                 Estilos por sección
└── routes/
    ├── api.php               84 rutas de API (60 protegidas con Sanctum)
    └── channels.php          Autorización de canales privados
```

---

## Tareas programadas

Tres comandos se ejecutan a diario mediante el planificador de Laravel:

| Comando | Hora | Función |
|---|---|---|
| `news:import` | 08:00 | Importa noticias deportivas por scraping |
| `calendar:import` | 09:00 | Importa eventos al calendario |
| `training:expire` | 03:00 | Caduca las solicitudes de entrenamiento vencidas |

Para que corran solos, registra el planificador en el cron del sistema:

```bash
* * * * * cd /ruta/al/proyecto && php artisan schedule:run >> /dev/null 2>&1
```

Para ejecutar uno a mano:

```bash
php artisan news:import
```

---

## Solución de problemas

**`SQLSTATE[08006] could not connect to server`**
La base de datos no responde. Comprueba que PostgreSQL esté arrancado y que los
datos de `DB_*` en `.env` sean correctos. Con Supabase, asegúrate de usar el host
del pooler y no el directo.

**`could not find driver`**
Falta la extensión `pdo_pgsql` en PHP. Actívala en `php.ini` y reinicia el
servidor web.

**Las imágenes de perfil o de producto no cargan**
Falta el enlace simbólico. Ejecuta `php artisan storage:link`.

**Los cambios en el código no se reflejan en el navegador**
Comprueba si existe el archivo `public/hot`. Si no está, reinicia `npm run dev`.
Ese archivo lo borra `npm run build`.

**`419 Page Expired` o fallos de sesión**
Falta `APP_KEY`. Ejecuta `php artisan key:generate`.

**El chat no envía mensajes en tiempo real**
Revisa que `BROADCAST_CONNECTION=pusher` y que las credenciales de Pusher estén
completas. Recuerda reiniciar `npm run dev` tras cambiar cualquier variable `VITE_`.

**Cambios en `.env` que no surten efecto**
Limpia la caché de configuración:

```bash
php artisan config:clear
php artisan cache:clear
```

---

## Licencia

Proyecto académico desarrollado por [David Mansilla](https://github.com/DavidMansila).
