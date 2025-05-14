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

  }
}
</script>

<style lang="scss">

body {
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
  margin: 0;
  padding: 0;
  line-height: 1.6;
}

</style>