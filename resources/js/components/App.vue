<template>
  <div class="app-container">
    <VerificaCorreo v-if="user && !user.email_verified_at && !isLoginOrHome" :user="user" />
    <router-view v-else-if="user || publicRoutes.includes($route.path)" />
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
      // Redirigir a / si intenta acceder a ruta protegida sin estar logeado
      if (!this.user && !this.publicRoutes.includes(to.path)) {
        this.$router.replace('/');
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
  },
  methods: {
    checkRoute(route) {
      const loginPaths = ['/', '/signUp', '/signup'];
      this.isLoginOrHome = loginPaths.includes(route.path);
    }
  },
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