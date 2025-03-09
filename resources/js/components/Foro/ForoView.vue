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


    <h2 class="titulo-foro">Foro de Discusión</h2>

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
    background: linear-gradient(to right, #000000, #f4b5ff);
    padding: 1rem 2rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.logo-container {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.logo {
    width: 50px;
    height: 50px;
}

.nav-links {
    display: flex;
    gap: 2rem;
}

.nav-link {
    color: white;
    text-decoration: none;
    font-size: 1.2rem;
    font-weight: bold;
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
    color: #ff3149;
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
