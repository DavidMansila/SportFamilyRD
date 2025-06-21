<template>
  <div class="app-container">
    <router-view />
  </div>
</template>

<script>
import axios from 'axios';
import ProductModal from '../components/CarritoComponent.vue';

export default {
  components: {
    ProductModal
  },
  name: 'App',
  data() {
    return {
      user: null,
    };
  },
  async created() {
    // await this.loadUser();
  },
  methods: {
    // async loadUser() {
    //   try {
    //     const storedUser = localStorage.getItem('user');
    //     if (storedUser) {
    //       try {
    //         this.user = JSON.parse(storedUser);
    //       } catch (e) {
    //         console.error('Failed to parse stored user:', e);
    //         localStorage.removeItem('user');
    //       }
    //     }

    //     const response = await axios.get('/current-user');
    //     if (response.data) {
    //       this.user = response.data;
    //       localStorage.setItem('user', JSON.stringify(response.data));
    //     }
    //   } catch (error) {
    //     console.error('Error loading user:', error);
    //     this.handleAuthError(error);
    //   }
    // },

    // handleAuthError(error) {
    //   if (error.response?.status === 401) {
    //     this.user = null;
    //     localStorage.removeItem('user');
    //     this.$router.push('/login');
    //   }
    // },
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