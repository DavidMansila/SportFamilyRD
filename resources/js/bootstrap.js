// En tu archivo principal de JavaScript (app.js o similar)
import axios from "axios";

axios.defaults.baseURL = "http://localhost:8000";
axios.defaults.withCredentials = true; // Enviar cookies con las solicitudes

// Interceptor para agregar el token a cada solicitud
axios.interceptors.request.use((config) => {
    const token = sessionStorage.getItem("auth_token");
    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
});

// Interceptor para manejar errores
axios.interceptors.response.use(
    (response) => response,
    (error) => {
        if (error.response?.status === 401) {
            sessionStorage.removeItem("auth_token");
            sessionStorage.removeItem("user");
            window.location.href = "/login";
        }
        return Promise.reject(error);
    }
);

// Configuración de Pusher/Echo
import Pusher from "pusher-js";
import Echo from "laravel-echo";

document.addEventListener("DOMContentLoaded", () => {
    const pusherConfig = {
        broadcaster: "pusher",
        key: "337abba0601b16bbbce2",
        cluster: "mt1",
        forceTLS: true,
        encrypted: true,
        disableStats: true,
    };

    window.Pusher = Pusher;
    window.Echo = new Echo(pusherConfig);
});
