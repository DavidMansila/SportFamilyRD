<template>
  <div class="noticias-page">
    <!-- NavBar -->
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

    <div class="container">
      <h2 class="page-title">Sports News</h2>

      <!-- Lista de noticias -->
      <div v-for="noticia in noticias" :key="noticia.id" class="noticia-card">
        <div class="noticia-image">
          <img src="https://th.bing.com/th/id/OIP.SwvZPcCkze8R1IQhvfhhDQHaDF?w=202&h=84&c=7&r=0&o=5&dpr=1.5&pid=1.7" alt="Imagen de noticia" class="image" />
        </div>
        <div class="noticia-content">
          <h3 class="noticia-title">{{ noticia.title }}</h3>
          <p class="noticia-description">{{ noticia.content }}</p>
          <p class="noticia-source">{{ noticia.source }} · {{ noticia.published_at }} min read</p>
          <button @click="abrirNoticia(noticia)" class="read-more">Read more</button>
        </div>
      </div>
    </div>

    <!-- Pop-up de noticia completa -->
    <div v-if="noticiaSeleccionada" class="popup-overlay" @click="cerrarNoticia">
      <div class="popup-content" @click.stop>
        <button class="btn-cerrar" @click="cerrarNoticia">×</button>
        <img :src="noticiaSeleccionada.imagen" alt="Imagen de noticia" class="popup-imagen" />
        <div class="popup-info">
          <h3 class="popup-titulo">{{ noticiaSeleccionada.titulo }}</h3>
          <p class="popup-descripcion">{{ noticiaSeleccionada.descripcion }}</p>
          <p class="popup-fuente">{{ noticiaSeleccionada.fuente }} · {{ noticiaSeleccionada.tiempo }} min read</p>
        </div>
      </div>
    </div>
  </div>
  
</template>




<script>
import axios from 'axios';

export default {
  name: 'NoticiasComponent', // Nombre del componente

  data() {
    return {
      noticias: [], // Lista de noticias (vacía inicialmente)
      isLoading: false, // Estado de carga
      errorMessage: '', // Mensaje de error
      noticiaSeleccionada: null, // Noticia seleccionada para el pop-up
    };
  },
  methods: {
    abrirNoticia(noticia) {
      this.noticiaSeleccionada = noticia; // Abre el pop-up con la noticia seleccionada
    },
    cerrarNoticia() {
      this.noticiaSeleccionada = null; // Cierra el pop-up
    },
    async fetchNews() {
      this.isLoading = true; 
      this.errorMessage = ''; 

      axios.get('news')
      .then((response) => {
        console.log('Datos de noticias:', response.data);
        this.noticias = response.data.news;
      })
      .catch((error) => {
        console.error('Error al obtener las noticias:', error);
        this.errorMessage = 'Algo salió mal al cargar las noticias. Por favor, intenta de nuevo más tarde.'; // Mostrar mensaje de error
      })
      .finally(() => {
        this.isLoading = false;
      });
    },
    noticiasScrape(){
      axios.get('/scrape')
      .then((response) => {
        console.log('Datos de noticias:', response.data);
        this.noticias = response.data.news;
      })
      .catch((error) => {
        console.error('Error al obtener las noticias:', error);
        this.errorMessage = 'Algo salió mal al cargar las noticias. Por favor, intenta de nuevo más tarde.'; // Mostrar mensaje de error
      })
      .finally(() => {
        this.isLoading = false;
      });
    },
  },
  mounted() {
    this.noticiasScrape(); 
    this.fetchNews(); 
  },
};
</script>





<style scoped>
/* ------------------- ESTILOS DEL NAVBAR ------------------- */
.navbar {
  background: linear-gradient(to right, #000000, #007BFF);
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
  flex-direction: row;
}

.logo-container h1 {
  font-size: 2rem;
  font-weight: bold;
  color: rgb(255, 255, 255);
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

/* ------------------- ESTILOS DE NOTICIAS ------------------ */
.noticias-page {
  font-family: 'Inter', sans-serif;
  background-color: #f8f9fa;
  min-height: 100vh;
  padding-bottom: 40px;
}

.container {
  max-width: 1100px;
  margin: 0 auto;
  padding: 20px;
}

.page-title {
  font-size: 2.5rem;
  color: #333;
  margin-bottom: 30px;
  font-weight: bold;
  text-align: left;
}

.noticia-card {
  display: flex;
  background: #fff;
  border-radius: 15px;
  margin: 20px 0;
  padding: 20px;
  box-shadow: 0px 8px 16px rgba(0, 0, 0, 0.1);
  transition: transform 0.3s ease-in-out;
}

.noticia-card:hover {
  transform: translateY(-5px);
  box-shadow: 0px 12px 20px rgba(0, 0, 0, 0.2);
}

.noticia-image {
  flex: 1;
  max-width: 250px;
  margin-right: 20px;
}

.image {
  width: 100%;
  height: auto;
  border-radius: 10px;
  object-fit: cover;
}

.noticia-content {
  flex: 2;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}

.noticia-title {
  font-size: 1.6rem;
  color: #222;
  font-weight: bold;
  margin-bottom: 10px;
}

.noticia-description {
  font-size: 1.1rem;
  color: #555;
  margin-bottom: 15px;
}

.noticia-source {
  font-size: 0.9rem;
  color: #777;
  margin-bottom: 15px;
}

.read-more {
  align-self: flex-start;
  padding: 10px 15px;
  background: #007bff;
  color: white;
  font-weight: bold;
  text-decoration: none;
  border-radius: 8px;
  transition: background 0.3s;
  cursor: pointer;
  border: none;
}

.read-more:hover {
  background: #0056b3;
}

/* ------------------- ESTILOS DEL POP-UP ------------------ */
.popup-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0, 0, 0, 0.7);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.popup-content {
  background-color: #fff;
  border-radius: 15px;
  padding: 2rem;
  max-width: 800px;
  width: 90%;
  position: relative;
  box-shadow: 0px 8px 16px rgba(0, 0, 0, 0.2);
}

.btn-cerrar {
  position: absolute;
  top: 1rem;
  right: 1rem;
  background: none;
  border: none;
  font-size: 1.5rem;
  cursor: pointer;
  color: #333;
}

.popup-imagen {
  width: 100%;
  max-height: 400px;
  object-fit: cover;
  border-radius: 10px;
  margin-bottom: 1.5rem;
}

.popup-info {
  text-align: left;
}

.popup-titulo {
  font-size: 2rem;
  color: #222;
  margin-bottom: 1rem;
  font-weight: bold;
}

.popup-descripcion {
  font-size: 1.2rem;
  color: #555;
  margin-bottom: 1.5rem;
  line-height: 1.6;
}

.popup-fuente {
  font-size: 1rem;
  color: #777;
}

/* Responsive Design */
@media (max-width: 768px) {
  .navbar {
    flex-direction: column;
    text-align: center;
  }

  .nav-links {
    flex-direction: column;
    gap: 10px;
  }

  .auth-buttons {
    margin-top: 10px;
  }

  .noticia-card {
    flex-direction: column;
    text-align: center;
  }

  .noticia-image {
    max-width: 100%;
    margin-right: 0;
    margin-bottom: 15px;
  }

  .read-more {
    align-self: center;
  }

  .popup-content {
    padding: 1rem;
  }

  .popup-titulo {
    font-size: 1.5rem;
  }

  .popup-descripcion {
    font-size: 1rem;
  }
}
</style>