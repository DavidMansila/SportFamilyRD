<template>
  <!--
    Confirmacion para acciones destructivas del admin (borrar un producto, un
    evento, una noticia, un post).

    Sustituye al confirm() del navegador, que tenia tres problemas: se ve
    distinto en cada sistema operativo y nada que ver con la pagina, bloquea
    el hilo entero mientras esta abierto, y no deja decir QUE se va a borrar
    con formato (el nombre en negrita, el aviso de que no se puede deshacer).
  -->
  <Teleport to="body">
    <transition name="confirm">
      <div v-if="open" class="confirm-overlay" @click.self="cancel">
        <div class="confirm-box" role="alertdialog" aria-modal="true" :aria-labelledby="titleId"
          :aria-describedby="bodyId" ref="box" tabindex="-1" @keydown.tab="trapFocus">
          <div class="confirm-icon" :class="`confirm-icon--${tone}`" aria-hidden="true">
            <svg viewBox="0 0 24 24" width="26" height="26" fill="none" stroke="currentColor" stroke-width="2"
              stroke-linecap="round" stroke-linejoin="round">
              <path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z" />
              <line x1="12" y1="9" x2="12" y2="13" />
              <line x1="12" y1="17" x2="12.01" y2="17" />
            </svg>
          </div>

          <h2 class="confirm-title" :id="titleId">{{ title }}</h2>
          <p class="confirm-text" :id="bodyId">
            <slot>{{ message }}</slot>
          </p>

          <div class="confirm-actions">
            <button type="button" class="confirm-btn confirm-btn--ghost" @click="cancel" ref="cancelButton">
              {{ cancelLabel }}
            </button>
            <button type="button" class="confirm-btn" :class="`confirm-btn--${tone}`" :disabled="busy"
              @click="$emit('confirm')">
              {{ busy ? 'Eliminando...' : confirmLabel }}
            </button>
          </div>
        </div>
      </div>
    </transition>
  </Teleport>
</template>

<script>
let idCounter = 0;

const FOCUSABLE = 'button:not([disabled]), a[href], input:not([disabled]), [tabindex]:not([tabindex="-1"])';

export default {
  name: 'ConfirmDialog',
  props: {
    open: { type: Boolean, default: false },
    title: { type: String, default: '¿Estás seguro?' },
    message: { type: String, default: '' },
    confirmLabel: { type: String, default: 'Eliminar' },
    cancelLabel: { type: String, default: 'Cancelar' },
    // 'danger' para borrados, 'accent' para acciones normales.
    tone: { type: String, default: 'danger' },
    // Deja el boton en "Eliminando..." mientras la peticion esta en vuelo,
    // para que no se pueda pulsar dos veces y borrar dos cosas.
    busy: { type: Boolean, default: false }
  },
  emits: ['confirm', 'cancel'],
  data() {
    const n = ++idCounter;
    return {
      titleId: `confirm-title-${n}`,
      bodyId: `confirm-body-${n}`,
      previouslyFocused: null
    };
  },
  watch: {
    open(isOpen) {
      if (isOpen) {
        this.previouslyFocused = document.activeElement;
        document.addEventListener('keydown', this.onKeydown, true);
        // El foco arranca en "Cancelar" a proposito: en un dialogo de borrado,
        // la opcion segura es la que debe estar bajo el dedo.
        this.$nextTick(() => this.$refs.cancelButton?.focus());
      } else {
        this.release();
      }
    }
  },
  methods: {
    cancel() {
      this.$emit('cancel');
    },

    release() {
      document.removeEventListener('keydown', this.onKeydown, true);
      if (this.previouslyFocused?.focus) {
        this.previouslyFocused.focus({ preventScroll: true });
      }
      this.previouslyFocused = null;
    },

    onKeydown(event) {
      if (event.key === 'Escape') {
        event.stopPropagation();
        this.cancel();
      }
    },

    trapFocus(event) {
      const items = Array.from(this.$refs.box?.querySelectorAll(FOCUSABLE) || []);
      if (!items.length) return;

      const first = items[0];
      const last = items[items.length - 1];

      if (event.shiftKey && document.activeElement === first) {
        event.preventDefault();
        last.focus();
      } else if (!event.shiftKey && document.activeElement === last) {
        event.preventDefault();
        first.focus();
      }
    }
  },
  beforeUnmount() {
    if (this.open) this.release();
  }
};
</script>

<style scoped>
.confirm-overlay {
  position: fixed;
  inset: 0;
  /* Por encima de TODO: la confirmacion puede lanzarse desde dentro de
     cualquier otra capa (un formulario de admin, o el pop-out de un post del
     foro, que usa z-index 2000 a pelo). Ver --z-confirm en _variables.scss.

     Antes esto era calc(var(--z-modal) + 10) = 1110, y el pop-out del foro
     quedaba por delante: al pulsar "eliminar publicacion", el dialogo salia
     por detras del post. */
  z-index: var(--z-confirm, 100000);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: var(--space-4, 1rem);
  background: rgba(15, 18, 24, 0.78);
  backdrop-filter: blur(4px);
  -webkit-backdrop-filter: blur(4px);
}

.confirm-box {
  width: min(420px, 100%);
  padding: var(--space-6, 2rem);
  border-radius: var(--radius-lg, 16px);
  background: var(--surface, #fff);
  box-shadow: var(--shadow-lg, 0 12px 32px rgba(0, 0, 0, 0.12));
  text-align: center;
  outline: none;
}

.confirm-icon {
  width: 56px;
  height: 56px;
  margin: 0 auto var(--space-4, 1rem);
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
}

.confirm-icon--danger {
  background: rgba(192, 58, 58, 0.12);
  color: var(--danger, #c03a3a);
}

.confirm-icon--accent {
  background: var(--accent-soft, rgba(161, 0, 19, 0.3));
  color: var(--accent, #a10013);
}

.confirm-title {
  margin: 0 0 var(--space-2, 0.5rem);
  font-size: 1.2rem;
  font-weight: 800;
  color: var(--text-primary, #1a202c);
}

.confirm-text {
  margin: 0 0 var(--space-5, 1.5rem);
  color: var(--gray-700, #4a5568);
  font-size: 0.95rem;
  line-height: 1.6;
  word-break: break-word;
}

.confirm-actions {
  display: flex;
  gap: var(--space-3, 0.75rem);
}

.confirm-btn {
  flex: 1;
  padding: 11px 20px;
  border: 1px solid transparent;
  border-radius: var(--radius-pill, 999px);
  font-size: 0.92rem;
  font-weight: 700;
  font-family: inherit;
  cursor: pointer;
  transition: background 0.2s ease, transform 0.2s ease;
}

.confirm-btn:focus-visible {
  outline: none;
  box-shadow: var(--focus-ring, 0 0 0 3px rgba(161, 0, 19, 0.35));
}

.confirm-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.confirm-btn--ghost {
  background: transparent;
  border-color: var(--border, #e2e8f0);
  color: var(--text-primary, #1a202c);
}

.confirm-btn--ghost:hover {
  background: var(--gray-100, #f1f5f9);
}

.confirm-btn--danger {
  background: var(--danger, #c03a3a);
  color: #fff;
}

.confirm-btn--danger:hover:not(:disabled) {
  background: #9d2f2f;
  transform: translateY(-2px);
}

.confirm-btn--accent {
  background: var(--accent, #a10013);
  color: #fff;
}

.confirm-btn--accent:hover:not(:disabled) {
  background: var(--accent-strong, #7a000e);
  transform: translateY(-2px);
}

.confirm-enter-active,
.confirm-leave-active {
  transition: opacity 0.18s ease;
}

.confirm-enter-active .confirm-box,
.confirm-leave-active .confirm-box {
  transition: transform 0.18s ease;
}

.confirm-enter-from,
.confirm-leave-to {
  opacity: 0;
}

.confirm-enter-from .confirm-box,
.confirm-leave-to .confirm-box {
  transform: scale(0.94);
}

@media (prefers-reduced-motion: reduce) {

  .confirm-enter-active,
  .confirm-leave-active,
  .confirm-enter-active .confirm-box,
  .confirm-leave-active .confirm-box {
    transition: none;
  }

  .confirm-enter-from .confirm-box,
  .confirm-leave-to .confirm-box {
    transform: none;
  }
}
</style>
