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

        return Promise.reject(error);
    }
);

// Manejar errores 401 (No autorizado)
// axios.interceptors.response.use(
//     (response) => response,
//     (error) => {
//         if (error.response && error.response.status === 401) {
//             sessionStorage.removeItem("token");
//             sessionStorage.removeItem("user");
//             window.location.href = "";
//         }
//         return Promise.reject(error);
//     }
// );
