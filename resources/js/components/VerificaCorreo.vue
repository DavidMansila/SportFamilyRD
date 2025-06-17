<template>
  <div class="verifica-correo" style="display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 60vh;">
    <h2>Verifica tu correo electrónico</h2>
    <p>Hemos enviado un enlace de verificación a <b>{{ user.email }}</b>. Por favor, revisa tu bandeja de entrada y haz clic en el enlace para activar tu cuenta.</p>
    <p>Puedes chequear el spam si el correo no llega de 2-5 segundos.</p>
    <p>Para tu seguridad, solo puedes verificar tu cuenta usando el enlace enviado a tu correo electrónico.</p>
    <p v-if="reenviado" style="color: green;">¡Correo de verificación reenviado!</p>
    <button @click="reenviarCorreo" :disabled="reenviando" style="margin-top: 1rem;">
      <span v-if="reenviando">Enviando...</span>
      <span v-else>Reenviar correo de verificación</span>
    </button>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import axios from 'axios';

const props = defineProps({
  user: { type: Object, required: true }
});

const reenviando = ref(false);
const reenviado = ref(false);

async function reenviarCorreo() {
  reenviando.value = true;
  reenviado.value = false;
  try {
    await axios.post('/email/verification-notification');
    reenviado.value = true;
  } catch (e) {
    alert('Error al reenviar el correo.');
  }
  reenviando.value = false;
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
