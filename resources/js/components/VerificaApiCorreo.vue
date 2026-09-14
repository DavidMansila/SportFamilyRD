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
    // Sin user_id: el backend reenvia al usuario autenticado y solo a ese. El
    // token lo agrega el interceptor de resources/js/bootstrap.js.
    await axios.post('/email/verification-notification');
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
    // La URL se arma relativa a la baseURL de axios ("/api"): antes empezaba
    // por "/api/..." y axios la concatenaba, saliendo "/api/api/email/verify/..."
    // -un 404 seguro-, asi que esta pantalla nunca llego a verificar nada.
    //
    // Se reenvia la query string ORIGINAL intacta (expires + signature) y no se
    // le agrega nada: el endpoint valida la firma sobre la URL completa, asi que
    // cualquier parametro de mas la invalida. El user_id que se anadia aqui
    // sobraba -el usuario sale del {id} de la ruta, que va dentro de la firma-.
    const url = `/email/verify/${id}/${hash}${window.location.search}`;
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
