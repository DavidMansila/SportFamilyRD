import axios from "axios";
import Echo from "laravel-echo";
import Pusher from "pusher-js";

// Configuración esencial de Axios
axios.defaults.withCredentials = true;
axios.defaults.baseURL = "http://localhost:8000";

// Configuración de CSRF
const ensureCsrfToken = async () => {
    if (document.cookie.indexOf("XSRF-TOKEN") === -1) {
        await axios.get("/sanctum/csrf-cookie");
    }
};

axios.interceptors.request.use(async (config) => {
    if (["post", "put", "patch", "delete"].includes(config.method)) {
        await ensureCsrfToken();
    }
    return config;
});

// Configuración de Echo
window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: "pusher",
    key: import.meta.env.VITE_PUSHER_APP_KEY,
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
    forceTLS: true,
    encrypted: true,
    authEndpoint: "/broadcasting/auth",
    auth: {
        headers: {
            "X-CSRF-Token":
                document.querySelector('meta[name="csrf-token"]')?.content ||
                "",
            Authorization: `Bearer ${localStorage.getItem("token")}`,
        },
    },
    disableStats: true,
    enabledTransports: ["ws", "wss"],
});
