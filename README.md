# SportFamilyRD

Plataforma web para la comunidad deportiva dominicana: foro, tienda, calendario de
eventos, noticias, directorio de deportes, fichas de entrenadores y chat en tiempo
real entre atletas y entrenadores.

Aplicación de página única en **Vue 3** sobre una **API REST en Laravel 11**, con
**PostgreSQL** como base de datos y despliegue continuo en Render.

### ▶ [Ver la aplicación en funcionamiento](https://sportfamilyrd.onrender.com)

> **La primera carga puede tardar cerca de un minuto.** El servicio está en el plan
> gratuito de Render, que suspende la instancia cuando pasa un rato sin visitas y
> vuelve a levantarla con la siguiente petición. A partir de ahí la navegación es
> inmediata.
>
> Se puede recorrer **sin crear cuenta**: foro, noticias, calendario, tienda,
> directorio de deportes y fichas de entrenadores. El registro está abierto si
> quieres probar el carrito, publicar en el foro o solicitar entrenamiento.

<img width="1853" alt="Portada de SportFamilyRD" src="https://github.com/user-attachments/assets/90fa6171-ce34-4582-906a-6bee2c3b0ae3" />

---

## Índice

- [Qué incluye](#qué-incluye)
- [Decisiones técnicas](#decisiones-técnicas)
- [Pruebas y calidad del código](#pruebas-y-calidad-del-código)
- [Stack tecnológico](#stack-tecnológico)
- [Requisitos previos](#requisitos-previos)
- [Instalación y configuración](#instalación-y-configuración)
- [Restaurar la base de datos](#restaurar-la-base-de-datos)
- [Variables de entorno](#variables-de-entorno)
- [Arrancar el proyecto](#arrancar-el-proyecto)
- [Estructura del proyecto](#estructura-del-proyecto)
- [Tareas programadas](#tareas-programadas)
- [Solución de problemas](#solución-de-problemas)
- [Capturas](#capturas)

---

## Qué incluye

Unas 7.200 líneas de PHP repartidas en 67 archivos, 27 componentes de Vue, 79 rutas
de API sobre 29 tablas, y 6 comandos de consola propios.

| Módulo | Qué hace |
|---|---|
| **Foro** | Publicaciones por categoría, con comentarios, respuestas anidadas y «me gusta» en los tres niveles. Imagen opcional por publicación. |
| **Noticias** | Se alimenta solo: cinco fuentes deportivas dominicanas se recorren de forma programada y las noticias entran clasificadas por deporte. Cada usuario puede guardar las suyas. |
| **Calendario** | Eventos deportivos con detalle, precio y aforo, también alimentado por importación automática. Los destacados salen en la portada. |
| **Tienda** | Catálogo con carrito persistente por usuario, control de existencias y aforo, y gestión desde el panel de administración. El paso de pago es una simulación: no hay pasarela real conectada. |
| **Entrenadores** | Solicitud para darse de alta con logros y especialidades, aprobación por administración y directorio público de los aprobados. |
| **Entrenamientos** | Un atleta solicita entrenar con alguien del directorio; el entrenador acepta o rechaza, y al aceptar se abre el chat entre ambos. |
| **Chat** | Mensajería en tiempo real por WebSocket, con contador de no leídos, marcado de leído y aviso de conexión. |
| **Directorio de deportes** | Fichas por deporte con requisitos, lugares donde practicarlo y datos de interés. |
| **Perfil y ajustes** | Datos personales, avatar, preferencias y cambio de contraseña. |
| **Administración** | Gestión de productos, eventos, noticias, solicitudes de entrenador y catálogo de ajustes. |

---

## Decisiones técnicas

Algunas cosas del proyecto no son la opción obvia, y el motivo está en las
restricciones reales del entorno donde corre:

**El correo sale por API HTTP, no por SMTP.** El plan gratuito de Render bloquea el
tráfico saliente a los puertos 25, 465 y 587, así que ningún envío por SMTP puede
completarse. El transporte usa la API HTTPS del proveedor, que no depende de esos
puertos.

**Los archivos subidos van a almacenamiento externo.** El sistema de archivos del
contenedor es efímero: cualquier avatar o imagen subida desaparecería en el
siguiente reinicio. Todo se guarda en Supabase Storage a través del disco `s3` de
Laravel, y el código no distingue entre uno y otro.

**El tiempo real llega por dos caminos distintos.** Las tablas públicas —eventos y
publicaciones— se escuchan directamente. La de usuarios no: lleva correo y
teléfono, así que en vez de exponer las filas, un disparador en la base emite solo
un aviso de que la cuenta cambió, y el cliente vuelve a pedir el dato agregado.

**Las importaciones responden antes de trabajar.** El servicio de cron corta a los
30 segundos y un ciclo de scraping ronda los 28. La petición devuelve `202` de
inmediato y el trabajo arranca después de enviar la respuesta, con un cerrojo que
impide que dos importaciones se solapen.

**Cachés con invalidación por modelo.** Los endpoints públicos se sirven cacheados,
y el caché se limpia desde el propio modelo al guardar o borrar, no desde los
controladores: así no hay forma de añadir una ruta nueva y olvidarse de invalidar.

---

## Pruebas y calidad del código

**76 pruebas automatizadas** con 218 aserciones (`php artisan test`), sobre SQLite en
memoria. No cubren la interfaz: están donde un fallo silencioso hace daño.

| Área | Qué se comprueba |
|---|---|
| Autorización | Barridos genéricos: ningún recurso ajeno es accesible y ninguna acción de administración funciona con una cuenta normal |
| Verificación de correo | Enlace firmado, caducidad, id manipulado y parámetros añadidos |
| Chat | Quién puede abrir una conversación, escribir en ella y verla; coherencia de roles cuando una cuenta cambia de tipo |
| Carrito | Existencias, cantidades acumuladas, líneas huérfanas y unicidad |
| Rendimiento | Que la bandeja de mensajes no haga una consulta por conversación |

Dos detalles de las pruebas que valen más que el número:

- Algunas comprueban el **SQL generado** en lugar del resultado. Las pruebas corren
  sobre SQLite y producción es PostgreSQL: un filtro booleano mal construido pasa en
  una y revienta en la otra, así que comprobar el resultado daría verde sobre código
  roto.
- Otras miran la **tabla de rutas**, no la respuesta: verifican que las rutas de
  administración llevan su middleware. Un barrido funcional no detectaría que falta
  si la ruta cambiara de dirección.

El archivo [`MEJORAS.md`](MEJORAS.md) mantiene el estado de la deuda técnica
pendiente, con lo que está resuelto y lo que no, y por qué.

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

### Cachés de Laravel (importante al desarrollar)

La configuración y las rutas están **cacheadas** para no reconstruirlas en cada
petición (mide unos 90 ms por petición). El efecto secundario es que Laravel deja
de leer `.env` y `routes/` en caliente:

> ⚠️ Si editas **`.env`**, **`config/`** o **`routes/`** y el cambio no surte
> efecto, no es que no funcione: es que está cacheado. Ejecuta:
>
> ```bash
> php artisan optimize
> ```
>
> Y para desactivar los cachés mientras depuras algo:
>
> ```bash
> php artisan optimize:clear
> ```

También hace falta **OPcache activo** en PHP para que el servidor vaya rápido: sin
él, PHP recompila todo Laravel en cada petición y se pierden ~400 ms por petición.
En XAMPP viene apagado; se activa descomentando `zend_extension=opcache` y
`opcache.enable=1` en `php.ini` y reiniciando Apache. En desarrollo, deja
`opcache.validate_timestamps=1` para que tus cambios de código se sigan viendo al
instante.

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

---

## Capturas

Recorrido por las pantallas principales. Cada bloque se despliega al pulsarlo.

<details>
<summary><b>Registro e inicio de sesión</b> — Alta de cuenta y acceso (2)</summary>

<img width="1253" height="757" alt="Registro e inicio de sesión" src="https://github.com/user-attachments/assets/8c2b39aa-8053-4f0d-9859-0f61c7859d7a" />

<img width="1247" height="737" alt="Registro e inicio de sesión" src="https://github.com/user-attachments/assets/68d97bb3-7cb4-41e1-85a5-9c23f754d066" />

</details>

<details>
<summary><b>Portada</b> — Estadísticas en vivo, eventos destacados, noticias y publicaciones populares (6)</summary>

<img width="1853" height="922" alt="Portada" src="https://github.com/user-attachments/assets/90fa6171-ce34-4582-906a-6bee2c3b0ae3" />

<img width="1841" height="867" alt="Portada" src="https://github.com/user-attachments/assets/9089dc1d-7cf8-4623-b5ad-aa9123451f37" />

<img width="1835" height="916" alt="Portada" src="https://github.com/user-attachments/assets/66a48a97-c854-4da7-a24d-a1b93c8b7d15" />

<img width="1832" height="915" alt="Portada" src="https://github.com/user-attachments/assets/afbf9655-e7f3-444b-9d14-cee83993392f" />

<img width="1845" height="915" alt="Portada" src="https://github.com/user-attachments/assets/a8fae0e5-86c7-463c-bac7-268836279285" />

<img width="1841" height="917" alt="Portada" src="https://github.com/user-attachments/assets/7d7a1261-2f2d-4adb-9d7c-d0ce3e51e72e" />

</details>

<details>
<summary><b>Directorio de deportes</b> — Fichas con requisitos y lugares donde practicar (2)</summary>

<img width="1848" height="917" alt="Directorio de deportes" src="https://github.com/user-attachments/assets/f9bb7177-36ba-40c0-8c3d-067464a1108f" />

<img width="1850" height="921" alt="Directorio de deportes" src="https://github.com/user-attachments/assets/8ff19da1-2f74-4591-a908-619bc0a7e2ab" />

</details>

<details>
<summary><b>Noticias</b> — Listado por deporte y detalle, alimentado por importación automática (2)</summary>

<img width="1841" height="925" alt="Noticias" src="https://github.com/user-attachments/assets/cebf1852-699b-4583-ae78-937c8ba59608" />

<img width="1845" height="917" alt="Noticias" src="https://github.com/user-attachments/assets/9778040f-61b1-41e2-a001-0c05911d7a00" />

</details>

<details>
<summary><b>Calendario</b> — Eventos con detalle, precio y aforo (3)</summary>

<img width="1846" height="912" alt="Calendario" src="https://github.com/user-attachments/assets/95ac218a-846a-4bd4-ba3d-199f62795828" />

<img width="1847" height="917" alt="Calendario" src="https://github.com/user-attachments/assets/b6e5ab35-8dfc-49e7-9d17-a5b4e18abcfc" />

<img width="1862" height="927" alt="Calendario" src="https://github.com/user-attachments/assets/b104a19c-928d-4675-b371-4b00a9911fcb" />

</details>

<details>
<summary><b>Tienda</b> — Catálogo, ficha de producto y gestión (4)</summary>

<img width="1847" height="922" alt="Tienda" src="https://github.com/user-attachments/assets/f250ab2e-3d1e-4781-8102-620c2fc53a08" />

<img width="1843" height="918" alt="Tienda" src="https://github.com/user-attachments/assets/0893c12c-873f-4968-bd8d-94f3026f3c67" />

<img width="1837" height="918" alt="Tienda" src="https://github.com/user-attachments/assets/48975a29-8ef9-48e3-9eb0-8a545b71b7fb" />

<img width="1858" height="916" alt="Tienda" src="https://github.com/user-attachments/assets/92e81f51-bede-4bda-8815-b1fa469c5b48" />

</details>

<details>
<summary><b>Entrenadores</b> — Directorio de entrenadores aprobados y su ficha (3)</summary>

<img width="1847" height="918" alt="Entrenadores" src="https://github.com/user-attachments/assets/7a610ff8-467f-4beb-8d19-a29403d0c799" />

<img width="1841" height="920" alt="Entrenadores" src="https://github.com/user-attachments/assets/57ffafec-cb00-41a7-99fd-252656c9db97" />

<img width="1845" height="922" alt="Entrenadores" src="https://github.com/user-attachments/assets/970940f4-5ad2-4df4-9bca-d0375f2d8f08" />

</details>

<details>
<summary><b>Alta de entrenador</b> — Solicitud con logros, especialidades y horarios (6)</summary>

<img width="1295" height="845" alt="Alta de entrenador" src="https://github.com/user-attachments/assets/ef793123-00c9-40cb-b218-a6b94a47358b" />

<img width="1202" height="721" alt="Alta de entrenador" src="https://github.com/user-attachments/assets/c251d7d4-9e76-45e7-a1e6-cf6408b3e928" />

<img width="1156" height="845" alt="Alta de entrenador" src="https://github.com/user-attachments/assets/92b0b1e5-9d12-4901-ae43-be2b450032a9" />

<img width="1017" height="713" alt="Alta de entrenador" src="https://github.com/user-attachments/assets/579df4a2-439b-4d6f-8cd4-1b04f6867448" />

<img width="928" height="898" alt="Alta de entrenador" src="https://github.com/user-attachments/assets/6d3d037f-efbc-430a-b037-1395aba97e5c" />

<img width="472" height="382" alt="Alta de entrenador" src="https://github.com/user-attachments/assets/dd64acfe-e043-49bb-ae46-f0db95166156" />

</details>

<details>
<summary><b>Foro</b> — Publicaciones, comentarios anidados y «me gusta» (4)</summary>

<img width="1843" height="921" alt="Foro" src="https://github.com/user-attachments/assets/73454250-bbac-4106-95bc-f8afd208a738" />

<img width="1846" height="921" alt="Foro" src="https://github.com/user-attachments/assets/33541382-a11b-46fd-ac9b-49c72c65259c" />

<img width="1652" height="835" alt="Foro" src="https://github.com/user-attachments/assets/792bf97b-e022-4f4f-9efa-a439db93f8c9" />

<img width="1653" height="823" alt="Foro" src="https://github.com/user-attachments/assets/69e5e501-58a5-47fc-afd9-2b25d8766d56" />

</details>

<details>
<summary><b>Carrito</b> — Carrito persistente y proceso de compra (3)</summary>

<img width="1860" height="921" alt="Carrito" src="https://github.com/user-attachments/assets/dc6c83de-15f7-4053-adff-5e0dfa1067b0" />

<img width="938" height="852" alt="Carrito" src="https://github.com/user-attachments/assets/95d616a7-9ffd-46b4-b3ef-57b131418520" />

<img width="717" height="721" alt="Carrito" src="https://github.com/user-attachments/assets/e0fae731-da6f-4efe-b78f-8f2fd86c2d65" />

</details>

<details>
<summary><b>Ajustes</b> — Preferencias de la cuenta (2)</summary>

<img width="1842" height="922" alt="Ajustes" src="https://github.com/user-attachments/assets/e3f4ab61-2991-455a-a290-76a3547d478d" />

<img width="1843" height="922" alt="Ajustes" src="https://github.com/user-attachments/assets/14beccef-8635-4d32-8876-f796d89bc8e0" />

</details>

<details>
<summary><b>Perfil</b> — Datos personales, avatar y estadísticas (2)</summary>

<img width="1847" height="920" alt="Perfil" src="https://github.com/user-attachments/assets/1fcee57c-8669-4fb3-a69b-17ffdb3f1317" />

<img width="1616" height="702" alt="Perfil" src="https://github.com/user-attachments/assets/92ca9d33-5ab2-4fb0-95dd-2a39e0f99ce5" />

</details>

---

## Licencia

Proyecto académico desarrollado por [David Mansilla](https://github.com/DavidMansila) y [Yirbel Gomez](https://github.com/YirbelG).
