<template>
  <div class="app-container">
    <VerificaCorreo v-if="user && !user.email_verified_at && !isLoginOrHome" :user="user" />
    <div v-else-if="verificationStatus" class="verification-message" :class="verificationStatus.type">
      <h2>{{ verificationStatus.title }}</h2>
      <p>{{ verificationStatus.message }}</p>
      <pre v-if="verificationStatus.raw" style="text-align:left; background:#f3f4f6; color:#334155; padding:10px; border-radius:6px; font-size:13px; overflow-x:auto;">{{ verificationStatus.raw }}</pre>
      <router-link v-if="verificationStatus.type==='success'" to="/login">Iniciar sesión</router-link>
    </div>
    <router-view v-else-if="user || isPublicRoute($route)" />
  </div>
</template>

<script>
import axios from 'axios';
import ProductModal from '../components/CarritoComponent.vue';
import VerificaCorreo from './VerificaCorreo.vue';

export default {
  components: {
    ProductModal
  },
  name: 'App',
  components: { VerificaCorreo },
  data() {
    return {
      user: null,
      isLoginOrHome: false,
      publicRoutes: ['/', '/signup', '/login'],
      verificationStatus: null
    };
  },
  watch: {
    '$route'(to) {
      this.checkRoute(to);
      // Recargar el usuario desde sessionStorage en cada cambio de ruta
      const storedUser = sessionStorage.getItem('user');
      if (storedUser) {
        try {
          this.user = JSON.parse(storedUser);
        } catch (e) {
          sessionStorage.removeItem('user');
          this.user = null;
        }
      } else {
        this.user = null;
      }
      // Redirigir a / si intenta acceder a ruta protegida sin estar logeado y no es pública
      if (!this.user && !this.isPublicRoute(to)) {
        this.$router.replace('/');
      }
      // Limpiar mensaje de verificación si cambia de ruta
      this.verificationStatus = null;
      // Si es ruta de verificación, intentar verificar
      if (this.isVerifyRoute(to)) {
        this.handleEmailVerification(to);
      }
    }
  },
  created() {
    this.checkRoute(this.$route);
    // Inicializar user desde sessionStorage inmediatamente
    const storedUser = sessionStorage.getItem('user');
    if (storedUser) {
      try {
        this.user = JSON.parse(storedUser);
      } catch (e) {
        sessionStorage.removeItem('user');
        this.user = null;
      }
    }
    // Si entra directo a la ruta de verificación
    if (this.isVerifyRoute(this.$route)) {
      this.handleEmailVerification(this.$route);
    }
  },
  methods: {
    checkRoute(route) {
      const loginPaths = ['/', '/signUp', '/signup'];
      this.isLoginOrHome = loginPaths.includes(route.path);
    },
    isPublicRoute(route) {
      if (this.publicRoutes.includes(route.path)) return true;
      const verifyRegex = /^\/email\/verify\/\d+\/[^/]+$/;
      return verifyRegex.test(route.path);
    },
    isVerifyRoute(route) {
      // Detectar /email/verify/:id/:hash
      const verifyRegex = /^\/email\/verify\/(\d+)\/([^/]+)$/;
      return verifyRegex.test(route.path);
    },
    async handleEmailVerification(route) {
      // Extraer id y hash de la ruta
      const match = route.path.match(/^\/email\/verify\/(\d+)\/([^/]+)$/);
      if (!match) return;
      const [ , id, hash ] = match;
      try {
        const response = await axios.get(`/api/email/verify/${id}/${hash}`);
        this.verificationStatus = {
          type: 'success',
          title: '¡Correo verificado!',
          message: response.data?.message || 'Tu correo electrónico ha sido verificado correctamente. Ahora puedes iniciar sesión.',
          raw: JSON.stringify(response.data, null, 2)
        };
        // Obtener usuario actualizado tras verificar
        const userResp = await axios.get(`/api/user/${id}`);
        this.user = userResp.data;
        sessionStorage.setItem('user', JSON.stringify(this.user));
      } catch (error) {
        this.verificationStatus = {
          type: 'error',
          title: 'Error de verificación',
          message: error?.response?.data?.message || 'No se pudo verificar el correo. El enlace puede haber expirado o ya fue usado.',
          raw: error?.response ? JSON.stringify(error.response.data, null, 2) : String(error)
        };
      }
    }
  },
  mounted() {
    axios.defaults.withCredentials = true;
  }
};
</script>

<style lang="scss">
/* Base Styles */
:root {
  --font-primary: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
  --font-size-base: 14px;
  --line-height-base: 1.6;
}

body {
  font-family: var(--font-primary);
  margin: 0;
  padding: 0;
  line-height: var(--line-height-base);
  font-size: var(--font-size-base);
  -webkit-text-size-adjust: 100%;
}

.app-container {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  width: 100%;
  max-width: 100vw;
  overflow-x: hidden;
}

/* Verification Message Styles */
.verification-message {
  max-width: 400px;
  margin: 40px auto;
  padding: 2rem 1.5rem;
  border-radius: 8px;
  background: #f8fafc;
  box-shadow: 0 2px 8px rgba(0,0,0,0.07);
  text-align: center;
  h2 {
    margin-bottom: 0.5rem;
  }
  &.success {
    border: 1.5px solid #22c55e;
    color: #166534;
    background: #f0fdf4;
  }
  &.error {
    border: 1.5px solid #ef4444;
    color: #991b1b;
    background: #fef2f2;
  }
}

/* Responsive Breakpoints */
@media (max-width: 480px) {
  :root {
    --font-size-base: 13px;
  }

  /* Typography */
  h1 {
    font-size: 1.6rem;
  }

  h2 {
    font-size: 1.4rem;
  }

  h3 {
    font-size: 1.2rem;
  }

  /* Form Elements */
  input,
  textarea,
  select,
  button,
  .btn {
    font-size: 0.9rem;
    padding: 8px 12px;
    min-height: 36px;
  }

  /* Layout Utilities */
  .container,
  .content-wrapper {
    padding: 0 12px;
  }

  .grid-container {
    grid-template-columns: 1fr;
  }

  .flex-container {
    flex-direction: column;
  }
}
</style>