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

/**
 * Comprueba que una variable VITE_ traiga un valor de verdad.
 *
 * Las VITE_* se incrustan en el bundle EN TIEMPO DE COMPILACION, asi que un
 * valor mal puesto no se puede corregir en caliente: queda dentro del JS hasta
 * el siguiente build. El caso que hay que atrapar es el del texto sin expandir,
 * "${PUSHER_APP_KEY}": en local Vite lee .env y resuelve esas referencias, pero
 * en la imagen de Docker NO hay .env (lo excluye .dockerignore) y los valores
 * llegan como build args desde el panel de Render, que no expande nada. Si alli
 * se copia "${PUSHER_APP_KEY}" tal cual desde el .env, el literal acaba en el
 * bundle y pusher-js intenta conectar a "ws-${pusher_app_cluster}.pusher.com",
 * un host que no existe: ERR_NAME_NOT_RESOLVED y reintentos sin fin llenando la
 * consola en cada inicio de sesion.
 */
function valorUtil(valor) {
    return typeof valor === "string" && valor !== "" && !valor.includes("${");
}

export function getEcho() {
    if (window.Echo) {
        return Promise.resolve(window.Echo);
    }

    const token = sessionStorage.getItem("token");
    if (!token) {
        return Promise.resolve(null);
    }

    const key = import.meta.env.VITE_PUSHER_APP_KEY;
    const cluster = import.meta.env.VITE_PUSHER_APP_CLUSTER;

    // Sin credenciales validas no se intenta conectar: mas vale quedarse sin
    // tiempo real (el chat sigue cargando por HTTP) que dejar a pusher-js
    // reintentando contra un host inexistente. El aviso sale una sola vez y
    // dice que hay que mirar.
    if (!valorUtil(key) || !valorUtil(cluster)) {
        console.warn(
            "[Echo] Chat en tiempo real desactivado: VITE_PUSHER_APP_KEY / " +
                "VITE_PUSHER_APP_CLUSTER no tienen un valor valido en el build. " +
                "Revisa las variables de entorno del despliegue (el valor debe ser " +
                "la clave literal, no una referencia del tipo ${PUSHER_APP_KEY})."
        );
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
                key,
                cluster,
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
