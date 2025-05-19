<template>
  <div class="noticias-page">


    <!-- Navbar -->
    <Navbar />

    <!-- Botón flotante para agregar noticia -- v-if="user_type === 'admin'" -->
    <div v-if="user_type === 'admin'" class="floating-action">
      <button class="btn-agregar" @click="agregarNoticia">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M12 5V19" stroke="white" stroke-width="2" stroke-linecap="round" />
          <path d="M5 12H19" stroke="white" stroke-width="2" stroke-linecap="round" />
        </svg>
      </button>
    </div>

    <!-- Contenido principal -->
    <div class="container">
      <div class="header-section">
        <h2 class="page-title">Últimas Noticias Deportivas</h2>
        <p class="page-subtitle">Mantente informado con lo último del mundo del deporte</p>
      </div>

      <!-- Filtro de deportes -->
      <div class="filtro-deportes">
        <button v-for="deporte in deportes" :key="deporte.value" @click="cambiarDeporte(deporte.value)"
          :class="['filtro-btn', { active: deporteSeleccionado === deporte.value }]">
          <span class="deporte-icon" :class="deporte.value"></span>
          {{ deporte.label }}
        </button>
      </div>


      <!-- Estados de carga -->
      <div v-if="isLoading" class="loading-container">
        <div class="spinner"></div>
        <p class="loading-text">Cargando noticias...</p>
      </div>

      <div v-else-if="errorMessage" class="error-container">
        <div class="error-icon">!</div>
        <p class="error-text">{{ errorMessage }}</p>
        <button @click="cargarNoticias" class="retry-btn">Reintentar</button>
      </div>


      <!-- Lista de noticias -->
      <div v-else>
        <div v-if="paginatedNews.length > 0" class="news-grid">
          <div v-for="noticia in paginatedNews" :key="noticia.id" class="noticia-card" @click="abrirNoticia(noticia)"
            :class="noticia.categoria">
            <div class="noticia-image">
              <img :src="noticia.image" alt="Imagen de noticia" class="image" />
              <span class="news-category">{{ getCategoryName(noticia.categoria) }}</span>
            </div>
            <div class="noticia-content">
              <div class="content-wrapper">
                <h3 class="noticia-title">{{ noticia.title }}</h3>
                <p class="noticia-excerpt">{{ truncateText(noticia.description, 120) }}</p>
                <div class="noticia-meta">
                  <span class="author-name">{{ noticia.author }}</span>
                  <span class="noticia-date">{{ formatDateForDisplay(noticia.parsedDate) }}</span>
                </div>
              </div>
              <button class="read-more">Leer más <span class="arrow">→</span></button>

              <div class="saved-indicator" v-if="noticia.saved">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                  <path d="M19 21L12 16L5 21V5C5 3.89543 5.89543 3 7 3H17C18.1046 3 19 3.89543 19 5V21Z" />
                </svg>
              </div>

              <!-- ADMIN -->
              <button v-if="user.user_type === 'admin'" class="btn-editar" @click.stop="editarNoticia(noticia)">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                  <path
                    d="M13.945 5.23997L3.87503 15.31C3.70599 15.479 3.58707 15.6913 3.53203 15.923L2.72203 19.447C2.65321 19.735 2.74074 20.0382 2.95403 20.25C3.1489 20.444 3.41422 20.5486 3.68803 20.539L7.19503 20.458C7.42676 20.4494 7.6493 20.3744 7.83803 20.242L17.906 10.172"
                    stroke="currentColor" />
                  <path
                    d="M12.945 6.23999L16.766 2.41799C17.546 1.63899 18.812 1.63899 19.592 2.41799L21.592 4.41799C22.372 5.19799 22.372 6.46399 21.592 7.24399L17.846 10.99"
                    stroke="currentColor" />
                </svg>
              </button>

              <!-- ADMIN -->
              <button v-if="user.user_type === 'admin'" class="btn-eliminar" @click.stop="eliminarNoticia(noticia)">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                  <path d="M4 7H20" stroke="currentColor" stroke-width="2" />
                  <path d="M10 11V17" stroke="currentColor" />
                  <path d="M14 11V17" stroke="currentColor" />
                  <path d="M5 7L6 19C6 20.1046 6.89543 21 8 21H16C17.1046 21 18 20.1046 18 19L19 7"
                    stroke="currentColor" />
                  <path d="M9 7V4C9 3.44772 9.44772 3 10 3H14C14.5523 3 15 3.44772 15 4V7" stroke="currentColor" />
                </svg>
              </button>


            </div>
          </div>
        </div>
        <div v-else class="no-news">
          <img src="/imagenes/no-news.png" alt="No hay noticias" class="no-news-img" />
          <p>No hay noticias disponibles para esta categoría.</p>
          <button @click="resetFilters" class="retry-btn">Ver todas las noticias</button>
        </div>
      </div>


      <!-- Paginación -->
      <paginatorComponent v-model="currentPage" :total-items="noticiasFiltradas.length" :items-per-page="itemsPerPage"
        :max-pages-shown="5" />
    </div>


    <!-- Pop-up de noticia -->
    <transition name="fade">
      <div v-if="noticiaSeleccionada" class="popup-overlay" @click.self="cerrarNoticia">
        <div class="popup-content">
          <button class="btn-cerrar" @click="cerrarNoticia">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M18 6L6 18" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                stroke-linejoin="round" />
              <path d="M6 6L18 18" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                stroke-linejoin="round" />
            </svg>
          </button>

          <div class="popup-header">
            <!-- Selector de categoría (solo en edición) -->
            <select v-if="noticiaSeleccionada.isEditing" v-model="noticiaSeleccionada.categoria" class="edit-select">
              <option v-for="deporte in deportes.filter(d => d.value !== 'todos')" :key="deporte.value"
                :value="deporte.value">
                {{ deporte.label }}
              </option>
            </select>
            <span v-else class="popup-category" :class="noticiaSeleccionada.categoria">
              {{ getCategoryName(noticiaSeleccionada.categoria) }}
            </span>

            <!-- Título editable -->
            <input v-if="noticiaSeleccionada.isEditing" v-model="noticiaSeleccionada.title" class="edit-title"
              placeholder="Título de la noticia">
            <h3 v-else class="popup-titulo">{{ noticiaSeleccionada.title }}</h3>

            <div class="popup-author-date">
              <span class="author-avatar">{{ getInitials(noticiaSeleccionada.author) }}</span>
              <div class="author-info">
                <!-- Autor editable -->
                <input v-if="noticiaSeleccionada.isEditing" v-model="noticiaSeleccionada.author" class="edit-author"
                  placeholder="Autor">
                <span v-else class="popup-author">Por {{ noticiaSeleccionada.author }}</span>

                <!-- Fecha editable -->
                <input v-if="noticiaSeleccionada.isEditing" type="date" v-model="noticiaSeleccionada.date"
                  class="edit-date">
                <span v-else class="popup-date">{{ noticiaSeleccionada.date }}</span>
              </div>
            </div>
          </div>

          <div class="popup-image-container">
            <img :src="noticiaSeleccionada.image" alt="Imagen de noticia" class="popup-image" />
          </div>

          <div class="popup-body">
            <!-- Descripción editable -->
            <textarea v-if="noticiaSeleccionada.isEditing" v-model="noticiaSeleccionada.description"
              class="edit-description" placeholder="Descripción de la noticia"></textarea>
            <div v-else class="popup-descripcion" v-html="formatDescription(noticiaSeleccionada.description)"></div>

            <div class="popup-actions">
              <button v-if="noticiaSeleccionada.isEditing" class="popup-save" @click="guardarCambios">
                Guardar cambios
              </button>
              <button v-if="noticiaSeleccionada.isEditing" class="popup-cancel" @click="cancelarEdicion">
                Cancelar
              </button>

              <template v-else>
                <button class="popup-share">
                  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path
                      d="M18 8C19.6569 8 21 6.65685 21 5C21 3.34315 19.6569 2 18 2C16.3431 2 15 3.34315 15 5C15 5.12548 15.0077 5.24917 15.0227 5.37061L8.0826 9.84066C7.54305 9.32015 6.8089 9 6 9C4.34315 9 3 10.3431 3 12C3 13.6569 4.34315 15 6 15C6.8089 15 7.54305 14.6798 8.0826 14.1593L15.0227 18.6294C15.0077 18.7508 15 18.8745 15 19C15 20.6569 16.3431 22 18 22C19.6569 22 21 20.6569 21 19C21 17.3431 19.6569 16 18 16C17.1911 16 16.457 16.3202 15.9174 16.8407L8.9773 12.3706C8.99225 12.2492 9 12.1255 9 12C9 11.8745 8.99225 11.7508 8.9773 11.6294L15.9174 7.15934C16.457 7.67985 17.1911 8 18 8Z"
                      fill="currentColor" />
                  </svg>
                  Compartir
                </button>
                <button class="popup-save" @click.stop="toggleSave(noticiaSeleccionada)">
                  <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                    <path :fill="noticiaSeleccionada.saved ? 'currentColor' : 'none'" stroke="currentColor"
                      d="M19 21L12 16L5 21V5C5 3.89543 5.89543 3 7 3H17C18.1046 3 19 3.89543 19 5V21Z" />
                  </svg>
                  {{ noticiaSeleccionada.saved ? 'Guardado' : 'Guardar' }}
                </button>
              </template>
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
import Navbar from '../navbarComponent.vue';

export default {
  name: 'NoticiasComponent',
  components: {
    paginatorComponent,
    Navbar
  },
  data() {
    return {
      currentPage: 1,
      itemsPerPage: 9,
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
        { value: 'volleyball', label: 'Volleyball' },
        { value: 'swimming', label: 'Natacion' },
      ],
      saved: false,
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

    generateStableId(title, date) {
      const str = `${title}-${date}`;

      if (typeof TextEncoder !== 'undefined') {
        const utf8Bytes = new TextEncoder().encode(str);
        const base64 = btoa(String.fromCharCode(...new Uint8Array(utf8Bytes)));
        return base64.replace(/[+/=]/g, '').substr(0, 12);
      }

      // Fallback para navegadores antiguos
      return btoa(unescape(encodeURIComponent(str)))
        .replace(/[+/=]/g, '')
        .substr(0, 12);
    },


    async cargarNoticias() {
      this.isLoading = true;
      this.errorMessage = '';
      try {
        const [futbol, baloncesto, beisbol, volleyball, swimming] = await Promise.all([
          axios.get('/futbol_news'),
          axios.get('/basketball_news'),
          axios.get('/baseball_news'),
          axios.get('/volleyball_news'),
          axios.get('/swimming_news')
        ]);

        // Procesar cada categoría con debug
        const processCategory = (news, category) => {
          return news.map(n => {
            const parsedDate = this.parseDate(n.date, category);
            return {
              ...n,
              id: n.id || this.generateStableId(n.title, n.date),
              saved: false,
              categoria: category,
              parsedDate: parsedDate
            };
          });
        };

        this.noticias = [
          ...processCategory(futbol.data.futbol_news, 'futbol'),
          ...processCategory(baloncesto.data.basketball_news, 'baloncesto'),
          ...processCategory(beisbol.data.baseball_news, 'beisbol'),
          ...processCategory(volleyball.data.volleyball_news, 'volleyball'),
          ...processCategory(swimming.data.swimming_news, 'swimming'),
        ];

        // Debug antes de ordenar
        console.log("Noticias antes de ordenar:", this.noticias.map(n => ({
          title: n.title,
          originalDate: n.date,
          parsedDate: n.parsedDate,
          formatted: this.formatDateForDisplay(n.parsedDate)
        })));

        // Ordenar por fecha (más reciente primero)
        this.noticias.sort((a, b) => b.parsedDate - a.parsedDate);

        // Debug después de ordenar
        console.log("Noticias después de ordenar:", this.noticias.map(n => ({
          title: n.title,
          formattedDate: this.formatDateForDisplay(n.parsedDate)
        })));

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

      this.ordenarNoticiasGuardadas();
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
    },


    parseDate(dateStr, category) {
      if (!dateStr) return new Date(0); // Fecha mínima si no hay fecha

      // Limpiar la cadena de fecha
      dateStr = dateStr.trim();

      try {
        switch (category) {
          case 'futbol': // "7 de enero de 2025"
            const partsFutbol = dateStr.split(' de ');
            if (partsFutbol.length !== 3) return new Date(0);
            const dayFutbol = parseInt(partsFutbol[0]);
            const monthFutbol = this.getMonthNumber(partsFutbol[1]);
            const yearFutbol = parseInt(partsFutbol[2]);
            if (isNaN(dayFutbol)) return new Date(0);
            return new Date(yearFutbol, monthFutbol, dayFutbol);

          case 'baloncesto': // "domingo 04 mayo, 2025"
            const partsBaloncesto = dateStr.split(' ');
            if (partsBaloncesto.length < 4) return new Date(0);
            const dayBaloncesto = parseInt(partsBaloncesto[1]);
            const monthBaloncesto = this.getMonthNumber(partsBaloncesto[2].replace(',', ''));
            const yearBaloncesto = parseInt(partsBaloncesto[3]);
            if (isNaN(dayBaloncesto)) return new Date(0);
            return new Date(yearBaloncesto, monthBaloncesto, dayBaloncesto);

          case 'beisbol': // "06/05/2025   ·   01:31 PM"
            const [datePart] = dateStr.split('·').map(s => s.trim());
            const datePartsBeisbol = datePart.split('/');
            if (datePartsBeisbol.length !== 3) return new Date(0);
            const dayBeisbol = parseInt(datePartsBeisbol[0]);
            const monthBeisbol = parseInt(datePartsBeisbol[1]) - 1;
            const yearBeisbol = parseInt(datePartsBeisbol[2]);
            if (isNaN(dayBeisbol) || isNaN(monthBeisbol)) return new Date(0);
            return new Date(yearBeisbol, monthBeisbol, dayBeisbol);

          case 'volleyball': // "May 5, 2025"
            const partsVolleyball = dateStr.split(' ');
            if (partsVolleyball.length < 3) return new Date(0);
            const monthVolleyball = this.getMonthNumber(partsVolleyball[0]);
            const dayVolleyball = parseInt(partsVolleyball[1].replace(',', ''));
            const yearVolleyball = parseInt(partsVolleyball[2]);
            if (isNaN(dayVolleyball)) return new Date(0);
            return new Date(yearVolleyball, monthVolleyball, dayVolleyball);

          case 'swimming': // "agosto 22, 2024"
            const partsSwimming = dateStr.split(' ');
            if (partsSwimming.length < 3) return new Date(0);
            const monthSwimming = this.getMonthNumber(partsSwimming[0]);
            const daySwimming = parseInt(partsSwimming[1].replace(',', ''));
            const yearSwimming = parseInt(partsSwimming[2]);
            if (isNaN(daySwimming)) return new Date(0);
            return new Date(yearSwimming, monthSwimming, daySwimming);

          default:
            return new Date(dateStr) || new Date(0);
        }
      } catch (e) {
        console.error(`Error parsing date (${category}): "${dateStr}"`, e);
        return new Date(0);
      }
    },

    getMonthNumber(monthName) {
      if (!monthName) return 0;

      const months = {
        'enero': 0, 'january': 0, 'jan': 0,
        'febrero': 1, 'february': 1, 'feb': 1,
        'marzo': 2, 'march': 2, 'mar': 2,
        'abril': 3, 'april': 3, 'apr': 3,
        'mayo': 4, 'may': 4,
        'junio': 5, 'june': 5, 'jun': 5,
        'julio': 6, 'july': 6, 'jul': 6,
        'agosto': 7, 'august': 7, 'aug': 7,
        'septiembre': 8, 'september': 8, 'sep': 8, 'setiembre': 8,
        'octubre': 9, 'october': 9, 'oct': 9,
        'noviembre': 10, 'november': 10, 'nov': 10,
        'diciembre': 11, 'december': 11, 'dec': 11
      };

      const normalizedMonth = monthName.toLowerCase().replace(',', '').trim();
      return months[normalizedMonth] ?? 0;
    },

    formatDateForDisplay(dateObj) {
      if (!dateObj || !(dateObj instanceof Date)) return 'Fecha desconocida';

      // Verificar si la fecha es válida
      if (isNaN(dateObj.getTime())) return 'Fecha inválida';

      const options = { year: 'numeric', month: 'long', day: 'numeric' };
      return dateObj.toLocaleDateString('es-ES', options);
    },




    // FUNCIONES DE GUARDADO

    toggleSave(noticia) {
      noticia.saved = !noticia.saved;
      this.guardarEnLocalStorage(noticia);
      this.ordenarNoticiasGuardadas();

      // Forzar actualización del DOM para el popup
      this.noticiaSeleccionada = { ...this.noticiaSeleccionada };
    },

    guardarEnLocalStorage(noticia) {
      const savedNews = JSON.parse(sessionStorage.getItem('savedNews') || '{}');
      if (noticia.saved) {
        savedNews[noticia.id] = true;
      } else {
        delete savedNews[noticia.id];
      }
      sessionStorage.setItem('savedNews', JSON.stringify(savedNews));

      // Actualizar la lista completa de noticias
      this.noticias = this.noticias.map(n =>
        n.id === noticia.id ? { ...n, saved: noticia.saved } : n
      );
    },

    ordenarNoticiasGuardadas() {
      this.noticiasFiltradas.sort((a, b) => {
        if (a.saved === b.saved) return b.parsedDate - a.parsedDate;
        return b.saved - a.saved;
      });
    },

    mostrarFeedbackGuardado(noticia) {
      this.toastMessage = noticia.saved ? 'Noticia guardada' : 'Noticia eliminada de guardados';
      this.mostrarToast = true;
      setTimeout(() => this.mostrarToast = false, 2000);
    },

    cargarGuardados() {
      const savedNews = JSON.parse(sessionStorage.getItem('savedNews')) || {};
      this.noticias = this.noticias.map(noticia => ({
        ...noticia,
        saved: savedNews[noticia.id] || false
      }));
    },










    // FUNCIONES PARA ADMINISTRADOR

    agregarNoticia() {
      // Lógica para agregar nueva noticia
      console.log('Agregar nueva noticia');
    },

    editarNoticia(noticia) {
      // Abre la noticia en modo edición
      this.noticiaSeleccionada = {
        ...noticia,
        isEditing: true // Añadimos un flag para indicar que estamos editando
      };
      document.body.style.overflow = 'hidden';
    },

    eliminarNoticia(noticia) {
      // Lógica para eliminar noticia
      if (confirm(`¿Estás seguro de eliminar "${noticia.title}"?`)) {
        console.log('Eliminar noticia:', noticia);
      }
    },

    cancelarEdicion() {
      this.cerrarNoticia();
    },

    async guardarCambios() {
      try {
        this.isLoading = true;

        // Determinar el endpoint basado en la categoría
        let endpoint = '';
        switch (this.noticiaSeleccionada.categoria) {
          case 'futbol': endpoint = '/update_futbol_news'; break;
          case 'baloncesto': endpoint = '/update_basketball_news'; break;
          case 'beisbol': endpoint = '/update_baseball_news'; break;
          case 'volleyball': endpoint = '/update_volleyball_news'; break;
          case 'swimming': endpoint = '/update_swimming_news'; break;
        }

        // Enviar los cambios al servidor
        const response = await axios.put(endpoint, {
          id: this.noticiaSeleccionada.id,
          title: this.noticiaSeleccionada.title,
          author: this.noticiaSeleccionada.author,
          description: this.noticiaSeleccionada.description,
          date: this.noticiaSeleccionada.date,
          // image: this.noticiaSeleccionada.image (manejaría esto aparte si cambió)
        });

        // Actualizar la noticia en la lista local
        const index = this.noticias.findIndex(n => n.id === this.noticiaSeleccionada.id);
        if (index !== -1) {
          this.noticias[index] = { ...this.noticiaSeleccionada, isEditing: false };
        }

        // Cerrar el popup
        this.cerrarNoticia();

        // Mostrar feedback
        alert('Cambios guardados exitosamente');

      } catch (error) {
        console.error('Error al guardar cambios:', error);
        alert('Error al guardar cambios. Por favor intente nuevamente.');
      } finally {
        this.isLoading = false;
      }
    },



  },
  async mounted() {
    try {
      // Cargar savedNews primero para tenerlos disponibles
      const savedNews = JSON.parse(sessionStorage.getItem('savedNews') || '{}');
      await this.cargarNoticias();

      // Aplicar savedNews después de cargar
      this.noticias = this.noticias.map(noticia => ({
        ...noticia,
        saved: savedNews[noticia.id] || false
      }));

      this.filtrarNoticias();
      this.user = JSON.parse(sessionStorage.getItem('user'));
    } catch (error) {
      console.error('Error al cargar noticias:', error);
    }
    document.title = 'Noticias';
  }
};

</script>




<style scoped>
@import '../../../scss/Noticias/noticias.scss';

@import '../../../scss/Noticias/noticias_navbar.scss';

@import '../../../scss/Noticias/noticias_filtros.scss';

@import '../../../scss/Noticias/noticias_deportes.scss';

@import '../../../scss/Noticias/noticias_grid.scss';

@import '../../../scss/Noticias/noticias_pop_out.scss';

@import '../../../scss/Noticias/noticias_responsive.scss';

@import '../../../scss/Admin/Admin_Noticias.scss';



/* En el CSS del componente */
.noticia-card {
  position: relative;
  /* Asegurar contexto de posicionamiento */
  overflow: hidden;
  /* Prevenir desbordamientos */
}

.saved-indicator {
  position: absolute;
  top: 15px;
  right: 15px;
  z-index: 3;
  /* Asegurar que está por encima de todo */
  background: rgba(255, 255, 255, 0.95);
  padding: 6px;
  border-radius: 50%;
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
  transition: all 0.2s ease;
}




.noticia-excerpt {
  padding-right: 30px;
  margin-bottom: 15px;
  line-height: 1.4;
  max-height: 4.2em;
  /* Limitar altura basado en línea */
  overflow: hidden;
}

</style>
