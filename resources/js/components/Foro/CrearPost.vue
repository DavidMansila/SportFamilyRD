<template>
    <div class="foro-page">
      <h1>Foro de Discusión</h1>
      <!-- Botón para crear un nuevo post -->
      <router-link to="/foro/crear" class="btn-crear-post">Crear un Nuevo Post</router-link>
      
      <!-- Lista de los posts -->
      <div v-for="post in posts" :key="post.id" class="post-card">
        <h3>{{ post.titulo }}</h3>
        <p>{{ post.contenido }}</p>
        <a :href="'/foro/post/' + post.id">Leer más</a>
      </div>
    </div>
  </template>
  
  <script>
  export default {
    name: 'ForoComponent',
    data() {
      return {
        posts: [
          { id: 1, titulo: '¿Quién es tu jugador favorito?', contenido: 'Hablemos de nuestros jugadores favoritos en el fútbol.' },
          { id: 2, titulo: 'Consejos para correr más rápido', contenido: '¿Qué ejercicios te ayudan a correr más rápido?' },
        ],
      };
    },
  };
  </script>
  

  
  <style scoped>
@import '../../../scss/Foro/crearpost.scss';
</style>