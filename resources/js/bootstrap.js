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
            Accept: "application/json",
        },
    },
});

// Función para obtener cookies
function getCookie(name) {
    const value = `; ${document.cookie}`;
    const parts = value.split(`; ${name}=`);
    if (parts.length === 2) return parts.pop().split(";").shift();
}
