<template>
  <div>

    <router-view></router-view>
  </div>
</template>

<script>
import axios from 'axios';
export default {

  data() {
    return {
      user: '',
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
    logout() {
      axios.post('/logout')
      .then(response => {
        console.log('Logout successful:', response.data);
        this.user = null;
        sessionStorage.removeItem('user');
        this.$router.push('/');

      }).catch((error) => {
        console.log(error);
        console.error('Error al cerrar sesión:', error);
      });
    }

  }
}
</script>

<style lang="scss">


</style>