import axios from "axios";

// Relativa a proposito: Laravel sirve el SPA y la API desde el mismo origen
// (mismo dominio/puerto), asi que "/api" resuelve solo contra el host que
// esta sirviendo la pagina en ese momento, sin importar cual sea (artisan
// serve en :8000, Apache en :8080, una IP de red local, o el dominio real de
// produccion). Antes esto apuntaba hardcodeado a http://127.0.0.1:8080/api:
// funcionaba "de casualidad" en la laptop que tenia ese servidor local
// corriendo, y fallaba para cualquier otro dispositivo o visitante real.
axios.defaults.baseURL = '/api';

// Tope de peticiones a la API en vuelo al mismo tiempo.
//
// Ya no es un parche: la causa de los crashes del servidor era una recursion
// infinita en config/sanctum.php ('guard' se incluia a si mismo), que reventaba
// la pila en cada peticion autenticada. Con eso corregido, el servidor aguanta
// 10 peticiones concurrentes sin un solo fallo.
//
// Se mantiene un tope moderado a proposito: la base de datos es remota
// (Supabase) y los navegadores de todas formas limitan a ~6 conexiones por
// dominio, asi que 6 no frena nada y evita abrir rafagas innecesarias de
// conexiones nuevas contra la BD.
const MAX_CONCURRENT_REQUESTS = 6;
let activeRequestCount = 0;
const pendingRequestQueue = [];

function releaseNextQueuedRequest() {
    if (activeRequestCount >= MAX_CONCURRENT_REQUESTS || pendingRequestQueue.length === 0) {
        return;
    }
    activeRequestCount++;
    const runNext = pendingRequestQueue.shift();
    runNext();
}

function onRequestSettled() {
    activeRequestCount--;
    releaseNextQueuedRequest();
}

// Interceptor para agregar token + limitar concurrencia
axios.interceptors.request.use((config) => {
    const token = sessionStorage.getItem("token");
    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    }

    return new Promise((resolve) => {
        pendingRequestQueue.push(() => resolve(config));
        releaseNextQueuedRequest();
    });
});

// Reintento unico para fallos transitorios de conexion. La base de datos es
// remota (Supabase), asi que un handshake TLS puede fallar de vez en cuando
// por razones de red; ademas, en el entorno local de Windows libpq devuelve
// esporadicamente errores de OpenSSL bajo concurrencia (ver nota de arriba).
// Solo se reintentan GET (nunca POST/PUT/DELETE, que no son idempotentes y
// podrian duplicar datos) y solo ante error de red o 5xx, una sola vez.
const RETRY_DELAY_MS = 400;

function shouldRetry(error) {
    const config = error.config;
    if (!config || config._retried) return false;
    if ((config.method || 'get').toLowerCase() !== 'get') return false;
    // Sin respuesta = fallo de red/conexion; 5xx = error del servidor.
    return !error.response || error.response.status >= 500;
}

// Sesion caducada o revocada.
//
// Los tokens de Sanctum ahora CADUCAN (config/sanctum.php: 7 dias) y se revocan
// al cambiar la contraseña. Antes no caducaban nunca, asi que este caso casi no
// se daba y el manejador de 401 estaba comentado.
//
// Sin el, el usuario queda en un estado roto a medias: sessionStorage todavia
// tiene el objeto "user", asi que la interfaz lo trata como identificado y
// pinta la burbuja de chat, el carrito y el resto, pero cada peticion contra la
// API devuelve 401 y no se entera de nada. Hay que limpiar la sesion local en
// cuanto el servidor diga que ya no vale.
//
// Se excluye /login: un 401 ahi es "credenciales incorrectas", no una sesion
// caducada, y no debe disparar una recarga.
function esSesionCaducada(error) {
    if (!error.response || error.response.status !== 401) return false;

    const url = (error.config && error.config.url) || '';
    if (url.includes('/login')) return false;

    // Solo si creiamos tener sesion. Se mira el token Y el usuario: el estado
    // "user sin token" existe de verdad (al abrir el enlace de verificacion en
    // un navegador sin sesion) y es justo el que hay que limpiar, porque la
    // interfaz se comporta como identificada y ninguna peticion funciona.
    //
    // Si no hay ni token ni user, no habia sesion que caducar: un 401 ahi es lo
    // esperado y no debe provocar ninguna redireccion.
    return !!(sessionStorage.getItem('token') || sessionStorage.getItem('user'));
}

let cerrandoSesion = false;

axios.interceptors.response.use(
    (response) => {
        onRequestSettled();
        return response;
    },
    (error) => {
        onRequestSettled();

        if (shouldRetry(error)) {
            error.config._retried = true;
            return new Promise((resolve) => setTimeout(resolve, RETRY_DELAY_MS))
                .then(() => axios(error.config));
        }

        if (esSesionCaducada(error) && !cerrandoSesion) {
            // El guard evita que varias peticiones en vuelo disparen varias
            // recargas a la vez.
            cerrandoSesion = true;
            sessionStorage.removeItem('token');
            sessionStorage.removeItem('user');
            sessionStorage.setItem(
                'logoutMessage',
                'Tu sesión expiró. Vuelve a iniciar sesión.'
            );
            window.location.assign('/signup');
        }

        return Promise.reject(error);
    }
);
