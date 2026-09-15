/**
 * Reintenta automaticamente las imagenes que fallan al cargar.
 *
 * EL PROBLEMA
 * -----------
 * Cuando la peticion de una imagen falla -un parpadeo de red, un
 * net::ERR_NETWORK_CHANGED, un servidor externo lento-, el navegador marca ese
 * <img> como fallido y NO lo reintenta nunca. El hueco se queda en blanco para
 * siempre, aunque la red vuelva un segundo despues.
 *
 * Eso producia un sintoma desconcertante en la Tienda: una tarjeta salia sin
 * foto, se abria el pop-out y ahi SI se veia (porque el modal crea un <img>
 * nuevo, que dispara una peticion nueva), y al cerrarlo la tarjeta seguia
 * vacia, porque su <img> original continuaba en estado fallido.
 *
 * LA SOLUCION
 * -----------
 * Un solo listener en fase de CAPTURA sobre document. El evento 'error' de las
 * imagenes no burbujea, asi que un listener normal en document no lo veria:
 * hay que capturarlo. Asi queda cubierta cualquier imagen de la aplicacion
 * -productos, avatares, noticias, posts- sin tocar ni una plantilla.
 *
 * Se reintenta con espera creciente y un tope, para no castigar a un servidor
 * que de verdad este caido ni entrar en un bucle con una URL rota de verdad.
 */

const MAX_INTENTOS = 3;
const ESPERA_BASE_MS = 800;

// Cuantas veces se ha reintentado cada elemento. WeakMap para que no impida
// que el recolector de basura libere las imagenes que se van del DOM.
const intentos = new WeakMap();

function conMarcaDeReintento(url, n) {
    // Parametro de consulta y no fragmento (#): el fragmento no viaja al
    // servidor y el navegador podria devolver la respuesta fallida de su cache.
    const limpia = url.replace(/([?&])_reintento=\d+/, '$1').replace(/[?&]$/, '');
    const separador = limpia.includes('?') ? '&' : '?';
    return `${limpia}${separador}_reintento=${n}`;
}

function alFallar(evento) {
    const el = evento.target;

    if (!(el instanceof HTMLImageElement)) return;

    // Sin src o con data:/blob: no hay nada que reintentar.
    if (!el.src || el.src.startsWith('data:') || el.src.startsWith('blob:')) return;

    const hechos = intentos.get(el) || 0;
    if (hechos >= MAX_INTENTOS) return;

    const siguiente = hechos + 1;
    intentos.set(el, siguiente);

    const url = el.src;

    setTimeout(() => {
        // Si el elemento ya se quito del DOM, no tiene sentido reintentar.
        if (!el.isConnected) return;
        el.src = conMarcaDeReintento(url, siguiente);
    }, ESPERA_BASE_MS * siguiente);
}

export function activarReintentoDeImagenes() {
    document.addEventListener('error', alFallar, true);
}
