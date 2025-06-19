import axios from "axios";
import Pusher from "pusher-js";
import Echo from "laravel-echo";

// Configura Axios
window.axios = axios;
window.axios.defaults.headers.common["X-Requested-With"] = "XMLHttpRequest";
axios.defaults.withCredentials = true;

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
