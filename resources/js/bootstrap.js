import axios from "axios";

// axios.defaults.baseURL = 'http://18.191.50.161/api';
axios.defaults.baseURL = 'http://127.0.0.1:8080/api';
    // axios.defaults.baseURL = 'http://10.0.0.7:8000//api';

// axios.defaults.baseURL = 'http://10.0.0.6:8000/api';
// axios.defaults.baseURL = 'http://10.0.0.7:8000/api';
// axios.defaults.baseURL = 'http://10.193.2.172:8000/api';

// Limita cuantas peticiones a la API viajan al mismo tiempo. El servidor
// local de desarrollo (Apache/PHP en Windows) se cae si le llegan mas de
// ~8 peticiones PHP simultaneas (bug de concurrencia de libpq/OpenSSL
// dentro del proceso de Apache, no de esta app). Sin este limite, con
// Home + navbar + burbuja de chat + carrito pidiendo datos a la vez al
// cargar la pagina, se supera facilmente ese numero. Se deja con margen
// de sobra por debajo del limite real.
const MAX_CONCURRENT_REQUESTS = 4;
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

axios.interceptors.response.use(
    (response) => {
        onRequestSettled();
        return response;
    },
    (error) => {
        onRequestSettled();
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
