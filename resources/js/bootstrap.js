import axios from "axios";

// axios.defaults.baseURL = 'http://18.191.50.161/api';
axios.defaults.baseURL = 'http://127.0.0.1:8080/api';
    // axios.defaults.baseURL = 'http://10.0.0.7:8000//api';

// axios.defaults.baseURL = 'http://10.0.0.6:8000/api';
// axios.defaults.baseURL = 'http://10.0.0.7:8000/api';
// axios.defaults.baseURL = 'http://10.193.2.172:8000/api';

// Limita cuantas peticiones a la API viajan al mismo tiempo.
//
// Causa (diagnosticada y aislada, NO es de esta app): en el entorno local de
// Windows, la libreria libpq.dll que usa PHP para hablar con Postgres corrompe
// memoria cuando varios hilos de Apache abren conexiones TLS a Supabase de
// forma simultanea, y se lleva el proceso completo (0xC0000409). Se comprobo
// que PHP y Apache aguantan 12+ peticiones concurrentes sin problema mientras
// NO toquen la base de datos, asi que el limite aplica al numero de conexiones
// TLS simultaneas, no a la carga en general.
//
// Medido empiricamente contra los endpoints reales, en tandas de 10 peticiones
// repetidas: con 4+ el proceso se cae siempre; con 3 se cayo 3 veces en 8
// rondas; con 2 no se cayo ninguna vez. Por eso se usa 2.
//
// En un servidor Linux (produccion, WSL2 o Docker) este problema no existe y
// el limite puede subirse o quitarse.
const MAX_CONCURRENT_REQUESTS = 2;
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
