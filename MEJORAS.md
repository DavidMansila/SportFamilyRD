# SportFamilyRD — Auditoría de código y plan de mejoras

Revisión original del proyecto (Laravel 11 + Vue 3 SPA) hecha el **7 de septiembre de 2026**.

> **Revisado el 20 de septiembre de 2026.** Se comprobó punto por punto contra el código
> actual, buscando por contenido (los números de línea del documento original ya no valen:
> el código cambió mucho). **De los 30 puntos, 13 están resueltos y 4 parcialmente.** El
> Bloque 1 completo —lo único marcado como crítico— está cerrado. Al final hay un
> **Bloque 8** con lo que encontró esta revisión y que el documento original no recogía.

**Cómo leer las prioridades:**

| Marca | Significado |
|---|---|
| 🔴 **Crítico** | Hay que arreglarlo antes del próximo despliegue. Riesgo de seguridad o pérdida de datos. |
| 🟠 **Alto** | Afecta a usuarios reales hoy (rendimiento, bugs latentes). |
| 🟡 **Medio** | Deuda técnica que va a doler cuando el proyecto crezca. |
| ⚪ **Bajo** | Limpieza y consistencia. |

**Estado de cada punto:**

| Marca | Significado |
|---|---|
| ✅ **RESUELTO** | Verificado en el código actual, con la evidencia anotada. |
| 🟡 **PARCIAL** | Se mitigó, pero no como pedía el punto. Se indica qué falta. |
| ⬜ **PENDIENTE** | Sigue igual. |

---

## Bloque 1 — Seguridad · ✅ los 7 puntos resueltos

### 1.1 🔴 Cualquiera puede verificar el correo de otra cuenta — ✅ RESUELTO

El usuario salía de `?user_id=` (controlado por quien llamaba), el "hash" era `sha1(correo)`
sin firmar y la ruta no tenía `signed` ni `throttle`.

**Hoy:** `routes/api.php` resuelve por el `{id}` de la ruta y aplica
`->whereNumber('id')->middleware(['signed', 'throttle:10,1'])`. La firma se comprueba de
verdad. Cubierto por `CorreoVerificacionTest` y por las pruebas de enlace manipulado y
caducado de `AutorizacionTest` / `BarridoAutorizacionTest`.

### 1.2 🔴 Cualquier usuario puede editar o borrar el catálogo global de ajustes — ✅ RESUELTO

**Hoy:** `ConfigurationController` centraliza la comprobación en `soloAdmin()` y la invoca
en `show`, `store`, `update` y `destroy`. Se resolvió dentro del controlador y no como
middleware de ruta (que es lo que pedía el punto 2.1), pero el agujero está cerrado.
Prueba: `test_gestionar_la_configuracion_global_exige_admin`.

### 1.3 🟠 IDOR en entrenamientos — ✅ RESUELTO

**Hoy:** `TrainingController::show()` comprueba solicitante, entrenador destinatario o
admin, con el mismo criterio que `update()` y `destroy()`.

### 1.4 🟠 Los errores devuelven el mensaje interno de la excepción — ✅ RESUELTO

**Hoy:** existe `error_json($e, $mensaje, $status)` en `app/Support/helpers.php`, que
registra el detalle con `Log::error()` y devuelve al cliente solo un mensaje genérico y un
identificador de incidencia. **Cero** apariciones de `'error' => $e->getMessage()` en
respuestas de controladores.

### 1.5 🟠 Ruta que ejecuta artisan desde el navegador — ✅ RESUELTO

**Hoy:** la ruta `/internal/artisan` **ya no existe**; las migraciones corren al arrancar el
contenedor (`Dockerfile`). Las rutas `/internal/*` que quedan validan con `X-Cron-Token` de
cabecera y aceptan `?token=` solo por compatibilidad con el cron ya configurado.

### 1.6 🟡 `dd()` olvidado en producción — ✅ RESUELTO

**Hoy:** `UserController::create()` no existe, y no queda ningún `dd(` en `app/`.

### 1.7 🟡 `'id'` en el `$fillable` de Post — ✅ RESUELTO

**Hoy:** `app/Models/Post.php` ya no lo incluye, con un comentario explicando por qué.

---

## Bloque 2 — Autorización y consistencia del backend

### 2.1 🟠 23 comprobaciones de admin copiadas a mano — ⬜ PENDIENTE (hoy son 29)

No existe el middleware `EnsureUserIsAdmin` ni el alias `admin` en `bootstrap/app.php`, y
ninguna ruta lo usa. El patrón `user_type !== 'admin'` aparece **29 veces repartidas en 10
controladores** — cuatro más que cuando se escribió el documento.

Sigue vigente el argumento original: basta olvidarlo una vez para abrir un agujero, y es
exactamente lo que pasó en 1.2 y 1.3. Con la suite de autorización que ahora existe (7.1),
hacer este cambio es mucho menos arriesgado que en septiembre: las pruebas avisan si alguna
ruta se queda sin protección.

### 2.2 🟡 Falta validación de entrada en varios endpoints — ✅ RESUELTO

Los tres casos citados validan hoy: `CartController::updateItem` acota `quantity`
(`min:1|max:99`), `ConfigurationController::store` valida `configuration`, y
`PostController::store` valida título, contenido, categoría e imagen.

### 2.3 ⚪ `featuredEvents` y `recentProducts` devuelven formas distintas — ⬜ PENDIENTE

No existe `app/Http/Resources` ni ninguna clase que extienda `JsonResource`. Las cuatro
formas siguen siendo distintas: `{events: […]}`, `{message, products: […]}`, array pelado
para `/recent-news` y objeto plano para `/home-stats`.

---

## Bloque 3 — Rendimiento

### 3.1 🟠 Endpoints sin paginación — 🟡 PARCIAL

**Ninguno pagina**, pero cada uno recibió una mitigación distinta. Medido en local contra la
base real (peso de la respuesta):

| Ruta | Peso hoy | Estrategia actual |
|---|---:|---|
| `GET /trainer/approved` | **135,1 KB** | Ninguna. No aparecía en el documento original |
| `GET /products` | 98,1 KB | Solo caché de 300 s; devuelve todas las filas y columnas |
| `GET /news` | 80,4 KB | Recorta `description` a 300 caracteres + caché; el texto completo lo sirve `/news/{id}` |
| `GET /sports` | 65,3 KB | Solo caché de 600 s (catálogo pequeño y estable) |
| `GET /post` | 48,4 KB | `limit(200)` + columnas explícitas del autor |
| `GET /calendar` | 4,5 KB | Solo caché de 300 s |

La decisión de no paginar `/news` está razonada en el propio código: el frontend filtra,
busca y ordena en el cliente, y paginar en servidor obligaría a mover toda esa lógica.
**Lo que falta:** `/products`, `/sports` y sobre todo `/trainer/approved` siguen enviando el
100 % de filas y columnas; la caché ahorra la conexión a Supabase, no el tamaño.

### 3.2 🟡 El foro carga la conversación completa para el listado — 🟡 PARCIAL

`PostController::index` sigue trayendo el árbol completo de comentarios y respuestas. Se
quitó la relación `likes` cruda (un 25 % del payload) y se limitaron las columnas del autor,
pero **no** se sustituyó por `withCount('comments')` ni existe un `show($id)` que sirva el
detalle, que era justo lo que pedía el punto.

### 3.3 🟡 `Like::where('user_id', $userId)->get()` trae todos los likes del usuario — ⬜ PENDIENTE

Sigue igual en `PostController::index`. Se carga el historial completo de likes del usuario
en cada visita al foro, para marcar cuáles de los 200 posts visibles están likeados.

### 3.4 ⚪ Productos aleatorios en cada carga — ✅ RESUELTO

`recentProducts` envuelve el `inRandomOrder()` en `Cache::remember(…, 60s)`. La "doble
barajada" ya no existe: el `shuffleArray()` de `TiendaView` opera sobre `/products` (catálogo
completo), que es un endpoint distinto del que consume el Home.

---

## Bloque 4 — Frontend

### 4.1 🟠 Componentes enormes — ⬜ PENDIENTE (y han crecido)

| Archivo | 7 sep | Hoy |
|---|---:|---:|
| `Foro/ForoView.vue` | 1718 | **1931** |
| `home/HomeVIew.vue` | 1496 | **1539** |
| `Tienda/TiendaView.vue` | 1166 | **1434** |
| `Entrenadores/EntrenadoresView.vue` | 1268 | **1314** |
| `Perfil/PerfilView.vue` | 1326 | 1326 |
| `Entrenadores/SolicitudView.vue` | 1275 | **1297** |
| `navbarComponent.vue` | 1136 | **1202** |
| `CarritoComponent.vue` | 1185 | **1196** |
| `Calendario/CalendarioView.vue` | 1066 | 946 |

Solo Calendario adelgazó. Tienda creció 268 líneas y Foro 213.

### 4.2 🟠 `getUserImage()` copiado en 4 componentes — ⬜ PENDIENTE

No existe `resources/js/utils/userImage.js` (la carpeta `utils/` solo tiene `scrollLock.js`
y `sports.js`). La función sigue duplicada en los mismos cuatro sitios.

### 4.3 🟠 Cuatro implementaciones distintas del mismo modal — ⬜ PENDIENTE

No existe `components/ui/BaseModal.vue`. `home/HomeModal.vue` sigue siendo el único con
trampa de foco, `aria-modal` y devolución de foco. Tienda y Calendario añadieron
`role="dialog" aria-modal="true"` a sus modales de administración, pero sin trampa de foco.

### 4.4 🟠 Token leído de `localStorage` cuando se guarda en `sessionStorage` — ✅ RESUELTO

Cero apariciones de `localStorage.getItem('token')` en todo `resources/js`. Las cabeceras
escritas a mano se borraron y hay comentarios en Tienda y Calendario explicando que el
interceptor de `bootstrap.js` ya adjunta el token.

### 4.5 🟡 `this.$notify` no existe — ⬜ PENDIENTE

Sigue en el `catch` de `getProducts()` de `Tienda/TiendaView.vue`, y `app.js` sigue sin
registrar ese plugin. Si falla la carga de productos, el manejador de errores lanza un
`TypeError` y el usuario no ve nada.

### 4.6 🟡 Dos routers, uno muerto — ⬜ PENDIENTE

`resources/js/router/index.js` sigue ahí, con sus 92 líneas, apuntando a
`components/Home/HomeView.vue`, que no existe. Nadie lo importa.

### 4.7 🟡 `resources/js/axios.js` está entero comentado — ⬜ PENDIENTE

Sigue existiendo, 35 líneas, todas comentadas, sin que nadie lo importe.

### 4.8 ⚪ `console.log` en producción — 🟡 PARCIAL

De 26 se bajó a **15**, en 7 archivos. Sigue sin haber `esbuild: { drop: ['console'] }` en
`vite.config.js`, así que el build de producción los mantiene.

### 4.9 ⚪ El nombre del archivo del Home tiene una errata — ⬜ PENDIENTE

Sigue siendo `components/home/HomeVIew.vue`. Es lo que hace que el router muerto de 4.6
apunte a una ruta que en Linux no resolvería.

---

## Bloque 5 — Estilos (SCSS) · ⬜ los 3 puntos pendientes

### 5.1 🟡 `@import` de Sass está deprecado — ⬜ PENDIENTE

**66** `@import` de Sass en `resources/`, y **cero** `@use` / `@forward` en todo el proyecto.

### 5.2 🟡 Colores escritos a mano conviviendo con el sistema de variables — ⬜ PENDIENTE

Unos **1.484** hexadecimales sueltos: 1.158 en `resources/scss/**/*.scss` y 326 en los
bloques `<style>` de los `.vue`.

### 5.3 ⚪ CSS muerto — ⬜ PENDIENTE

Confirmado tal cual lo describía el documento: `.card-back`, `.news-tabs`, `.tab-button` y
`.secondary-news-card` siguen definidas en `resources/scss/Home/home.scss` y no aparecen ni
una vez en `HomeVIew.vue`.

---

## Bloque 6 — Repositorio y tooling

### 6.1 🟠 Binarios de 28 MB versionados — ✅ RESUELTO

`ngrok.exe` y `composer-setup.php` ya no están rastreados ni existen en disco, y ambos
figuran en `.gitignore`. (Siguen en el historial de git; sacarlos de ahí requiere
reescribirlo, y eso solo compensa si el repositorio fuera público.)

### 6.2 🟡 `README.md` sin instrucciones de arranque local — ✅ RESUELTO

El README pasó a 535 líneas con una sección de instalación completa: dependencias, `.env`,
`storage:link`, restauración de la base, Apache/XAMPP en el puerto 8080 y `npm run dev`.

### 6.3 ⚪ Sin linter ni formateador automático — ⬜ PENDIENTE

`package.json` solo tiene `build` y `dev`; no hay ESLint ni Prettier ni configuración de
ninguno. `laravel/pint` sigue en `require-dev` sin script que lo ejecute.

---

## Bloque 7 — Pruebas

### 7.1 🟠 No hay ni una prueba — ✅ RESUELTO

De cero a **57 pruebas con 182 aserciones**, en 13 archivos. Los tres puntos que pedía el
documento, por orden de rentabilidad:

1. **Autorización** → cubierto de sobra: `AutorizacionTest`, `BarridoAutorizacionTest`
   (barridos genéricos de IDOR y de escalada de privilegios) y `SegundaAuditoriaTest`.
2. **Endpoints del Home** → parcial: solo `/home-stats` tiene pruebas
   (`ContadoresDelHomeTest`). `/recent-news`, `/recent-products`, `/popular-posts` y
   `/featured-events` siguen sin ninguna.
3. **Flujo de carrito** → parcial: `CarritoIntegridadTest` cubre bien los límites de stock,
   el borrado en cascada y la restricción de línea única, pero **todos** los casos usan
   `item_type = 'product'`; no hay ni uno con `'event'`.

---

## Bloque 8 — Encontrado en la revisión del 20 de septiembre

Nada de esto estaba en el documento original.

### 8.1 🟠 Los avisos de verificación se encolaban sin que nadie los procesara — ✅ RESUELTO

`App\Events\EmailVerified` implementaba `ShouldBroadcast` (encolado) mientras los otros tres
eventos del proyecto usan `ShouldBroadcastNow`. Como `QUEUE_CONNECTION=database` pero **no
hay ningún worker** —en Render corre un único proceso que atiende peticiones—, cada aviso se
guardaba en la tabla `jobs` y se quedaba allí. Había dos atascados, del 17 y del 20 de
septiembre, sin error ni rastro.

Corregido a `ShouldBroadcastNow` y limpiados los dos trabajos. `EventosEnVivoTest` recorre
`app/Events` y falla si alguno vuelve a encolarse, así que cubre también los que se escriban
más adelante.

### 8.2 ⚪ Ese evento no lo escucha nadie — ⬜ DECISIÓN PENDIENTE

Ningún componente se suscribe al canal `email-verified`: la verificación se resuelve
llamando al endpoint y refrescando el usuario. El evento se emite a un canal vacío, lo que
ahora cuesta una llamada a Pusher dentro de la petición.

**Dos opciones:** eliminar el evento (nadie lo usa), o suscribir la pantalla de "verifica tu
correo" para que se actualice sola cuando la persona verifica desde otro dispositivo, que es
para lo que parecía estar pensado.

### 8.3 🟡 Una tabla `news` vacía conviviendo con la que se usa — ⬜ PENDIENTE

El modelo `News` apunta a `NewsScrapping` (**129 filas**), pero en la base existe además una
tabla `news` **vacía** y con columnas distintas (`content`, `categoria`). Es el origen de los
bugs de `content` que arrastró el código durante meses: quien lee el nombre del modelo supone
la tabla equivocada.

Conviene borrarla, o renombrar `NewsScrapping` a `news` y quedarse con una sola. Lo segundo
es más limpio pero toca el modelo y cualquier consulta escrita a mano.

### 8.4 🟡 `NewsController::index` quedó huérfano — ⬜ PENDIENTE

La ruta `GET /news` la sirve un *closure* en `routes/api.php`; el método `index()` del
controlador, que hace `News::all()` sin recortes ni caché, ya no lo llama nadie. Si alguien
vuelve a enrutarlo, se pierden de golpe el recorte de campo y la caché.

### 8.5 🟡 Cada petición escribe la sesión en Supabase — ⬜ PENDIENTE

Hay **60 filas** en la tabla `sessions`: producción usa el driver de base de datos. Esta API
se autentica con tokens Bearer, así que la sesión no aporta nada y añade una lectura y una
escritura contra una base remota en cada petición.

Antes de cambiarlo hay que comprobar qué depende de la sesión (la autorización de canales de
broadcasting y el CSRF de las rutas web). Es un cambio de variable de entorno, no de código.

### 8.6 ⚪ Piezas sueltas en la base de datos — ⬜ PENDIENTE

Queda la función `rls_auto_enable`, sin ningún trigger que la invoque, y la tabla
`keepalive` (1 fila), sin ninguna referencia en el código —probablemente la toca un cron
externo para que Supabase no suspenda el proyecto por inactividad—. Ninguna de las dos
molesta, pero ambas son de la misma familia que las tres piezas desconectadas que se
arreglaron el 20 de septiembre (la función de n8n, el trigger del contador y la publicación
de realtime): conviene documentarlas o eliminarlas.

---

## Qué queda por hacer

Lo de los bloques 1 y 7 ya está. Este es el orden que tiene sentido hoy.

### Ahora que hay pruebas: el middleware `admin` (2.1)

Es el punto de más valor que sigue abierto, y el momento es bueno: con 57 pruebas —entre
ellas dos barridos que comprueban que ninguna acción de administración es accesible para una
cuenta normal— el cambio se puede hacer con red debajo. Sustituir 29 comprobaciones
repartidas por una regla visible en el archivo de rutas.

### Limpieza barata (medio día, riesgo cero)

| # | Tarea | Punto |
|---|---|---|
| 1 | Borrar `resources/js/axios.js` y `resources/js/router/index.js` | 4.6, 4.7 |
| 2 | Cambiar `this.$notify` por `Alert.vue` en Tienda | 4.5 |
| 3 | `drop: ['console']` en el build de producción | 4.8 |
| 4 | `utils/userImage.js` compartido | 4.2 |
| 5 | Borrar la tabla `news` vacía | 8.3 |
| 6 | Decidir qué hacer con el evento `EmailVerified` | 8.2 |
| 7 | Pint + ESLint/Prettier con un script que los ejecute | 6.3 |

### Rendimiento, por orden de lo que más pesa

1. `/trainer/approved` (135 KB) — el más pesado y el único sin ninguna mitigación.
2. Acotar la consulta de likes del foro a los posts visibles (3.3).
3. Sacar comentarios y respuestas del listado del foro (3.2).
4. Recortar columnas en `/products` (3.1).

### Continuo

`BaseModal.vue` (4.3), partir los componentes de más de 1.000 líneas (4.1), migrar a `@use`
(5.1), sustituir hexadecimales por variables (5.2) y barrer el CSS muerto (5.3). La regla
sigue siendo la misma: si abres un archivo para otra cosa, aprovecha.

---

## Resumen

| Bloque | ✅ Resuelto | 🟡 Parcial | ⬜ Pendiente |
|---|:-:|:-:|:-:|
| 1. Seguridad | 7 | — | — |
| 2. Autorización | 1 | — | 2 |
| 3. Rendimiento | 1 | 2 | 1 |
| 4. Frontend | 1 | 1 | 7 |
| 5. Estilos | — | — | 3 |
| 6. Repositorio | 2 | — | 1 |
| 7. Pruebas | 1 | — | — |
| 8. Revisión del 20 sep | 1 | — | 5 |
| **Total** | **14** | **3** | **19** |

**Los dos puntos críticos están cerrados**, y con ellos todo el Bloque 1. La cobertura de
pruebas pasó de cero a 57, que era lo que el documento señalaba como condición para que lo
demás no se re-rompiera en silencio.

Lo que queda se reparte en tres grupos: **lo que no se hizo** (middleware `admin`, API
Resources, paginación real), **la limpieza que nunca es urgente** (routers muertos, CSS,
`@import`, linter) y **lo que apareció después** del documento original, en el Bloque 8. El
patrón que más se repite en esa última parte merece atención: piezas que existen, parecen
montadas y no están conectadas a nada.
