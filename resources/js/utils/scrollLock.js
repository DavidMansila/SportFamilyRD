/**
 * Bloqueo del scroll de la pagina mientras hay un pop-out abierto.
 *
 * EL PROBLEMA
 * -----------
 * Cada modal de la aplicacion se apañaba como podia: unos ponian
 * `document.body.style.overflow = 'hidden'`, otros una clase `.no-scroll` y
 * otros no hacian nada. En escritorio `overflow: hidden` sobre el body basta
 * -el navegador lo propaga al viewport-, pero con el dedo NO: en Safari de
 * iOS el gesto tactil sigue arrastrando la pagina, y en cualquier movil, al
 * llegar al final del contenido del modal, el desplazamiento "salta" al
 * documento de detras y este sube y baja por debajo del pop-out.
 *
 * LA SOLUCION
 * -----------
 * Mientras hay algo abierto, el <body> pasa a `position: fixed` desplazado
 * hacia arriba justo lo que estaba desplazada la pagina. Asi el documento
 * literalmente NO tiene scroll que hacer -no es que este prohibido, es que ya
 * no sobra alto-, y eso lo respetan todos los navegadores moviles. Al cerrar
 * se devuelve el body a su sitio y se restaura la posicion, de modo que la
 * persona vuelve exactamente donde estaba.
 *
 * El contador de bloqueos es necesario porque los modales se apilan: un
 * ConfirmDialog encima del pop-out del foro, por ejemplo. Sin contador, al
 * cerrar el dialogo de confirmacion se liberaria el scroll aunque el pop-out
 * siguiera abierto detras.
 */

let bloqueos = 0;
let scrollGuardado = 0;

// Lo que tenia el body antes de tocarlo, para devolverlo tal cual y no
// pisar un estilo en linea que hubiera puesto otro.
let estilosPrevios = null;

export function bloquearScrollDeFondo() {
    bloqueos += 1;

    // Ya estaba bloqueado por otro modal de mas abajo en la pila.
    if (bloqueos > 1) return;

    if (typeof document === 'undefined') return;

    const body = document.body;
    scrollGuardado = window.scrollY || document.documentElement.scrollTop || 0;

    estilosPrevios = {
        position: body.style.position,
        top: body.style.top,
        left: body.style.left,
        right: body.style.right,
        width: body.style.width,
        overflow: body.style.overflow,
        paddingRight: body.style.paddingRight,
    };

    // En escritorio, al fijar el body desaparece la barra de scroll y el
    // contenido se ensancharia de golpe esos ~15px: se ve un salto lateral
    // justo al abrir el modal. Por eso se congela el ancho que el body tenia
    // HASTA ESTE MOMENTO, en vez de dejarlo en 100%. En movil la barra no
    // ocupa espacio, asi que este ancho ya es el de la ventana y no cambia
    // nada. Se mide antes de tocar 'position', que es lo que la hace
    // desaparecer.
    const anchoActual = body.getBoundingClientRect().width;

    body.style.position = 'fixed';
    body.style.top = `-${scrollGuardado}px`;
    body.style.left = '0';
    body.style.right = 'auto';
    body.style.width = `${anchoActual}px`;
    body.style.overflow = 'hidden';

    // Corta el rebote elastico y el "tirar para recargar" del movil, que
    // tambien mueven la pagina aunque no haya scroll.
    body.classList.add('scroll-bloqueado');
}

export function liberarScrollDeFondo() {
    if (bloqueos === 0) return;

    bloqueos -= 1;

    // Todavia queda algun modal abierto por debajo.
    if (bloqueos > 0) return;

    if (typeof document === 'undefined') return;

    const body = document.body;
    const previos = estilosPrevios || {};

    body.style.position = previos.position || '';
    body.style.top = previos.top || '';
    body.style.left = previos.left || '';
    body.style.right = previos.right || '';
    body.style.width = previos.width || '';
    body.style.overflow = previos.overflow || '';
    body.style.paddingRight = previos.paddingRight || '';
    body.classList.remove('scroll-bloqueado');
    estilosPrevios = null;

    // 'instant' y no el valor por defecto: si alguna hoja pusiera
    // scroll-behavior: smooth, volver al sitio se veria como un salto animado.
    window.scrollTo({ top: scrollGuardado, left: 0, behavior: 'instant' });
}

/**
 * Suelta de golpe el bloqueo que tuviera este componente. Para llamar en
 * beforeUnmount: si se navega a otra pagina con el modal abierto, su
 * cerrar...() ya no se ejecuta y el body se quedaria fijo para siempre.
 */
export function liberarScrollSiBloqueado(estabaBloqueado) {
    if (estabaBloqueado) liberarScrollDeFondo();
}
