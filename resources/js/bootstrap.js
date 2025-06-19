import Pusher from "pusher-js";
import Echo from "laravel-echo";

// Configura Axios
import axios from 'axios';

window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Configurar token si existe
const token = localStorage.getItem('auth_token');
if (token) {
    window.axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
}

document.addEventListener("DOMContentLoaded", () => {
    const pusherConfig = {
        broadcaster: "pusher",
        key: "337abba0601b16bbbce2", // Key directa
        cluster: "mt1", // Cluster directo
        forceTLS: true,
        encrypted: true,
        disableStats: true,
        // authEndpoint: "/broadcasting/auth", // COMENTA ESTA LÍNEA
    };

    window.Pusher = Pusher;
    window.Echo = new Echo(pusherConfig);

    console.log("Pusher inicializado sin autenticación CSRF");
});
