<template>
  <div>
    <nav class="main-nav">
      <div class="nav-container">
      
      </div>

    </nav>

    <router-view></router-view>
  </div>
</template>

<script>
export default {
  data() {
    return {
      user: null
    }
  },
  async created() {
    await this.loadUser();
  },
  methods: {
    async loadUser() {
      try {
        // Intenta cargar usuario desde localStorage
        const storedUser = localStorage.getItem('user');
        if (storedUser) {
          this.user = JSON.parse(storedUser);
        }
        
        // Verifica con el backend
        const response = await this.$axios.get('/current-user');
        this.user = response.data;
        localStorage.setItem('user', JSON.stringify(response.data));
      } catch (error) {
        // Si hay error, limpia los datos
        this.user = null;
        localStorage.removeItem('user');
      }
    },
    async logout() {
      try {
        await this.$axios.post('/logout');
        this.user = null;
        localStorage.removeItem('user');
        this.$router.push('/');
      } catch (error) {
        console.error('Error al cerrar sesión:', error);
      }
    }
  }
}
</script>

<style lang="scss">


</style>