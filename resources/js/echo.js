import axios from "axios";

/**
 * Inicializacion compartida de Laravel Echo (Pusher) para el chat.
 *
 * Se carga bajo demanda: laravel-echo y pusher-js solo se descargan cuando de
 * verdad hacen falta, no en el bundle inicial de toda la app.
 *
 * Devuelve null si no hay sesion iniciada (sin token no se puede autorizar
 * ningun canal privado).
 */
let echoPromise = null;

export function getEcho() {
    if (window.Echo) {
        return Promise.resolve(window.Echo);
    }

    const token = sessionStorage.getItem("token");
    if (!token) {
        return Promise.resolve(null);
    }

    if (echoPromise) {
        return echoPromise;
    }

    echoPromise = (async () => {
        try {
            const { default: Echo } = await import("laravel-echo");
            const { default: Pusher } = await import("pusher-js");

            window.Pusher = Pusher;
            window.Echo = new Echo({
                broadcaster: "pusher",
                key: import.meta.env.VITE_PUSHER_APP_KEY,
                cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
                forceTLS: true,
                // Se deriva de la baseURL de axios en vez de escribir "/api/..."
                // a mano, para que siga funcionando si cambia el host o el
                // puerto del backend.
                authEndpoint:
                    axios.defaults.baseURL.replace(/\/$/, "") + "/broadcasting/auth",
                auth: {
                    headers: {
                        Authorization: `Bearer ${token}`,
                        Accept: "application/json",
                    },
                },
            });

            return window.Echo;
        } catch (error) {
            console.error("No se pudo inicializar Echo:", error);
            echoPromise = null;
            return null;
        }
    })();

    return echoPromise;
}

/**
 * Cierra la conexion de Echo. Se llama al cerrar sesion para que el usuario
 * siguiente no herede una conexion autenticada con el token del anterior.
 */
export function teardownEcho() {
    if (window.Echo) {
        try {
            window.Echo.disconnect();
        } catch (e) {
            /* la conexion ya estaba cerrada */
        }
        window.Echo = null;
    }
    echoPromise = null;
}
