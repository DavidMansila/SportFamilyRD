<template>
  <div class="foro-page">

    <!-- Navbar -->
    <nav class="navbar">
      <div class="logo-container">
        <a href="/" class="logo-container">
          <img src="/imagenes/logo2.png" alt="SportFamilyRD Logo" class="logo"/>
        </a>

      </div>
      <div class="nav-links">
        <a href="/Noticias" class="nav-link">Noticias</a>
        <a href="/Calendario" class="nav-link">Calendario</a>
        <a href="/Tienda" class="nav-link">Tienda</a>
        <a href="/Entrenadores" class="nav-link">Entrenadores</a>
        <a href="/Foro" class="nav-link">Foro</a>
      </div>
      <div class="auth-buttons">
        <a href="/Ajustes">
          <button class="auth-btn">Ajustes</button>
        </a>
        <a href="/Login">
          <button class="auth-btn">Login</button>
        </a>
      </div>
    </nav>

    <!-- Título del Foro -->
    <h1 class="titulo-foro">Foro de Discusión</h1>

    <!-- Sección de Posts -->
    <div class="posts-container">
      <div
        v-for="post in posts"
        :key="post.id"
        class="post-card"
      >
        <div class="post-header">
          <h3 class="post-titulo">{{ post.titulo }}</h3>
          <span class="post-date">Publicado el {{ post.fecha }}</span>
        </div>
        <p class="post-contenido">{{ post.contenido }}</p>
        <div class="post-footer">
          <a href="/Publicacion" class="btn-leer">Leer más</a>
          <div class="post-stats">
            <span class="post-likes">👍 {{ post.likes }}</span>
            <span class="post-comments">💬 {{ post.comentarios }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Botón de Crear Nuevo Post -->
    <div class="crear-post-container">
      <button @click="crearPost" class="btn-crear-post">Crear un nuevo post</button>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ForoComponent',
  data() {
    return {
      posts: [
        { id: 1, titulo: '¿Quién es tu jugador favorito?', contenido: 'Hablemos de nuestros jugadores favoritos en el fútbol.', fecha: '12/10/2023', likes: 15, comentarios: 8 },
        { id: 2, titulo: 'Consejos para correr más rápido', contenido: '¿Qué ejercicios te ayudan a correr más rápido?', fecha: '10/10/2023', likes: 22, comentarios: 12 },
        { id: 3, titulo: 'Mejores trucos para jugar baloncesto', contenido: 'Comparte tus mejores trucos y técnicas para el baloncesto.', fecha: '08/10/2023', likes: 30, comentarios: 18 },
        { id: 4, titulo: '¿Qué opinas de la inteligencia artificial en el deporte?', contenido: 'Abre un debate sobre el futuro de la IA en los deportes.', fecha: '05/10/2023', likes: 45, comentarios: 25 },
      ],
    };
  },
  methods: {
    crearPost() {
      alert("Crear un nuevo post (aquí iría la lógica para abrir un formulario).");
    },
  },
};
</script>


<style scoped>
@import '../../../scss/Foro/foro.scss';
</style>