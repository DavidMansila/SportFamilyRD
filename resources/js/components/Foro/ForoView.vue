<template>
  <div class="foro-page">
    <h1 class="titulo-foro">Foro de Discusión</h1>

    <!-- Sección de Posts -->
    <div class="posts-container">
      <div
        v-for="post in posts"
        :key="post.id"
        class="post-card"
      >
        <h3 class="post-titulo">{{ post.titulo }}</h3>
        <p class="post-contenido">{{ post.contenido }}</p>
        <a :href="'/foro/post/' + post.id" class="btn-leer">Leer más</a>
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
        { id: 1, titulo: '¿Quién es tu jugador favorito?', contenido: 'Hablemos de nuestros jugadores favoritos en el fútbol.' },
        { id: 2, titulo: 'Consejos para correr más rápido', contenido: '¿Qué ejercicios te ayudan a correr más rápido?' },
        { id: 3, titulo: 'Mejores trucos para jugar baloncesto', contenido: 'Comparte tus mejores trucos y técnicas para el baloncesto.' },
        { id: 4, titulo: '¿Qué opinas de la inteligencia artificial en el deporte?', contenido: 'Abre un debate sobre el futuro de la IA en los deportes.' },
      ]
    };
  },
  methods: {
    crearPost() {
      alert("Crear un nuevo post (aquí iría la lógica para abrir un formulario).");
    }
  }
};
</script>

<style scoped>
/* Estilos Generales del Foro */
.foro-page {
  background-color: #f9f9f9;
  padding: 40px 20px;
  font-family: 'Arial', sans-serif;
}

.titulo-foro {
  font-size: 2.5rem;
  color: #333;
  margin-bottom: 40px;
  font-weight: 600;
  text-align: center;
}

/* Contenedor de los posts */
.posts-container {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 30px;
  max-width: 1200px;
  margin: 0 auto;
}

/* Estilo para cada tarjeta de post */
.post-card {
  background-color: #fff;
  padding: 25px;
  border-radius: 15px;
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
  transition: transform 0.3s ease, box-shadow 0.3s ease;
  cursor: pointer;
}

.post-card:hover {
  transform: translateY(-10px);
  box-shadow: 0 12px 30px rgba(0, 0, 0, 0.2);
}

/* Título de cada post */
.post-titulo {
  font-size: 1.6rem;
  font-weight: bold;
  color: #007bff;
  margin-bottom: 15px;
}

/* Contenido del post */
.post-contenido {
  font-size: 1.1rem;
  color: #555;
  margin-bottom: 20px;
  text-align: justify;
  line-height: 1.6;
}

/* Enlace "Leer más" */
.btn-leer {
  color: #007bff;
  text-decoration: none;
  font-weight: bold;
  font-size: 1.1rem;
  border-bottom: 2px solid #007bff;
  transition: color 0.3s ease, border-color 0.3s ease;
}

.btn-leer:hover {
  color: #0056b3;
  border-color: #0056b3;
}

/* Botón para crear un nuevo post */
.crear-post-container {
  text-align: center;
  margin-top: 50px;
}

.btn-crear-post {
  background-color: #28a745;
  color: white;
  font-size: 1.2rem;
  padding: 12px 30px;
  border-radius: 30px;
  border: none;
  cursor: pointer;
  transition: background-color 0.3s ease;
}

.btn-crear-post:hover {
  background-color: #218838;
}

/* Estilos Responsivos */
@media (max-width: 768px) {
  .posts-container {
    grid-template-columns: 1fr;
  }
}
</style>
