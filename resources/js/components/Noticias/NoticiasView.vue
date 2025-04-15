
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
        <a :href="'/Login'" class="Logout">
          <img src="/imagenes/Logout-Icon.png" alt="Logout" class="logout-icon"/>
        </a>
      </div>
    </nav>

    <!-- Contenido principal mejorado -->
    <div class="container">
      <div class="header-section">
        <h2 class="page-title">Últimas Noticias Deportivas</h2>
        <p class="page-subtitle">Mantente informado con lo último del mundo del deporte</p>
      </div>

      <!-- Filtro de deportes mejorado -->
      <div class="filtro-deportes">
        <button
          v-for="deporte in deportes"
          :key="deporte.value"
          @click="cambiarDeporte(deporte.value)"
          :class="['filtro-btn', { active: deporteSeleccionado === deporte.value }]"
        >
          <span class="deporte-icon" :class="deporte.value"></span>
          {{ deporte.label }}
        </button>
      </div>

      <!-- Estados de carga mejorados -->
      <div v-if="isLoading" class="loading-container">
        <div class="spinner"></div>
        <p class="loading-text">Cargando noticias...</p>
      </div>

      <div v-else-if="errorMessage" class="error-container">
        <div class="error-icon">!</div>
        <p class="error-text">{{ errorMessage }}</p>
        <button @click="cargarNoticias" class="retry-btn">Reintentar</button>
      </div>

      <!-- Lista de noticias mejorada -->
      <div v-else>
        <div v-if="paginatedNews.length > 0" class="news-grid">
          <div 
            v-for="noticia in paginatedNews" 
            :key="noticia.id" 
            class="noticia-card"
            @click="abrirNoticia(noticia)"
            :class="noticia.categoria"
          >
            <div class="card-overlay"></div>
            <div class="noticia-image">
              <img :src="noticia.image" alt="Imagen de noticia" class="image"/>
              <span class="news-category">{{ getCategoryName(noticia.categoria) }}</span>
            </div>
            <div class="noticia-content">
              <div class="content-wrapper">
                <h3 class="noticia-title">{{ noticia.title }}</h3>
                <p class="noticia-excerpt">{{ truncateText(noticia.description, 120) }}</p>
                <div class="noticia-meta">
                  <span class="author-name">{{ noticia.author }}</span>
                  <span class="noticia-date">{{ noticia.date }}</span>
                </div>
              </div>
              <button class="read-more">Leer más <span class="arrow">→</span></button>
            </div>
          </div>
        </div>
        <div v-else class="no-news">
          <img src="/imagenes/no-news.svg" alt="No hay noticias" class="no-news-img"/>
          <p>No hay noticias disponibles para esta categoría.</p>
          <button @click="resetFilters" class="retry-btn">Ver todas las noticias</button>
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

    <!-- Pop-up de noticia mejorado -->
    <transition name="fade">
      <div 
        v-if="noticiaSeleccionada" 
        class="popup-overlay" 
        @click.self="cerrarNoticia"
      >
        <div class="popup-content">
          <button class="btn-cerrar" @click="cerrarNoticia">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M18 6L6 18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              <path d="M6 6L18 18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          </button>
          <div class="popup-header">
            <span class="popup-category" :class="noticiaSeleccionada.categoria">{{ getCategoryName(noticiaSeleccionada.categoria) }}</span>
            <h3 class="popup-titulo">{{ noticiaSeleccionada.title }}</h3>
            <div class="popup-author-date">
              <span class="author-avatar">{{ getInitials(noticiaSeleccionada.author) }}</span>
              <div class="author-info">
                <span class="popup-author">Por {{ noticiaSeleccionada.author }}</span>
                <span class="popup-date">{{ noticiaSeleccionada.date }}</span>
              </div>
            </div>
          </div>
          <div class="popup-image-container">
            <img :src="noticiaSeleccionada.image" alt="Imagen de noticia" class="popup-image"/>
          </div>
          <div class="popup-body">
            <div class="popup-descripcion" v-html="formatDescription(noticiaSeleccionada.description)"></div>
            <div class="popup-actions">
              <button class="popup-share">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path d="M18 8C19.6569 8 21 6.65685 21 5C21 3.34315 19.6569 2 18 2C16.3431 2 15 3.34315 15 5C15 5.12548 15.0077 5.24917 15.0227 5.37061L8.0826 9.84066C7.54305 9.32015 6.8089 9 6 9C4.34315 9 3 10.3431 3 12C3 13.6569 4.34315 15 6 15C6.8089 15 7.54305 14.6798 8.0826 14.1593L15.0227 18.6294C15.0077 18.7508 15 18.8745 15 19C15 20.6569 16.3431 22 18 22C19.6569 22 21 20.6569 21 19C21 17.3431 19.6569 16 18 16C17.1911 16 16.457 16.3202 15.9174 16.8407L8.9773 12.3706C8.99225 12.2492 9 12.1255 9 12C9 11.8745 8.99225 11.7508 8.9773 11.6294L15.9174 7.15934C16.457 7.67985 17.1911 8 18 8Z" fill="currentColor"/>
                </svg>
                Compartir
              </button>
              <button class="popup-save">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path d="M19 21L12 16L5 21V5C5 4.46957 5.21071 3.96086 5.58579 3.58579C5.96086 3.21071 6.46957 3 7 3H17C17.5304 3 18.0391 3.21071 18.4142 3.58579C18.7893 3.96086 19 4.46957 19 5V21Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                Guardar
              </button>
            </div>
          </div>
        </div>
      </div>
    </transition>
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
      itemsPerPage: 6,
      noticias: [],
      noticiasFiltradas: [],
      isLoading: false,
      errorMessage: '',
      noticiaSeleccionada: null,
      deporteSeleccionado: 'todos',
      deportes: [
        { value: 'todos', label: 'Todos' },
        { value: 'futbol', label: 'Fútbol' },
        { value: 'baloncesto', label: 'Baloncesto' },
        { value: 'beisbol', label: 'Béisbol' },
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
      this.errorMessage = '';
      try {
        const [futbol, baloncesto, beisbol, volleyball] = await Promise.all([
          axios.get('/futbol_news'),
          axios.get('/basketball_news'),
          axios.get('/baseball_news'),
          axios.get('/volleyball_news'),
        ]);

        this.noticias = [
          ...futbol.data.futbol_news.map(n => ({ ...n, categoria: 'futbol' })),
          ...baloncesto.data.basketball_news.map(n => ({ ...n, categoria: 'baloncesto' })),
          ...beisbol.data.baseball_news.map(n => ({ ...n, categoria: 'beisbol' })),
          ...volleyball.data.volleyball_news.map(n => ({ ...n, categoria: 'volleyball' })),
        ];

        this.filtrarNoticias();
      } catch (error) {
        console.error('Error al cargar noticias:', error);
        this.errorMessage = 'Error al cargar noticias. Por favor, inténtalo de nuevo más tarde.';
      } finally {
        this.isLoading = false;
      }
    },
    filtrarNoticias() {
      this.noticiasFiltradas = this.deporteSeleccionado === 'todos'
        ? [...this.noticias]
        : this.noticias.filter(noticia => noticia.categoria === this.deporteSeleccionado);
      this.currentPage = 1;
    },
    cambiarDeporte(deporte) {
      this.deporteSeleccionado = deporte;
      this.filtrarNoticias();
    },
    abrirNoticia(noticia) {
      this.noticiaSeleccionada = noticia;
      document.body.style.overflow = 'hidden';
    },
    cerrarNoticia() {
      this.noticiaSeleccionada = null;
      document.body.style.overflow = 'auto';
    },
    resetFilters() {
      this.deporteSeleccionado = 'todos';
      this.filtrarNoticias();
    },
    truncateText(text, length) {
      return text.length > length ? text.substring(0, length) + '...' : text;
    },
    getCategoryName(category) {
      const deporte = this.deportes.find(d => d.value === category);
      return deporte ? deporte.label : 'Noticia';
    },
    getInitials(name) {
      return name.split(' ').map(n => n[0]).join('').toUpperCase();
    },
    formatDescription(desc) {
      return desc
        .replace(/\n/g, '<br>')
        .replace(/(https?:\/\/[^\s]+)/g, '<a href="$1" target="_blank">$1</a>');
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





