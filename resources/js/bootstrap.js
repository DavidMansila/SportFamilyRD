import axios from "axios";
import Pusher from "pusher-js";
import Echo from "laravel-echo";

// Configura Axios
window.axios = axios;
window.axios.defaults.headers.common["X-Requested-With"] = "XMLHttpRequest";

// Configura Echo/Pusher SOLO después de que el DOM esté listo
document.addEventListener("DOMContentLoaded", () => {
    const csrfMeta = document.querySelector('meta[name="csrf-token"]');

    if (!csrfMeta) {
        console.error("ERROR: Meta tag CSRF no encontrado. Revisa tu HTML.");
        return;
    }

    window.Pusher = Pusher;
    window.Echo = new Echo({
        broadcaster: "pusher",
        key: import.meta.env.VITE_PUSHER_APP_KEY,
        cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
        forceTLS: true,
        authEndpoint: "/broadcasting/auth",
        auth: {
            headers: {
                "X-CSRF-TOKEN": csrfMeta.content,
            },
        },
    });
});
