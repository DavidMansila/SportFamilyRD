<template>
  <div class="noticias-page">
    <!-- NavBar -->
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

    <div class="container">
      <h2 class="page-title">Sports News</h2>

   <!-- Filtro de deportes con botones -->
   <div class="filtro-deportes">
        <button
          v-for="deporte in deportes"
          :key="deporte.value"
          @click="deporteSeleccionado = deporte.value"
          :class="['filtro-btn', { active: deporteSeleccionado === deporte.value }]"
        >
          {{ deporte.label }}
        </button>
      </div>

      <!-- Lista de noticias -->
      <div v-for="noticia in noticias" :key="noticia.id" class="noticia-card">
        <div class="noticia-image">
          <img src="https://th.bing.com/th/id/OIP.SwvZPcCkze8R1IQhvfhhDQHaDF?w=202&h=84&c=7&r=0&o=5&dpr=1.5&pid=1.7" alt="Imagen de noticia" class="image" />
        </div>
        <div class="noticia-content">
          <h3 class="noticia-title">{{ noticia.title }}</h3>
          <p class="noticia-subtitle">{{ noticia.subtitle }}</p>
          <p class="noticia-author">{{ noticia.author }}</p>
          <button @click="abrirNoticia(noticia)" class="read-more">Read more</button>
        </div>
      </div>
    </div>
    

    <!-- Pop-up de noticia completa -->
    <div v-if="noticiaSeleccionada" class="popup-overlay" @click="cerrarNoticia">
      <div class="popup-content" @click.stop>
        <button class="btn-cerrar" @click="cerrarNoticia">×</button>
        <img src="https://th.bing.com/th/id/OIP.SwvZPcCkze8R1IQhvfhhDQHaDF?w=202&h=84&c=7&r=0&o=5&dpr=1.5&pid=1.7" alt="Imagen de noticia" class="image" />
        <div class="popup-info">
          <h3 class="popup-titulo">{{ noticiaSeleccionada.title }}</h3>
          <p class="popup-descripcion">{{ noticiaSeleccionada.description }}</p>
          <p class="popup-fuente">{{ noticiaSeleccionada.source }}</p>
        </div>
      </div>
    </div>
  </div>
</template>


<script>
import axios from 'axios';

export default {
  name: 'NoticiasComponent',

  data() {
    return {
      noticias: [], // Lista completa de noticias
      isLoading: false,
      errorMessage: '',
      noticiaSeleccionada: null,
      deporteSeleccionado: 'todos', // Deporte seleccionado (valor inicial: 'todos')
      deportes: [
        { value: 'todos', label: 'Todos' },
        { value: 'futbol', label: 'Fútbol' },
        { value: 'baloncesto', label: 'Baloncesto' },
        { value: 'tenis', label: 'Tenis' },
        { value: 'beisbol', label: 'Béisbol' },
        { value: 'natacion', label: 'Natación' },
        { value: 'voleyball', label: 'Voleyball' },
      ],
    };
  },

  computed: {
    // Filtra las noticias según el deporte seleccionado
    noticiasFiltradas() {
      if (this.deporteSeleccionado === 'todos') {
        return this.noticias; // Mostrar todas las noticias
      } else {
        return this.noticias.filter(noticia => noticia.categoria === this.deporteSeleccionado);
      }
    },
  },

  methods: {
    abrirNoticia(noticia) {
      this.noticiaSeleccionada = noticia;
    },

    cerrarNoticia() {
      this.noticiaSeleccionada = null;
    },

    async fetchNews() {
      this.isLoading = true;
      this.errorMessage = '';

      try {
        const response = await axios.get('news');
        console.log('Datos de noticias:', response.data);
        this.noticias = response.data.news;
      } catch (error) {
        console.error('Error al obtener las noticias:', error);
        this.errorMessage = 'Algo salió mal al cargar las noticias. Por favor, intenta de nuevo más tarde.';
      } finally {
        this.isLoading = false;
      }
    },

    async noticiasScrape() {
      try {
        const response = await axios.get('/scrape');
        console.log('Datos de noticias:', response.data);
        this.noticias = response.data.news;
      } catch (error) {
        console.error('Error al obtener las noticias:', error);
        this.errorMessage = 'Algo salió mal al cargar las noticias. Por favor, intenta de nuevo más tarde.';
      } finally {
        this.isLoading = false;
      }
    },
  },

  mounted() {
    this.fetchNews();
    this.noticiasScrape();
  },
};
</script>


<style scoped>
@import '../../../scss/Noticias/noticias.scss';
</style>