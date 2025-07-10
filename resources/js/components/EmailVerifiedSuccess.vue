<template>
  <div class="verification-message success">
    <h2>¡Correo verificado!</h2>
    <p>Tu correo electrónico ha sido verificado correctamente.<br>
      Por seguridad, debes iniciar sesión de nuevo para continuar usando la app.
      <router-link to="/signup" class="inline-login-btn">Iniciar sesión</router-link>
    </p>
  </div>
</template>

<script>
import axios from 'axios';
export default {
  name: 'EmailVerifiedSuccess',
  async mounted() {
    // Obtener el id de la query
    const params = new URLSearchParams(window.location.search);
    const id = params.get('id');
    console.log('Mounted EmailVerifiedSuccess', id);
    if (id) {
      // Refrescar usuario desde la API
      try {
        const res = await axios.get(`/api/user-by-id/${id}`);
        sessionStorage.setItem('user', JSON.stringify(res.data));
        // Notificar a otras pestañas que el usuario fue verificado
        localStorage.setItem('email_verified', id);
      } catch (e) {
        sessionStorage.removeItem('user');
      }
    }
  }
};
</script>

<style scoped>
.verification-message {
  max-width: 400px;
  margin: 40px auto;
  padding: 2rem 1.5rem;
  border-radius: 8px;
  background: #f8fafc;
  box-shadow: 0 2px 8px rgba(0,0,0,0.07);
  text-align: center;
}
.success {
  border: 1.5px solid #22c55e;
  color: #166534;
  background: #f0fdf4;
}
.inline-login-btn {
  display: inline-block;
  margin-left: 8px;
  padding: 4px 14px;
  font-size: 1em;
  background: #22c55e;
  color: #fff;
  border-radius: 6px;
  text-decoration: none;
  font-weight: 500;
  transition: background 0.18s;
  box-shadow: 0 1px 4px rgba(34,197,94,0.08);
  vertical-align: middle;
}
.inline-login-btn:hover {
  background: #16a34a;
  color: #fff;
}
</style>
