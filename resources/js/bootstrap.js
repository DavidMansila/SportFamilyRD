import axios from "axios";
import Echo from "laravel-echo";
import Pusher from "pusher-js";

// Configuración de Axios
axios.defaults.withCredentials = true;
axios.defaults.baseURL = "http://localhost:8000";

// Función para obtener cookies
const getCookie = (name) => {
    const value = `; ${document.cookie}`;
    const parts = value.split(`; ${name}=`);
    if (parts.length === 2) return parts.pop().split(";").shift();
};

// Interceptor para manejar CSRF y tokens de autenticación
axios.interceptors.request.use(async (config) => {
    // Asegurar token CSRF para métodos no-GET
    if (["post", "put", "patch", "delete"].includes(config.method)) {
        // Verificar si ya tenemos un token CSRF
        if (!getCookie("XSRF-TOKEN")) {
            await axios.get("/sanctum/csrf-cookie");
        }
        config.headers["X-XSRF-TOKEN"] = getCookie("XSRF-TOKEN");
    }

    // Agregar token de autenticación si existe
    const authToken = localStorage.getItem("auth_token");
    if (authToken) {
        config.headers.Authorization = `Bearer ${authToken}`;
    }

    return config;
});

// Interceptor para manejar errores de autenticación
axios.interceptors.response.use(
    (response) => response,
    async (error) => {
        const originalRequest = error.config;

        // Manejar token CSRF expirado (419)
        if (error.response?.status === 419 && !originalRequest._retry) {
            originalRequest._retry = true;
            await axios.get("/sanctum/csrf-cookie");
            return axios(originalRequest);
        }

        // Manejar token de acceso expirado (401)
        if (error.response?.status === 401 && !originalRequest._retry) {
            originalRequest._retry = true;

            try {
                // Intentar renovar el token
                const refreshResponse = await axios.post("/api/token/refresh");
                localStorage.setItem("auth_token", refreshResponse.data.token);
                originalRequest.headers.Authorization = `Bearer ${refreshResponse.data.token}`;
                return axios(originalRequest);
            } catch (refreshError) {
                // Redirigir a login si el refresh falla
                localStorage.removeItem("auth_token");
                window.location.href = "/login";
            }
        }

        return Promise.reject(error);
    }
);

// Configuración de Echo y Pusher
window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: "pusher",
    key: "337abba0601b16bbbce2",
    cluster: "mt1",
    forceTLS: true,
    encrypted: true,
    authEndpoint: "/broadcasting/auth",
    auth: {
        headers: {
            "X-CSRF-Token": getCookie("XSRF-TOKEN"),
            Authorization: `Bearer ${localStorage.getItem("auth_token")}`,
            Accept: "application/json",
        },
    },
});
