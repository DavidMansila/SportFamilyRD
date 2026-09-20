<template>
  <div class="verifica-correo" style="display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 60vh;">
    <h2>Verifica tu correo electrónico</h2>
    <p>Hemos enviado un enlace de verificación a <b>{{ user.email }}</b>. Por favor, revisa tu bandeja de entrada y haz clic en el enlace para activar tu cuenta.</p>
    <p>Si no lo ves en un minuto, revisa la carpeta de spam o correo no deseado.</p>
    <!-- <p>Para tu seguridad, solo puedes verificar tu cuenta usando el enlace enviado a tu correo electrónico.</p> -->
    <p v-if="envioFallido" style="color: #c03a3a;">
      No pudimos enviar el correo al crear tu cuenta. Pulsa «Reenviar» para intentarlo otra vez.
    </p>
    <p v-if="reenviado" style="color: green;">¡Correo de verificación reenviado!</p>
    <button @click="reenviarCorreo" :disabled="reenviando" style="margin-top: 1rem;">
      <span v-if="reenviando">Enviando...</span>
      <span v-else>Reenviar correo de verificación</span>
    </button>
    <button @click="logout" style="margin-top: 1rem; background:#ef4444;">Cerrar sesión</button>
  </div>

  <Alert
    v-if="openModal"
    :key="alertKey"
    :message="alertMessage"
    :type="alertType"
    @closed="openModal = null"
  />
</template>

<script setup>
import { ref } from 'vue';
import axios from 'axios';
import Alert from './Alert.vue';


const props = defineProps({
  user: { type: Object, required: true }
});
const emit = defineEmits(['logout']);

const reenviando = ref(false);
const reenviado = ref(false);

// La marca la deja SignUpView cuando el registro devuelve
// verification_email_sent === false (la cuenta se creo, pero el correo no
// llego a salir). Se limpia en cuanto un reenvio funciona.
const envioFallido = ref(sessionStorage.getItem('verificationEmailFailed') === '1');

const alertMessage = ref('');
const alertType = ref('');
const alertKey = ref(0);
const openModal = ref(false);

async function reenviarCorreo() {
  reenviando.value = true;
  reenviado.value = false;
  try {
    // Sin user_id: el backend reenvia al usuario autenticado y solo a ese. El
    // token lo agrega el interceptor de resources/js/bootstrap.js.
    await axios.post('/email/verification-notification');
    reenviado.value = true;
    envioFallido.value = false;
    sessionStorage.removeItem('verificationEmailFailed');
  } catch (e) {
    // Se muestra el mensaje que manda el backend, no uno generico: distingue
    // "el servicio de correo no esta configurado" (503) de "has pedido
    // demasiados reenvios" (429, el limite es de 5 por hora), y esas dos cosas
    // se resuelven de forma muy distinta.
    alertType.value = 'error';
    alertMessage.value = e.response?.status === 429
      ? 'Has pedido demasiados reenvíos. Espera un rato antes de volver a intentarlo.'
      : (e.response?.data?.message || 'Error al reenviar el correo.');
    openModal.value = true;

  }
  reenviando.value = false;
}

function logout() {
  emit('logout');
}
</script>

<style lang="scss" scoped>
.verifica-correo {
  background: #f8fafc;
  border-radius: 16px;
  box-shadow: 0 4px 24px rgba(0,0,0,0.10);
  padding: 2.5rem 2rem 2rem 2rem;
  max-width: 420px;
  margin: 3rem auto;
  display: flex;
  flex-direction: column;
  align-items: center;
  border: 1px solid #e2e8f0;

  h2 {
    color: #2563eb;
    font-size: 2rem;
    margin-bottom: 1rem;
    font-weight: 700;
    text-align: center;
  }

  p {
    color: #334155;
    font-size: 1.1rem;
    margin-bottom: 1.2rem;
    text-align: center;
    &.reenviado {
      color: green;
    }
    &.info {
      color: #64748b;
      margin-bottom: 1.2rem;
    }
  }

  button {
    background: linear-gradient(90deg, #2563eb 0%, #38bdf8 100%);
    color: #fff;
    border: none;
    border-radius: 8px;
    padding: 0.7rem 1.5rem;
    font-size: 1rem;
    font-weight: 600;
    margin-top: 0.5rem;
    cursor: pointer;
    transition: background 0.2s, box-shadow 0.2s;
    box-shadow: 0 2px 8px rgba(56,189,248,0.08);
    &:disabled {
      background: #cbd5e1;
      color: #64748b;
      cursor: not-allowed;
    }
    & + button {
      margin-left: 0.5rem;
    }
  }

  b {
    color: #0ea5e9;
  }
}
</style>
