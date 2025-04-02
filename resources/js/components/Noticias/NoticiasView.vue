<template>
  <div class="noticias-page">
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

      <div class="Imagenes">
        <a class="Carrito">
          <img src="/imagenes/Carrito-Icon.png" alt="Carrito" class="carrito-icon"/>
        </a>
        <a href="/Ajustes" class="Ajustes">
          <img src="/imagenes/Ajustes-Icon.png" alt="Ajustes" class="ajustes-icon"/>
        </a>
        <a class="Perfil">
          <img src="/imagenes/Perfil-Icon.png" alt="Perfil" class="perfil-icon"/>
        </a>
        <a :href="login ? '/Login' : '/Logout'" class="Logout">
          <img src="/imagenes/Logout-Icon.png" alt="Logout" class="logout-icon"/>
        </a>
      </div>
    </nav>

    <!-- Contenido principal -->
    <div class="container">
      <h2 class="page-title">Sports News</h2>

      <!-- Filtro de deportes -->
      <div class="filtro-deportes">
        <button
          v-for="deporte in deportes"
          :key="deporte.value"
          @click="cambiarDeporte(deporte.value)"
          :class="['filtro-btn', { active: deporteSeleccionado === deporte.value }]"
        >
          {{ deporte.label }}
        </button>
      </div>

      <!-- Estados de carga -->
      <div v-if="isLoading" class="loading-container">
        <div class="spinner"></div>
        <p class="loading-text">Cargando noticias...</p>
      </div>

      <div v-else-if="errorMessage" class="error-container">
        <div class="error-icon">⚠️</div>
        <p class="error-text">{{ errorMessage }}</p>
        <button @click="cargarNoticias" class="retry-btn">Reintentar</button>
      </div>

      <!-- Lista de noticias -->
      <div v-else>
        <div v-if="paginatedNews.length > 0">
          <div 
            v-for="noticia in paginatedNews" 
            :key="noticia.id" 
            class="noticia-card"
            @click="abrirNoticia(noticia)"
          >
            <div class="noticia-image">
              <img :src="noticia.image" alt="Imagen de noticia" class="image"/>
            </div>
            <div class="noticia-content">
              <h3 class="noticia-title">{{ noticia.title }}</h3>
              <p class="noticia-author">
                <span class="author-name">{{ noticia.author }}</span> · 
                <span class="noticia-date">{{ noticia.date }}</span>
              </p>
              <button class="read-more">Leer más</button>
            </div>
          </div>
        </div>
        <div v-else class="no-news">
          <p>No hay noticias disponibles para esta categoría.</p>
        </div>
      </div>

      <!-- Paginación -->
      <paginatorComponent
        v-model="currentPage"
        :total-items="noticiasFiltradas.length"
        :items-per-page="itemsPerPage"
        :max-pages-shown="5"
      />
    </div>

    <!-- Pop-up de noticia completa -->
    <div 
      v-if="noticiaSeleccionada" 
      class="popup-overlay" 
      @click.self="cerrarNoticia"
    >
      <div class="popup-content">
        <button class="btn-cerrar" @click="cerrarNoticia">×</button>
        <img :src="noticiaSeleccionada.image" alt="Imagen de noticia" class="popup-image"/>
        <div class="popup-info">
          <h3 class="popup-titulo">{{ noticiaSeleccionada.title }}</h3>
          <p class="popup-author">
            Por {{ noticiaSeleccionada.author }} · {{ noticiaSeleccionada.date }}
          </p>
          <p class="popup-descripcion">{{ noticiaSeleccionada.description }}</p>
        </div>
      </div>
    </div>
  </div>
</template>



<script>
import axios from 'axios';
import paginatorComponent from '@/components/paginatorComponent.vue';

export default {
  name: 'NoticiasComponent',
  components: {
    paginatorComponent,
  },
  data() {
    return {
      currentPage: 1,
      itemsPerPage: 7,
      noticias: [], // Todas las noticias
      noticiasFiltradas: [], // Noticias filtradas por deporte
      isLoading: false,
      errorMessage: '',
      noticiaSeleccionada: null,
      deporteSeleccionado: 'todos',
      deportes: [
        { value: 'todos', label: 'Todos' },
        { value: 'futbol', label: 'Fútbol' },
        { value: 'baloncesto', label: 'Baloncesto' },
        { value: 'beisbol', label: 'Béisbol' },
        { value: 'natacion', label: 'Natación' },
        { value: 'volleyball', label: 'Voleibol' },
      ],
    };
  },
  computed: {
    paginatedNews() {
      const start = (this.currentPage - 1) * this.itemsPerPage;
      const end = start + this.itemsPerPage;
      return this.noticiasFiltradas.slice(start, end);
    },
  },
  methods: {
    async cargarNoticias() {
      this.isLoading = true;
      try {
        const [futbol, baloncesto, beisbol, volleyball, natacion] = await Promise.all([
          axios.get('/futbol_news'),
          axios.get('/basketball_news'),
          axios.get('/baseball_news'),
          axios.get('/volleyball_news'),
          axios.get('/swimming_news')
        ]);

        this.noticias = [
          ...futbol.data.futbol_news.map(n => ({ ...n, categoria: 'futbol' })),
          ...baloncesto.data.basketball_news.map(n => ({ ...n, categoria: 'baloncesto' })),
          ...beisbol.data.baseball_news.map(n => ({ ...n, categoria: 'beisbol' })),
          ...volleyball.data.volleyball_news.map(n => ({ ...n, categoria: 'volleyball' })),
          ...natacion.data.swimming_news.map(n => ({ ...n, categoria: 'natacion' })),
        ];

        this.filtrarNoticias();
      } catch (error) {
        console.error('Error al cargar noticias:', error);
        this.errorMessage = 'Error al cargar noticias. Inténtalo de nuevo más tarde.';
      } finally {
        this.isLoading = false;
      }
    },

    filtrarNoticias() {
      this.noticiasFiltradas = this.deporteSeleccionado === 'todos'
        ? [...this.noticias]
        : this.noticias.filter(noticia => noticia.categoria === this.deporteSeleccionado);
      this.currentPage = 1; // Resetear paginación
    },

    cambiarDeporte(deporte) {
      this.deporteSeleccionado = deporte;
      this.filtrarNoticias();
    },

    abrirNoticia(noticia) {
      this.noticiaSeleccionada = noticia;
    },

    cerrarNoticia() {
      this.noticiaSeleccionada = null;
    }
  },

  mounted() {
    this.cargarNoticias();
  }
};
</script>


<style scoped>
@import '../../../scss/Noticias/noticias.scss';
</style>