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
  /* Estilos para el foro */
  .foro-page {
    text-align: center;
  }
  
  .post-card {
    margin: 10px;
    padding: 15px;
    background: #f4f4f4;
    border-radius: 5px;
  }
  
  .btn-crear-post {
    display: inline-block;
    padding: 10px 20px;
    background-color: #007bff;
    color: white;
    border-radius: 5px;
    margin-bottom: 20px;
    text-decoration: none;
  }
  
  .btn-crear-post:hover {
    background-color: #0056b3;
  }
  </style>
  