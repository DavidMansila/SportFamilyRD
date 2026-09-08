<template>
  <!--
    Caparazon reutilizable para los pop-outs del Home (noticias, productos,
    eventos, foro y directorio). Solo se encarga de lo transversal: overlay,
    bloqueo del scroll, cierre con Escape / click afuera, trampa de foco y
    atributos ARIA. El contenido lo pone quien lo usa, via slot.

    Se teletransporta a <body> a proposito: si viviera dentro de una <section>
    del Home, cualquier transform u overflow de esas secciones lo recortaria o
    lo sacaria de sitio.

    Recibe "open" como prop (en vez de que el padre lo monte y desmonte con
    v-if) para que el <transition> viva SIEMPRE montado: asi se ve tanto la
    animacion de entrada como la de salida. Con v-if en el padre, el
    componente se destruye de golpe y la salida nunca llega a dibujarse.
  -->
  <Teleport to="body">
    <transition name="home-modal">
      <div v-if="open" class="home-modal-overlay" @click.self="requestClose">
        <div class="home-modal" :class="variant ? `hm-theme--${variant}` : null" role="dialog" aria-modal="true"
          :aria-labelledby="titleId" ref="dialog" tabindex="-1" @keydown.tab="trapFocus">
          <button type="button" class="home-modal__close" @click="requestClose" aria-label="Cerrar ventana"
            ref="closeButton">
            <svg viewBox="0 0 24 24" width="22" height="22" aria-hidden="true" focusable="false">
              <path fill="currentColor"
                d="M19 6.41 17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12 19 6.41Z" />
            </svg>
          </button>

          <div class="home-modal__scroll">
            <!-- El nombre de la prop del slot va en camelCase a proposito: Vue
                 NO camelza las props de un slot, asi que ":title-id" llegaria
                 al padre como la clave "title-id" y no como "titleId". -->
            <slot :titleId="titleId"></slot>
          </div>
        </div>
      </div>
    </transition>
  </Teleport>
</template>

<script>
// Contador global de modales abiertos. Sin esto, cerrar un modal mientras
// otro sigue abierto devolveria el scroll al body y la pagina de atras se
// moveria por debajo del que queda.
let openModalCount = 0;
let previousBodyOverflow = '';

let idCounter = 0;

const FOCUSABLE = [
  'a[href]',
  'button:not([disabled])',
  'input:not([disabled]):not([type="hidden"])',
  'select:not([disabled])',
  'textarea:not([disabled])',
  '[tabindex]:not([tabindex="-1"])'
].join(',');

export default {
  name: 'HomeModal',
  props: {
    open: {
      type: Boolean,
      default: false
    },
    // Pinta el pop-out con el color de la seccion a la que pertenece el
    // contenido ('news', 'product', 'event', 'post', 'sport'). Ver el bloque
    // hm-theme--* en los estilos de abajo.
    variant: {
      type: String,
      default: null
    }
  },
  emits: ['close'],
  data() {
    return {
      titleId: `home-modal-title-${++idCounter}`,
      previouslyFocused: null,
      isLocked: false
    };
  },
  watch: {
    open(isOpen) {
      if (isOpen) {
        this.activate();
      } else {
        this.deactivate();
      }
    }
  },
  methods: {
    requestClose() {
      this.$emit('close');
    },

    activate() {
      if (this.isLocked) return;
      this.isLocked = true;

      this.previouslyFocused = document.activeElement;

      if (openModalCount === 0) {
        previousBodyOverflow = document.body.style.overflow;
        document.body.style.overflow = 'hidden';
      }
      openModalCount++;

      // En fase de captura, para atender Escape antes que cualquier otro
      // manejador de la pagina (por ejemplo el del carrito o el chat).
      document.addEventListener('keydown', this.onKeydown, true);

      this.$nextTick(() => {
        // El foco arranca en el boton de cerrar: es la salida mas util para
        // quien navega con teclado o lector de pantalla.
        (this.$refs.closeButton || this.$refs.dialog)?.focus();
      });
    },

    deactivate() {
      if (!this.isLocked) return;
      this.isLocked = false;

      document.removeEventListener('keydown', this.onKeydown, true);

      openModalCount = Math.max(0, openModalCount - 1);
      if (openModalCount === 0) {
        document.body.style.overflow = previousBodyOverflow;
      }

      // Devuelve el foco a la tarjeta que abrio el modal, para no perder el
      // sitio en la pagina al cerrarlo con teclado.
      if (this.previouslyFocused && typeof this.previouslyFocused.focus === 'function') {
        this.previouslyFocused.focus({ preventScroll: true });
      }
      this.previouslyFocused = null;
    },

    onKeydown(event) {
      if (event.key === 'Escape') {
        event.stopPropagation();
        this.requestClose();
      }
    },

    focusableElements() {
      if (!this.$refs.dialog) return [];
      return Array.from(this.$refs.dialog.querySelectorAll(FOCUSABLE))
        .filter(el => el.offsetParent !== null || el === document.activeElement);
    },

    // Mantiene el foco del teclado dentro del modal: Tab en el ultimo
    // elemento vuelve al primero y Shift+Tab en el primero salta al ultimo.
    trapFocus(event) {
      const elements = this.focusableElements();
      if (elements.length === 0) {
        event.preventDefault();
        return;
      }

      const first = elements[0];
      const last = elements[elements.length - 1];
      const active = document.activeElement;

      if (event.shiftKey && (active === first || active === this.$refs.dialog)) {
        event.preventDefault();
        last.focus();
      } else if (!event.shiftKey && active === last) {
        event.preventDefault();
        first.focus();
      }
    }
  },

  mounted() {
    if (this.open) this.activate();
  },

  beforeUnmount() {
    // Si la vista se destruye con el modal abierto (por ejemplo navegando a
    // otra ruta), hay que soltar igual el scroll del body.
    this.deactivate();
  }
};
</script>

<style scoped>
.home-modal-overlay {
  position: fixed;
  inset: 0;
  /* Alto REAL de la ventana. En el movil "100vh" (y cualquier vh) mide el
     viewport grande, el de la barra de URL escondida; mientras la barra esta
     a la vista, la zona visible es mas baja. Por eso el pop-out parecia
     moverse: al desplazarte, el navegador muestra u oculta la barra y todo lo
     medido en vh se descoloca. 'dvh' es el alto que hay AHORA mismo.
     La linea de vh se queda debajo como respaldo para navegadores viejos. */
  height: 100vh;
  height: 100dvh;
  z-index: var(--z-modal, 1100);
  display: flex;
  align-items: center;
  justify-content: center;
  /* El max() respeta la muesca y la barra de gestos de los telefonos, sin
     bajar nunca del margen normal. */
  padding:
    max(var(--space-4, 1rem), env(safe-area-inset-top))
    max(var(--space-4, 1rem), env(safe-area-inset-right))
    max(var(--space-4, 1rem), env(safe-area-inset-bottom))
    max(var(--space-4, 1rem), env(safe-area-inset-left));
  background: rgba(15, 18, 24, 0.72);
  backdrop-filter: blur(4px);
  -webkit-backdrop-filter: blur(4px);
  overscroll-behavior: contain;
}

.home-modal {
  position: relative;
  width: min(980px, 100%);
  /* Se mide en % contra el overlay (que ya es del alto exacto de la ventana y
     lleva el margen aplicado), no en vh: asi nunca puede sobresalir ni quedar
     por debajo del borde visible.
     El 88% conserva la proporcion que tenia el diseño original con 88vh, para
     que en escritorio siga viendose como un dialogo y no como una pantalla
     completa. En movil se sube al 100% (ver la media query), donde cada pixel
     de alto cuenta. */
  max-height: min(88%, 900px);
  background: var(--surface, #fff);
  color: var(--text-primary, #1a202c);
  border-radius: var(--radius-lg, 16px);
  box-shadow: var(--shadow-lg, 0 12px 32px rgba(0, 0, 0, 0.12));
  outline: none;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

.home-modal__scroll {
  overflow-y: auto;
  overscroll-behavior: contain;
  -webkit-overflow-scrolling: touch;
}

/* ===========================================================================
   Color por seccion

   El pop-out se pinta con el acento de la seccion de la que sale el
   contenido, no con el del Home: una noticia se ve azul como /noticias, un
   producto cyan como /tienda, etc. Los valores son los mismos de
   _variables.scss (que a su vez salen del degradado del navbar de cada
   seccion); se repiten aqui porque el modal se teletransporta a <body> y
   queda fuera de .home-container, asi que no puede heredarlos.

   Se definen como variables CSS sobre .home-modal para que TODO lo de
   adentro (incluido el contenido del slot, que pertenece a HomeView y tiene
   otro scope) los herede solo, sin repetir un color a mano.
   =========================================================================== */
.hm-theme--news {
  /* /noticias, navbar #11217a azul profundo */
  --accent: #11217a;
  --accent-strong: #0b1857;
  --accent-soft: rgba(17, 33, 122, 0.3);
}

.hm-theme--product {
  /* /tienda, navbar #46696f cyan apagado */
  --accent: #46696f;
  --accent-strong: #345054;
  --accent-soft: rgba(70, 105, 111, 0.3);
}

.hm-theme--event {
  /* /calendario, navbar #4e6b2e verde oscuro */
  --accent: #4e6b2e;
  --accent-strong: #3a5122;
  --accent-soft: rgba(78, 107, 46, 0.3);
}

.hm-theme--post {
  /* /foro, navbar #6a11cb violeta */
  --accent: #6a11cb;
  --accent-strong: #530da0;
  --accent-soft: rgba(106, 17, 203, 0.3);
}

.hm-theme--sport {
  /* /directorio, navbar #a13300 naranja */
  --accent: #a13300;
  --accent-strong: #7a2700;
  --accent-soft: rgba(161, 51, 0, 0.3);
}

/* Una linea del color de la seccion arriba del pop-out: da la pista visual
   de a donde pertenece el contenido antes de leer nada. */
.home-modal::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 4px;
  background: var(--accent);
  z-index: 3;
}

.home-modal__close {
  position: absolute;
  top: 12px;
  right: 12px;
  /* Por encima de la barra de acento (::before, z-index 3), que si no le
     pisaba los 4px de arriba y la X se veia cortada. */
  z-index: 4;
  width: 40px;
  height: 40px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border: none;
  border-radius: 50%;
  cursor: pointer;
  color: #fff;
  background: rgba(20, 20, 20, 0.55);
  transition: background 0.2s ease, transform 0.2s ease;
}

.home-modal__close:hover {
  background: rgba(20, 20, 20, 0.8);
  transform: scale(1.06);
}

.home-modal__close:focus-visible {
  outline: none;
  box-shadow: var(--focus-ring, 0 0 0 3px rgba(161, 0, 19, 0.35));
}

/* Transicion de entrada/salida */
.home-modal-enter-active,
.home-modal-leave-active {
  transition: opacity 0.22s ease;
}

.home-modal-enter-active .home-modal,
.home-modal-leave-active .home-modal {
  transition: transform 0.22s ease, opacity 0.22s ease;
}

.home-modal-enter-from,
.home-modal-leave-to {
  opacity: 0;
}

.home-modal-enter-from .home-modal,
.home-modal-leave-to .home-modal {
  opacity: 0;
  transform: translateY(18px) scale(0.97);
}

@media (max-width: 767.98px) {
  /* Antes era una hoja pegada al borde inferior (align-items: flex-end) y de
     borde a borde. Sumado al 92vh, en el telefono quedaba siempre abajo del
     todo y se descolocaba al aparecer/ocultarse la barra del navegador.
     Ahora va centrado, con margen a los cuatro lados, igual que en escritorio:
     una sola forma de comportarse en todos los tamaños. */
  .home-modal-overlay {
    padding:
      max(0.75rem, env(safe-area-inset-top))
      max(0.75rem, env(safe-area-inset-right))
      max(0.75rem, env(safe-area-inset-bottom))
      max(0.75rem, env(safe-area-inset-left));
  }

  .home-modal {
    width: 100%;
    /* En pantallas pequeñas se aprovecha todo el alto disponible dentro del
       margen del overlay, en vez del 88% de escritorio. */
    max-height: 100%;
    /* Todas las esquinas: ya no es una hoja apoyada en el borde. */
    border-radius: var(--radius-lg, 16px);
  }

  /* 44px es el minimo comodo para tocar con el dedo; 40 se queda justo. */
  .home-modal__close {
    width: 44px;
    height: 44px;
  }

  .home-modal-enter-from .home-modal,
  .home-modal-leave-to .home-modal {
    transform: translateY(18px) scale(0.98);
  }
}

@media (prefers-reduced-motion: reduce) {

  .home-modal-enter-active,
  .home-modal-leave-active,
  .home-modal-enter-active .home-modal,
  .home-modal-leave-active .home-modal {
    transition: none;
  }

  .home-modal-enter-from .home-modal,
  .home-modal-leave-to .home-modal {
    transform: none;
  }
}
</style>
