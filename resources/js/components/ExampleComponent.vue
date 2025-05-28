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

/* Estilos base responsivos */
body {
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
  margin: 0;
  padding: 0;
  line-height: 1.6;
  font-size: 14px; /* Tamaño base más pequeño para móviles */
  -webkit-text-size-adjust: 100%; /* Evita zoom automático en iOS */
}

.app-container {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  width: 100%;
  max-width: 100vw;
  overflow-x: hidden;
}

/* Media queries para móviles pequeños */
@media only screen and (max-width: 480px) {
  html {
    font-size: 14px;
  }
  
  body {
    font-size: 0.9rem;
  }
  
  /* Asegura que los elementos no excedan el ancho de la pantalla */
  img, video, iframe, table, canvas {
    max-width: 100%;
    height: auto;
  }
  
  /* Contenedores principales */
  .container, .content-wrapper, .page-container {
    width: 100%;
    padding: 0 12px;
    box-sizing: border-box;
  }
  
  /* Ajustes generales para elementos comunes */
  h1 { font-size: 1.6rem; }
  h2 { font-size: 1.4rem; }
  h3 { font-size: 1.2rem; }
  
  /* Botones y elementos interactivos */
  button, .btn, a.button {
    padding: 8px 12px;
    font-size: 0.9rem;
    min-width: auto;
    min-height: 36px;
  }
  
  /* Forms */
  input, textarea, select {
    font-size: 1rem;
    padding: 8px;
  }
  
  /* Grids y layouts */
  .grid-container {
    grid-template-columns: 1fr;
  }
  
  .flex-container {
    flex-direction: column;
  }
  
  /* Margenes y paddings reducidos */
  .section {
    padding: 1rem 0;
  }
  
  .card {
    margin-bottom: 1rem;
  }
}

</style>