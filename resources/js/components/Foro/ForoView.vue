<template>
  <div class="foro-page">

    <!-- Navbar -->
    <nav class="navbar">
      <div class="logo-container">
        <a href="/" class="logo-container">
          <img src="/imagenes/logo.png" alt="SportFamilyRD Logo" class="logo"/>
        </a>
        <h1>SportFamilyRD</h1>
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
    <h2 class="titulo-foro">Foro de Discusión</h2>

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
body {
    font-family: Arial, sans-serif;
    margin: 0;
    padding: 0;
  }


  .logo-container {
    display: flex;
    gap: 1rem;
    flex-direction: row;

    h1 {
    font-size: 2rem;
    font-weight: bold;
    color: rgb(255, 255, 255);
  }
  }
  
  .container {
    margin: 0 auto;
  }

  

/* Navbar */
.navbar {
  background: linear-gradient(to right, #000000, #6a11cb);
  padding: 1rem 2rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.logo {
  width: 50px;
  height: 50px;
}

.logo-container {
  display: flex;
  gap: 1rem;
  align-items: center;
}

.logo-container h1 {
  font-size: 2rem;
  font-weight: bold;
  color: white;
  margin: 0;
}

.nav-links {
  display: flex;
  gap: 2rem;
}

.nav-link {
  color: white;
  text-decoration: none;
  font-size: 1.1rem;
  font-weight: 500;
  transition: color 0.3s ease-in-out;
}

.nav-link:hover {
  color: #fbbf24;
}

.auth-buttons {
  display: flex;
  gap: 1rem;
}

.auth-btn {
  background: transparent;
  border: 2px solid white;
  color: white;
  padding: 0.5rem 1.2rem;
  font-size: 1rem;
  font-weight: bold;
  border-radius: 0.5rem;
  cursor: pointer;
  transition: all 0.3s ease-in-out;
}

.auth-btn:hover {
  background-color: white;
  color: #6a11cb;
}


/* Estilos Generales del Foro */
.titulo-foro {
  font-size: 2.5rem;
  color: #333;
  margin-bottom: 40px;
  font-weight: 600;
  text-align: center;
  padding-top: 30px;
}

/* Contenedor de los posts */
.posts-container {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 30px;
  max-width: 1200px;
  margin: 0 auto;
  padding: 20px;
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

.post-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 15px;
}

.post-titulo {
  font-size: 1.6rem;
  font-weight: bold;
  color: #000000;
  margin: 0;
}

.post-date {
  font-size: 0.9rem;
  color: #777;
}

.post-contenido {
  font-size: 1.1rem;
  color: #555;
  margin-bottom: 20px;
  text-align: justify;
  line-height: 1.6;
}

.post-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.btn-leer {
  color: #7b00ff;
  text-decoration: none;
  font-weight: bold;
  font-size: 1.1rem;
  border-bottom: 2px solid #000000;
  transition: color 0.3s ease, border-color 0.3s ease;
}

.btn-leer:hover {
  color: #0008ff;
}

.post-stats {
  display: flex;
  gap: 10px;
}

.post-likes, .post-comments {
  font-size: 0.9rem;
  color: #777;
}

/* Botón para crear un nuevo post */
.crear-post-container {
  text-align: center;
  margin-top: 50px;
}

.btn-crear-post {
  background-color: #6a11cb;
  color: white;
  font-size: 1.2rem;
  padding: 12px 30px;
  border-radius: 30px;
  border: none;
  cursor: pointer;
  transition: background-color 0.3s ease;
}

.btn-crear-post:hover {
  background-color: #4a0d9b;
}

/* Estilos Responsivos */
@media (max-width: 768px) {
  .posts-container {
    grid-template-columns: 1fr;
  }

  .navbar {
    flex-direction: column;
    align-items: flex-start;
    padding: 1rem;
  }

  .nav-links {
    flex-direction: column;
    gap: 1rem;
    margin-top: 1rem;
  }

  .auth-buttons {
    margin-top: 1rem;
  }
}
</style>