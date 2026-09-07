# SportFamilyRD — Auditoría de código y plan de mejoras

Revisión completa del proyecto (Laravel 11 + Vue 3 SPA) hecha el **7 de septiembre de 2026**.
Las mejoras están agrupadas en bloques por tema. Cada bloque trae qué está mal, por qué
importa y qué hay que hacer para resolverlo.

**Cómo leer las prioridades:**

| Marca | Significado |
|---|---|
| 🔴 **Crítico** | Hay que arreglarlo antes del próximo despliegue. Riesgo de seguridad o pérdida de datos. |
| 🟠 **Alto** | Afecta a usuarios reales hoy (rendimiento, bugs latentes). |
| 🟡 **Medio** | Deuda técnica que va a doler cuando el proyecto crezca. |
| ⚪ **Bajo** | Limpieza y consistencia. |

---

## Bloque 1 — Seguridad

### 1.1 🔴 Cualquiera puede verificar el correo de otra cuenta

**Dónde:** `routes/api.php:219`

```php
Route::get('/email/verify/{id}/{hash}', function (Request $request, $id, $hash) {
    $user = User::find($request->input('user_id'));
    if (! hash_equals((string) $hash, sha1($user->getEmailForVerification()))) ...
```

Tres problemas en la misma ruta:

1. El `{id}` de la URL **se ignora**: el usuario sale de `?user_id=`, que lo controla quien
   llama.
2. El "hash" es `sha1(correo)` sin firmar. Cualquiera que conozca el correo de un usuario
   puede calcularlo y marcar esa cuenta como verificada.
3. No tiene middleware `signed` ni `throttle`, así que tampoco expira ni se limita.

**Cómo resolverlo:** usar el flujo estándar de Laravel — `EmailVerificationRequest` con
middleware `['auth:sanctum', 'signed', 'throttle:6,1']`. Laravel firma la URL con la
`APP_KEY` y le pone caducidad, así que ya no se puede fabricar a mano. El `id` debe salir
del parámetro de la ruta, nunca del query string.

### 1.2 🔴 Cualquier usuario puede editar o borrar el catálogo global de ajustes

**Dónde:** `app/Http/Controllers/ConfigurationController.php:128, 136, 145` — rutas
`Route::resource('/config', ...)` en `routes/api.php:190`

La tabla `configuration` **no es la configuración de un usuario**: es el catálogo global de
tipos de ajuste, y `configuration_user` es la tabla pivote que guarda el valor de cada
usuario. Ese modelo está bien.

El problema es que `store`, `update` y `destroy` del resource están solo bajo
`auth:sanctum`, **sin comprobación de admin**. Es decir, cualquier usuario con sesión
iniciada puede añadir ajustes al catálogo, renombrarlos o borrarlos para toda la
plataforma. Y borrar duele el doble: la migración
`2025_05_16_000101_create_configuration_user_table.php:14` define la clave foránea con
`onDelete('cascade')`, así que un `DELETE /api/config/1` **se lleva por delante el ajuste
de todos los usuarios**, no solo la fila del catálogo.

**Cómo resolverlo:** el catálogo solo lo debe tocar un admin. Sacar `store`, `update` y
`destroy` del grupo general y ponerlos tras el middleware `admin` del punto 2.1:

```php
Route::resource('/config', ConfigurationController::class)->only(['index', 'show']);
Route::middleware('admin')->group(function () {
    Route::resource('/config', ConfigurationController::class)->only(['store', 'update', 'destroy']);
});
```

Los usuarios normales siguen cambiando **su** valor por `POST /config-update-value`, que ya
resuelve el usuario desde el token y es correcto.

### 1.3 🟠 IDOR en entrenamientos

**Dónde:** `app/Http/Controllers/TrainingController.php:127`

`show($id)` devuelve cualquier entrenamiento por id sin verificar que quien pregunta sea
el usuario, el entrenador o un admin. Es raro dentro de este controlador, porque `index`,
`update` y `destroy` **sí** hacen la comprobación — se le escapó solo a `show`.

**Cómo resolverlo:** aplicar la misma comprobación que ya tiene `update` (líneas 157-160),
o mover toda la lógica a una `TrainingPolicy`.

### 1.4 🟠 Los errores devuelven el mensaje interno de la excepción

**Dónde:** 46 apariciones en 8 controladores. Ejemplo típico:

```php
return response()->json(['message' => 'Error ...', 'error' => $e->getMessage()], 500);
```

Un `$e->getMessage()` de una excepción de base de datos puede incluir el SQL, nombres de
columnas o rutas del servidor. Eso es un regalo para quien esté buscando por dónde entrar.

**Cómo resolverlo:** dejar `Log::error($e)` en el servidor y devolver al cliente solo un
mensaje genérico. Si se quiere el detalle en desarrollo, condicionarlo a
`config('app.debug')`.

### 1.5 🟠 Ruta que ejecuta artisan desde el navegador

**Dónde:** `routes/api.php:113` (`/internal/artisan`)

Está protegida por `CRON_SECRET` y una lista blanca de comandos, lo cual es razonable
como parche para Render (que en plan gratis no da consola). Pero es una superficie de
ataque permanente por una necesidad puntual, y `CRON_SECRET` viaja en el **query string**,
donde queda registrado en los logs de acceso de Apache/Render y en el historial del
navegador.

**Cómo resolverlo:** como mínimo, mover el token a una cabecera (`X-Cron-Token`) para que
no quede en los logs. Idealmente, borrar la ruta una vez terminada la migración inicial y
usar los *jobs* de despliegue de Render.

### 1.6 🟡 `dd()` olvidado en producción

**Dónde:** `app/Http/Controllers/UserController.php:40`

```php
public function create(Request $request) { dd($request->all()); }
```

Hoy no se puede llamar (la ruta `create` no está registrada), pero si alguien amplía el
resource, ese método vuelca toda la petición y mata el proceso.

**Cómo resolverlo:** borrar el método (no se usa en una API).

### 1.7 🟡 `'id'` en el `$fillable` de Post

**Dónde:** `app/Models/Post.php:14`

Tener la clave primaria en `$fillable` permite que un `Post::create($request->all())`
sobrescriba el id. Es exactamente lo que `$fillable` existe para evitar.

**Cómo resolverlo:** quitar `'id'` del array.

---

## Bloque 2 — Autorización y consistencia del backend

### 2.1 🟠 23 comprobaciones de admin copiadas a mano

**Dónde:** repartidas por `CalendarController`, `ProductController`, `NewsController`,
`TrainerController`, `TrainingController`, `PostController`, `UserController`.

```php
if ($request->user()->user_type !== 'admin') {
    return response()->json(['message' => 'No autorizado'], 403);
}
```

El patrón está bien, pero repetido 23 veces significa que basta olvidarlo **una vez** para
abrir un agujero — que es justo lo que pasó en 1.2 y 1.3. Además la autorización queda
invisible desde `routes/api.php`: hay que abrir cada controlador para saber quién puede
hacer qué.

**Cómo resolverlo, en dos pasos:**

1. Un middleware `EnsureUserIsAdmin` registrado como `admin`, aplicado en las rutas:
   ```php
   Route::middleware(['auth:sanctum', 'admin'])->group(function () {
       Route::post('/products', [ProductController::class, 'store']);
       // ...
   });
   ```
   Así la regla se lee en el archivo de rutas y no se puede olvidar dentro del método.
2. Para lo que depende del dueño del recurso (posts, comentarios, carrito, entrenamientos,
   configuración), Policies + `$this->authorize()`.

### 2.2 🟡 Falta validación de entrada en varios endpoints

`CartController::updateItem` no valida el rango de `quantity`; `ConfigurationController::store`
acepta cualquier `configuration`; `PostController::store` valida poco. Laravel ya trae
`$request->validate()` y se usa en otros sitios — es cuestión de aplicarlo de forma pareja.

### 2.3 ⚪ `featuredEvents` y `recentProducts` devuelven formas distintas

`featuredEvents` devuelve `{events: [...]}`, `recentProducts` `{products: [...]}`,
`recent-news` un array pelado, `home-stats` un objeto. El frontend tiene que recordar la
forma de cada uno. Conviene unificar con API Resources de Laravel (`JsonResource`), que
además evitan devolver columnas de más sin darse cuenta.

---

## Bloque 3 — Rendimiento

### 3.1 🟠 Endpoints sin paginación

| Ruta | Qué devuelve |
|---|---|
| `GET /post` | **Todos** los posts, con todos sus comentarios y todas las respuestas |
| `GET /news` | Todas las noticias |
| `GET /products` | Todos los productos |
| `GET /calendar` | Todos los eventos |
| `GET /sports` | Todos los deportes |

El frontend descarga el conjunto completo y pagina en el navegador
(`paginatorComponent`). Con pocos registros funciona; con mil posts, cada carga del foro
va a traerse megabytes desde Supabase.

**Cómo resolverlo:** paginar en el servidor (`->paginate(20)`) y adaptar
`paginatorComponent` para que pida la página al backend. Empezar por `/post`, que es el
más pesado (posts + comentarios + respuestas anidadas en una sola respuesta).

### 3.2 🟡 El foro carga la conversación completa para el listado

`PostController::index` hace `with(['comments.replies.user', 'likes', ...])` aunque la
tarjeta del listado solo muestra el título, el extracto y los contadores. Los comentarios
solo hacen falta al abrir el pop-out.

**Cómo resolverlo:** en `index`, cargar solo `withCount('comments')` y `withCount('likes')`.
Los comentarios que los traiga `show($id)` cuando se abre el post.

### 3.3 🟡 `Like::where('user_id', $userId)->get()` trae todos los likes del usuario

**Dónde:** `app/Http/Controllers/PostController.php:49`

Se cargan todos los likes históricos del usuario para marcar cuáles de los posts visibles
están likeados. Debería limitarse a los ids que están en la página actual.

### 3.4 ⚪ Productos aleatorios en cada carga

`ProductController::recentProducts` usa `inRandomOrder()` y además `TiendaView` vuelve a
barajar con `shuffleArray()`. Dos barajadas para lo mismo, y `ORDER BY RANDOM()` en
PostgreSQL escanea la tabla entera. Con catálogo grande conviene cachear la selección unos
minutos.

---

## Bloque 4 — Frontend

### 4.1 🟠 Componentes enormes

| Archivo | Líneas |
|---|---|
| `Foro/ForoView.vue` | 1718 |
| `home/HomeVIew.vue` | 1496 |
| `Perfil/PerfilView.vue` | 1326 |
| `Entrenadores/SolicitudView.vue` | 1275 |
| `Entrenadores/EntrenadoresView.vue` | 1268 |
| `CarritoComponent.vue` | 1185 |
| `Tienda/TiendaView.vue` | 1166 |
| `navbarComponent.vue` | 1136 |
| `Calendario/CalendarioView.vue` | 1066 |

Un archivo de 1700 líneas mezcla plantilla, estado, llamadas a la API y estilos. Cuesta
encontrar las cosas y es fácil romper algo sin darse cuenta.

**Cómo resolverlo, sin reescribir todo de golpe:** extraer por partes, empezando por lo que
ya está duplicado (4.2 y 4.3). El pop-out del Home ya se hizo así: `HomeModal.vue` se llevó
overlay, foco y bloqueo de scroll fuera de la vista.

### 4.2 🟠 `getUserImage()` copiado en 4 componentes

**Dónde:** `ChatComponent.vue`, `Entrenadores/SolicitudesUsuarios.vue`, `Foro/ForoView.vue`,
`home/HomeVIew.vue`.

La misma función, con la misma lógica de "si empieza por http úsala, si no arma
`/storage/users/{id}/…`, si no el avatar por defecto". Ya hubo un bug de avatares rotos
(commit `e080258`) que hubo que arreglar en varios sitios a la vez.

**Cómo resolverlo:** un solo `resources/js/utils/userImage.js` exportando `getUserImage(user)`
e importarlo en los cuatro.

### 4.3 🟠 Cuatro implementaciones distintas del mismo modal

- `Noticias`: overlay + `@click.self` + `body.style.overflow`
- `Tienda`: `:class="{ active }"` + listener propio de Escape
- `Foro`: popout con guardado de scroll
- `Calendario`: vista de detalle a pantalla completa

Ninguna de las cuatro tiene trampa de foco ni `aria-modal`, y cada una gestiona el scroll
del `body` a su manera (si dos coinciden, el scroll se queda bloqueado).

**Cómo resolverlo:** subir `home/HomeModal.vue` a `components/ui/BaseModal.vue` — ya
resuelve teletransporte, Escape, trampa de foco, ARIA, devolución del foco y bloqueo de
scroll con contador — y migrar las cuatro secciones una por una.

### 4.4 🟠 Token leído de `localStorage` cuando se guarda en `sessionStorage`

**Dónde:** `Calendario/CalendarioView.vue:642, 674` y `Tienda/TiendaView.vue:545, 595`

```php
Authorization: `Bearer ${localStorage.getItem('token')}`
```

El token se guarda en **`sessionStorage`**, así que esto manda `Bearer null`. Hoy no se
nota porque el interceptor de `bootstrap.js` pisa la cabecera con el token correcto — es
decir, funciona por accidente. Si alguien toca el interceptor, las operaciones de admin de
Tienda y Calendario dejan de funcionar sin explicación aparente.

**Cómo resolverlo:** borrar esas cabeceras `Authorization` escritas a mano. El interceptor
ya se encarga.

### 4.5 🟡 `this.$notify` no existe

**Dónde:** `Tienda/TiendaView.vue:428`

En el `catch` de `getProducts()` se llama a `this.$notify({...})`, pero ese plugin nunca se
registró en `app.js`. Si falla la carga de productos, el propio manejador de errores lanza
`TypeError` y el usuario no ve nada.

**Cómo resolverlo:** usar el componente `Alert.vue` que ya se usa en el resto del archivo.

### 4.6 🟡 Dos routers, uno muerto

`resources/js/router/index.js` (92 líneas) define un router completo que **nadie importa**:
el que se usa está escrito dentro de `resources/js/app.js`. Además apunta a
`components/Home/HomeView.vue`, ruta que no existe (el archivo real es
`components/home/HomeVIew.vue`).

**Cómo resolverlo:** borrar `router/index.js`, o mejor, mover el router de `app.js` allí
y dejar `app.js` solo con el arranque de la app.

### 4.7 🟡 `resources/js/axios.js` está entero comentado

35 líneas, todas comentadas, y nadie lo importa. Borrar.

### 4.8 ⚪ `console.log` en producción

26 llamadas repartidas en 9 componentes. `HomeVIew.vue` imprime en consola las respuestas
completas de stats, noticias, productos y posts.

**Cómo resolverlo:** quitarlos, o dejar solo `console.error`. Se puede automatizar con
`esbuild: { drop: ['console'] }` en `vite.config.js` para el build de producción.

### 4.9 ⚪ El nombre del archivo del Home tiene una errata

`components/home/HomeVIew.vue` — "VIew" con la I mayúscula, y la carpeta `home` en
minúscula mientras el resto son `Noticias/`, `Tienda/`, `Foro/`. En Windows no molesta,
pero el servidor de producción es Linux y ahí las mayúsculas sí importan: por eso
`router/index.js` (4.6) apunta a una ruta que en Linux no resolvería.

---

## Bloque 5 — Estilos (SCSS)

### 5.1 🟡 `@import` de Sass está deprecado

Cada build imprime decenas de avisos: `@import` desaparece en Dart Sass 3.0. Todo el
proyecto lo usa (`app.scss` y los `<style scoped>` de cada vista).

**Cómo resolverlo:** migrar a `@use` / `@forward`. Sass trae un migrador automático:
`npx sass-migrator module --migrate-deps resources/scss/app.scss`. Conviene hacerlo en un
commit aparte y revisar el resultado, porque `@use` cambia el ámbito de las variables.

### 5.2 🟡 Colores escritos a mano conviviendo con el sistema de variables

`_variables.scss` define un sistema completo (`--accent` por sección, escala de grises,
espaciado, radios, z-index) pero muchos archivos siguen con hexadecimales sueltos. El caso
que ya se corrigió: la ficha del Directorio usaba `#0056b3` (azul) y `#00796b` (verde agua)
cuando el navbar de esa sección es naranja `#a13300`.

**Cómo resolverlo:** ir sección por sección sustituyendo hexadecimales por `var(--accent)`
y compañía. Empezar por Perfil y Entrenadores, que son los que más colores sueltos tienen.

### 5.3 ⚪ CSS muerto

Hay reglas apuntando a clases que ya no existen en ninguna plantilla. Comprobado en
`home.scss`: `.card-back`, `.news-tabs`, `.tab-button` y `.secondary-news-card` no aparecen
ni una vez en `HomeVIew.vue` (las pestañas de filtro de noticias están comentadas en la
plantilla desde hace tiempo).

Al rediseñar la ficha del Directorio se limpió el caso equivalente en `directorio.scss`
(todo el bloque `.sport-detail` / `.detail-*` / `.place-card`), así que ese archivo ya está
al día — sirve de ejemplo de cómo hacerlo en los demás.

**Cómo resolverlo:** pasar PurgeCSS en modo reporte, o revisar a mano archivo por archivo.
No corre prisa, pero cada regla muerta es una pista falsa para quien venga después.

---

## Bloque 6 — Repositorio y tooling

### 6.1 🟠 Binarios de 28 MB versionados

```
ngrok.exe            28 MB
composer-setup.php   58 KB
```

Los dos están en git. `ngrok.exe` es una herramienta personal y `composer-setup.php` es el
instalador de Composer: ninguno pertenece al proyecto. Todo el que clone el repo se baja
esos 28 MB, y quedan en el historial para siempre.

**Cómo resolverlo:** `git rm --cached ngrok.exe composer-setup.php` y añadirlos a
`.gitignore`. Para sacarlos también del historial hace falta reescribirlo
(`git filter-repo`), lo cual solo tiene sentido si el repo es privado y hay pocas copias.

### 6.2 🟡 `README.md` de 16 KB sin instrucciones de arranque local

Conviene documentar lo que hoy solo vive en la cabeza: Apache de XAMPP en el puerto 8080
(no `php artisan serve`, que en esta máquina no aguanta peticiones concurrentes),
`npm run dev` en el 5173, y las variables de `.env` que hacen falta.

### 6.3 ⚪ Sin linter ni formateador automático

`laravel/pint` está en `composer.json` pero no hay script que lo ejecute, y en el frontend
no hay ESLint ni Prettier. Se nota en la indentación irregular y en las comillas mezcladas.

**Cómo resolverlo:** añadir `composer lint` (Pint) y `npm run lint` (ESLint + Prettier con
`eslint-plugin-vue`). Con un hook de pre-commit si se quiere que no se olvide.

---

## Bloque 7 — Pruebas

### 7.1 🟠 No hay ni una prueba

`tests/Feature/ExampleTest.php` y `tests/Unit/ExampleTest.php` son los que trae Laravel de
fábrica. PHPUnit está configurado pero nunca se usó.

Esto es lo que hace que los bugs del Bloque 1 pasen desapercibidos: no hay nada que avise
de que `/api/config/5` responde a un usuario que no es su dueño.

**Cómo resolverlo, por orden de rentabilidad:**

1. **Pruebas de autorización.** Una por endpoint protegido: usuario ajeno → 403. Son
   rápidas de escribir y son justo las que habrían pillado 1.2 y 1.3.
2. **Pruebas de los endpoints del Home** (`/home-stats`, `/recent-news`, `/recent-products`,
   `/popular-posts`, `/featured-events`), que ahora alimentan los pop-outs.
3. **Pruebas del flujo de carrito** (agregar producto, agregar evento, límites de stock).

---

## Plan de trabajo

Cuatro fases. Cada una se puede entregar por separado; no hace falta terminarlas todas
antes de desplegar.

### Fase 1 — Cerrar los agujeros (1-2 días)

> El objetivo es que no quede nada explotable. Todo lo de aquí es de bajo riesgo: son
> comprobaciones que se añaden, no comportamiento que se cambia.

| # | Tarea | Bloque |
|---|---|---|
| 1 | Rehacer la verificación de correo con `signed` + `throttle` | 1.1 |
| 2 | Filtrar por dueño en `ConfigurationController` | 1.2 |
| 3 | Añadir la comprobación que falta en `TrainingController::show` | 1.3 |
| 4 | Dejar de devolver `$e->getMessage()` al cliente | 1.4 |
| 5 | Mover `CRON_SECRET` a cabecera en `/internal/*` | 1.5 |
| 6 | Borrar `UserController::create()` y quitar `'id'` del `$fillable` de Post | 1.6, 1.7 |
| 7 | Sacar `ngrok.exe` y `composer-setup.php` del repo | 6.1 |

**Cómo verificar:** escribir las pruebas de autorización de 7.1 punto 1 **antes** de tocar
el código. Deben fallar primero y pasar después.

### Fase 2 — Bugs latentes y limpieza barata (1 día)

> Cosas que hoy funcionan de casualidad o que sobran. Cero riesgo, mucha claridad.

| # | Tarea | Bloque |
|---|---|---|
| 1 | Quitar las cabeceras `Authorization` con `localStorage` | 4.4 |
| 2 | Cambiar `this.$notify` por `Alert.vue` en Tienda | 4.5 |
| 3 | Borrar `resources/js/axios.js` y `resources/js/router/index.js` | 4.6, 4.7 |
| 4 | `drop: ['console']` en el build de producción | 4.8 |
| 5 | Renombrar `home/HomeVIew.vue` → `Home/HomeView.vue` | 4.9 |
| 6 | Añadir Pint + ESLint/Prettier y pasarlos una vez | 6.3 |
| 7 | Documentar el arranque local en el README | 6.2 |

⚠️ El renombrado de 4.9 hay que hacerlo con `git mv` en dos pasos (a un nombre temporal y
luego al definitivo), porque el sistema de archivos de Windows no distingue mayúsculas y
git no ve el cambio.

### Fase 3 — Rendimiento (2-3 días)

> Aquí sí cambia el comportamiento, así que conviene ir endpoint por endpoint y probar en
> local antes de subir.

| # | Tarea | Bloque |
|---|---|---|
| 1 | Paginar `/post` en el servidor y adaptar `paginatorComponent` | 3.1 |
| 2 | Sacar comentarios y respuestas del listado del foro (`withCount`) | 3.2 |
| 3 | Acotar la consulta de likes a los posts de la página | 3.3 |
| 4 | Paginar `/news`, `/products`, `/calendar` | 3.1 |
| 5 | Cachear la selección de productos destacados | 3.4 |

**Cómo verificar:** medir con la barra de red del navegador el peso de `/api/post` antes y
después. Objetivo: bajar de megabytes a decenas de kilobytes.

### Fase 4 — Estructura (continuo, en paralelo a lo demás)

> No es un sprint: son mejoras que se van metiendo cuando se toca cada zona. La regla es
> "si abres un archivo para otra cosa, aprovecha y aplícale lo que le toque".

| # | Tarea | Bloque |
|---|---|---|
| 1 | Middleware `admin` + Policies, sustituyendo los 23 checks a mano | 2.1 |
| 2 | `BaseModal.vue` a partir de `HomeModal.vue`, migrando una sección por vez | 4.3 |
| 3 | `utils/userImage.js` compartido | 4.2 |
| 4 | Validación pareja con `$request->validate()` | 2.2 |
| 5 | API Resources para unificar las respuestas | 2.3 |
| 6 | Migrar `@import` a `@use` con `sass-migrator` | 5.1 |
| 7 | Sustituir hexadecimales sueltos por las variables del sistema | 5.2 |
| 8 | Barrer el CSS muerto | 5.3 |
| 9 | Partir los componentes de más de 1000 líneas | 4.1 |

---

## Resumen

| Bloque | Crítico | Alto | Medio | Bajo |
|---|:-:|:-:|:-:|:-:|
| 1. Seguridad | 2 | 3 | 2 | — |
| 2. Autorización | — | 1 | 1 | 1 |
| 3. Rendimiento | — | 1 | 2 | 1 |
| 4. Frontend | — | 4 | 3 | 2 |
| 5. Estilos | — | — | 2 | 1 |
| 6. Repositorio | — | 1 | 1 | 1 |
| 7. Pruebas | — | 1 | — | — |
| **Total** | **2** | **11** | **11** | **6** |

Lo más urgente son los dos puntos rojos del Bloque 1: la verificación de correo falsificable
(1.1) y el IDOR de configuraciones (1.2). Los dos se arreglan en pocas horas.

Lo que más va a cambiar el día a día a medio plazo es la Fase 3 (paginación) y el punto 7.1
(pruebas de autorización), porque es lo que evita que vuelvan a aparecer fallos como los del
Bloque 1.
