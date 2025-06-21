import axios from "axios";

// Configuración esencial
axios.defaults.withCredentials = true;
axios.defaults.baseURL = "http://localhost:8000";

// Nueva función para manejar CSRF
const ensureCsrfToken = async () => {
    if (document.cookie.indexOf("XSRF-TOKEN") === -1) {
        await axios.get("/sanctum/csrf-cookie");
    }
};

// Interceptor para solicitudes
axios.interceptors.request.use(async (config) => {
    if (["post", "put", "patch", "delete"].includes(config.method)) {
        await ensureCsrfToken();
        const match = document.cookie.match(/XSRF-TOKEN=([^;]+)/);
        if (match) {
            config.headers["X-XSRF-TOKEN"] = decodeURIComponent(match[1]);
        }
    }
    return config;
});

// Interceptor para respuestas
axios.interceptors.response.use(
    (response) => response,
    async (error) => {
        if (error.response?.status === 419) {
            await ensureCsrfToken();
            return axios.request(error.config);
        }
        return Promise.reject(error);
    }
);

import Pusher from "pusher-js";
import Echo from "laravel-echo";

// window.axios.defaults.headers.common["X-Requested-With"] = "XMLHttpRequest";

// window.Pusher = Pusher;
// window.Echo = new Echo({
//     broadcaster: "pusher",
//     key: import.meta.env.VITE_PUSHER_APP_KEY,
//     cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
//     forceTLS: true,
//     authEndpoint: "/broadcasting/auth",
//     auth: {
//         headers: {
//             "X-CSRF-TOKEN": csrfMeta.content,
//         },
//     },
// });
