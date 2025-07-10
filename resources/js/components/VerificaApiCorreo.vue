<template>
  <div v-if="!yaVerificado" class="verifica-api">
    <h2>{{ mensaje }}</h2>
    <router-link v-if="verificado" :to="redirectTo">Ir a la página principal</router-link>
    <button v-if="!verificado" @click="reenviarCorreo" style="margin-top:1rem;">Reenviar correo de verificación</button>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';

const route = useRoute();
const router = useRouter();
const mensaje = ref('Verificando correo...');
const verificado = ref(false);
const redirectTo = ref('/');
const yaVerificado = ref(false);

async function reenviarCorreo() {
  try {
    const user = JSON.parse(sessionStorage.getItem('user'));

    await axios.post('/email/verification-notification', {user_id: user.id} );
    mensaje.value = 'Correo de verificación reenviado.';
  } catch (e) {
    mensaje.value = e.response?.data?.message || 'Error al reenviar el correo.';
  }
}

onMounted(async () => {
  // Si el usuario ya está verificado en sessionStorage, dejar pasar
  try {
    const user = JSON.parse(sessionStorage.getItem('user'));
    if (user && user.email_verified_at) {
      yaVerificado.value = true;
      router.replace(route.query.redirect || '/');
      return;
    }
  } catch {}

  const { id, hash } = route.params;
  // Si hay un parámetro redirect en la query, úsalo
  if (route.query.redirect) {
    redirectTo.value = route.query.redirect;
  }
  try {
    // Si no hay user en sessionStorage, intenta obtener user_id de la query
    let user = null;
    let userId = null;
    try {
      user = JSON.parse(sessionStorage.getItem('user'));
      if (user && user.id) userId = user.id;
    } catch {}
    if (!userId && route.query.user_id) {
      userId = route.query.user_id;
    }
    let url = `/api/email/verify/${id}/${hash}`;
    if (userId) {
      url += `?user_id=${userId}`;
    }
    const res = await axios.get(url);
    mensaje.value = res.data.message || 'Correo verificado con éxito.';
    verificado.value = true;
    
    setTimeout(() => {
      router.replace(redirectTo.value);
    }, 2000);
  } catch (e) {
    mensaje.value = e.response?.data?.message || 'Error al verificar el correo.';
  }
});
</script>

<style scoped>
.verifica-api {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  min-height: 60vh;
  background: #fff;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.08);
  padding: 2rem;
  max-width: 400px;
  margin: 2rem auto;
}
</style>
