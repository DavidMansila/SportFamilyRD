<template>
  <div class="noticias-page">


    <!-- Navbar -->
    <Navbar />

    <!-- Botón flotante para agregar noticia -- v-if="user_type === 'admin'" -->
    <!-- <div v-if="user_type === 'admin'" class="floating-action">
      <button class="btn-agregar" @click="agregarNoticia">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M12 5V19" stroke="white" stroke-width="2" stroke-linecap="round" />
          <path d="M5 12H19" stroke="white" stroke-width="2" stroke-linecap="round" />
        </svg>
      </button>
    </div> -->

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

            <button v-if="user?.user_type == 'admin'" class="btn-eliminar" @click.stop="eliminarNoticia(noticia)">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                <path d="M4 7H20" stroke="currentColor" stroke-width="2" />
                <path d="M10 11V17" stroke="currentColor" />
                <path d="M14 11V17" stroke="currentColor" />
                <path d="M5 7L6 19C6 20.1046 6.89543 21 8 21H16C17.1046 21 18 20.1046 18 19L19 7"
                  stroke="currentColor" />
                <path d="M9 7V4C9 3.44772 9.44772 3 10 3H14C14.5523 3 15 3.44772 15 4V7" stroke="currentColor" />
              </svg>
            </button>

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
      <div v-if="!errorMessage">
        <paginatorComponent v-model="currentPage" :total-items="noticiasFiltradas.length" :items-per-page="itemsPerPage"
          :max-pages-shown="5" />
      </div>


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
            <!-- Selector de categoría -->
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
                <input v-if="noticiaSeleccionada.isEditing" type="date"
                  :value="formatDateForAPI(noticiaSeleccionada.parsedDate)"
                  @input="noticiaSeleccionada.parsedDate = new Date($event.target.value)" class="edit-date">
                <span v-else class="popup-date">{{ formatDateForDisplay(noticiaSeleccionada.parsedDate) }}</span>
              </div>
            </div>
          </div>

          <div class="popup-image-container">
            <img :src="noticiaSeleccionada.image" alt="Imagen de noticia" class="popup-image" />

            <div v-if="noticiaSeleccionada.isEditing" class="image-upload">
              <label for="image-upload" class="upload-btn">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                  <path d="M3 6H5H21" stroke="currentColor" stroke-width="2" />
                  <path
                    d="M8 6V4C8 3.46957 8.21071 2.96086 8.58579 2.58579C8.96086 2.21071 9.46957 2 10 2H14C14.5304 2 15.0391 2.21071 15.4142 2.58579C15.7893 2.96086 16 3.46957 16 4V6"
                    stroke="currentColor" stroke-width="2" />
                  <path
                    d="M19 6V20C19 20.5304 18.7893 21.0391 18.4142 21.4142C18.0391 21.7893 17.5304 22 17 22H7C6.46957 22 5.96086 21.7893 5.58579 21.4142C5.21071 21.0391 5 20.5304 5 20V6"
                    stroke="currentColor" stroke-width="2" />
                  <path d="M10 11V17" stroke="currentColor" stroke-width="2" />
                  <path d="M14 11V17" stroke="currentColor" stroke-width="2" />
                </svg>
                Cambiar imagen
              </label>
              <input id="image-upload" type="file" accept="image/*" @change="handleImageChange" class="hidden-upload">
            </div>
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
                <!-- <button class="popup-share" @click.stop="compartirNoticia(noticiaSeleccionada)">
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                    <path
                      d="M18 8C19.6569 8 21 6.65685 21 5C21 3.34315 19.6569 2 18 2C16.3431 2 15 3.34315 15 5C15 5.12548 15.0077 5.24917 15.0227 5.37061L8.0826 9.84066C7.54305 9.32015 6.8089 9 6 9C4.34315 9 3 10.3431 3 12C3 13.6569 4.34315 15 6 15C6.8089 15 7.54305 14.6798 8.0826 14.1593L15.0227 18.6294C15.0077 18.7508 15 18.8745 15 19C15 20.6569 16.3431 22 18 22C19.6569 22 21 20.6569 21 19C21 17.3431 19.6569 16 18 16C17.1911 16 16.457 16.3202 15.9174 16.8407L8.9773 12.3706C8.99225 12.2492 9 12.1255 9 12C9 11.8745 8.99225 11.7508 8.9773 11.6294L15.9174 7.15934C16.457 7.67985 17.1911 8 18 8Z"
                      fill="currentColor" />
                  </svg>
                  Compartir
                </button> -->
                <button class="popup-save" @click.stop="toggleSave(noticiaSeleccionada)" v-if="user">
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

  <!-- Burbuja de Mensajes Flotante -->
  <ChatBubbleComponent v-if="user && !noticiaSeleccionada" :user="user" />

  <Alert 
    v-if="openModal" 
    :type="alertType" 
    :message="alertMessage" 
    @close="openModal = false" 
  />  
</template>

<script>
import axios from 'axios';
import paginatorComponent from '@/components/paginatorComponent.vue';
import Navbar from '../navbarComponent.vue';
import ChatBubbleComponent from '../ChatBubbleComponent.vue';
import Alert from '../Alert.vue';

export default {
  name: 'NoticiasComponent',
  components: {
    paginatorComponent,
    Navbar,
    ChatBubbleComponent,
    Alert
  },
  data() {
    return {
      openModal: false,
      alertType: 'success', // 'success', 'error', 'alert'
      alertMessage: '',

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
        { value: 'baseball', label: 'Béisbol' },
        { value: 'volleyball', label: 'Volleyball' },
        { value: 'swimming', label: 'Natacion' },
      ],
      saved: false,
      user: null
    };
  },
  computed: {
    paginatedNews() {
      const sortedNews = [...this.noticiasFiltradas].sort((a, b) => {
        // Primero: noticias guardadas
        if (a.saved !== b.saved) return b.saved - a.saved;

        // Segundo: noticias más recientes
        return b.parsedDate - a.parsedDate;
      });

      const start = (this.currentPage - 1) * this.itemsPerPage;
      const end = start + this.itemsPerPage;
      return sortedNews.slice(start, end);
    },
    dateInput: {
      get() {
        return this.formatDateForAPI(this.noticiaSeleccionada.parsedDate);
      },
      set(value) {
        this.noticiaSeleccionada.parsedDate = new Date(value);
      }
    }
  },

  methods: {

    async cargarNoticias() {
      this.isLoading = true;
      this.errorMessage = '';
      try {
        // Cargar noticias
        const newsResponse = await axios.get('/news');
        this.noticias = newsResponse.data.map(noticia => ({
          id: noticia.id,
          title: noticia.title,
          description: noticia.description,
          image: noticia.image || '/imagenes/noticia-default.jpg',
          author: noticia.author || 'Autor desconocido',
          date: noticia.published_at,
          categoria: noticia.category,
          saved: false,
          parsedDate: new Date(noticia.published_at)
        }));

        // Después de cargar las noticias
        if (this.user) {
          await this.cargarNoticiasGuardadas();
        }

        this.noticias.sort((a, b) => b.parsedDate - a.parsedDate);
        this.filtrarNoticias();

      } catch (error) {
        console.error('Error al cargar noticias:', error);
        this.errorMessage = 'Error al cargar noticias. Por favor, inténtalo de nuevo más tarde.';
      } finally {
        this.isLoading = false;
      }
    },

    async cargarNoticiasGuardadas() {
      if (!this.user || !this.user.id) return;

      try {
        const token = sessionStorage.getItem('token');

        const response = await axios.get('/saved-news', {

          headers: {
            Authorization: `Bearer ${token}`
          },
          params: {
            user_id: this.user.id
          }
        });

        const savedIds = response.data.map(id => Number(id));

        this.noticias.forEach(noticia => {
          noticia.saved = savedIds.includes(Number(noticia.id));
        });

        this.$forceUpdate();

      } catch (error) {
        console.error('Error al cargar noticias guardadas:', error);
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
    },

    formatDateForDisplay(dateObj) {
      if (!dateObj || !(dateObj instanceof Date)) return 'Fecha desconocida';

      // Verificar si la fecha es válida
      if (isNaN(dateObj.getTime())) return 'Fecha inválida';

      const options = { year: 'numeric', month: 'long', day: 'numeric' };
      return dateObj.toLocaleDateString('es-ES', options);
    },


    // FUNCIONES DE GUARDADO

    async toggleSave(noticia) {
      if (!this.user || !this.user.id) {
       
        this.alertMessage = 'Inicia sesión para guardar noticias';
        this.alertType = 'alert';
        this.openModal = true;
        return;
      }

      try {
        const token = sessionStorage.getItem('token');

        const response = await axios.post(
          `/news/${noticia.id}/toggle-save`,
          {},
          {
            headers: {
              Authorization: `Bearer ${token}`
            },
            params: {
              user_id: this.user.id
            }
          }
        );

        noticia.saved = response.data.saved;
        await this.cargarNoticiasGuardadas();

      } catch (error) {
        console.error('Error al guardar noticia:', error);
      }
    },


    // FUNCIONES PARA ADMINISTRADOR

    agregarNoticia() {
      // Lógica para agregar nueva noticia
      console.log('Agregar nueva noticia');
    },

    editarNoticia(noticia) {
      this.noticiaSeleccionada = {
        ...noticia,
        isEditing: true
      };
      document.body.style.overflow = 'hidden';
    },

    async eliminarNoticia(noticia) {
      if (confirm(`¿Estás seguro de eliminar "${noticia.title}"? Esta acción no se puede deshacer.`)) {
        try {
          const token = sessionStorage.getItem('token');

          await axios.delete(`/news/${noticia.id}`, {
            headers: {
              'Authorization': `Bearer ${token}`
            }
          });

          // Eliminar de la lista local
          this.noticias = this.noticias.filter(n => n.id !== noticia.id);
          this.filtrarNoticias();

          if (this.noticiaSeleccionada && this.noticiaSeleccionada.id === noticia.id) {
            this.cerrarNoticia();
          }

          this.showToast('Noticia eliminada correctamente', 'success');
        } catch (error) {
          console.error('Error al eliminar noticia:', error);
          this.showToast('Error al eliminar noticia. Por favor intente nuevamente.', 'error');
        }
      }
    },

    cancelarEdicion() {
      this.cerrarNoticia();
    },

    async guardarCambios() {
      try {
        this.isLoading = true;

        const formData = new FormData();
        formData.append('title', this.noticiaSeleccionada.title);
        formData.append('content', this.noticiaSeleccionada.description);
        formData.append('author', this.noticiaSeleccionada.author);
        formData.append('category', this.noticiaSeleccionada.categoria);
        formData.append('published_at', this.formatDateForAPI(this.noticiaSeleccionada.parsedDate));

        // Agregar imagen si se seleccionó una nueva
        if (this.newImage) {
          formData.append('image', this.newImage);
        }

        const token = sessionStorage.getItem('token');

        const response = await axios.put(
          `/news/${this.noticiaSeleccionada.id}`,
          formData,
          {
            headers: {
              'Content-Type': 'multipart/form-data',
              'Authorization': `Bearer ${token}`
            }
          }
        );

        // Actualizar la noticia en el frontend
        const updatedNews = response.data.news;
        const index = this.noticias.findIndex(n => n.id === updatedNews.id);

        if (index !== -1) {
          this.noticias[index] = {
            ...updatedNews,
            parsedDate: new Date(updatedNews.published_at)
          };
        }

        this.filtrarNoticias();
        this.cerrarNoticia();
        this.newImage = null;

        this.showToast('Cambios guardados exitosamente', 'success');
      } catch (error) {
        console.error('Error al guardar cambios:', error);
        this.showToast('Error al guardar cambios. Por favor intente nuevamente.', 'error');
      } finally {
        this.isLoading = false;
      }
    },


    handleImageChange(event) {
      const file = event.target.files[0];
      if (file) {
        this.newImage = file;

        // Crear una URL temporal para previsualización
        this.noticiaSeleccionada.image = URL.createObjectURL(file);
      }
    },


    formatDateForAPI(dateObj) {
      if (!dateObj || !(dateObj instanceof Date) || isNaN(dateObj.getTime())) {
        return '';
      }
      const year = dateObj.getFullYear();
      const month = String(dateObj.getMonth() + 1).padStart(2, '0');
      const day = String(dateObj.getDate()).padStart(2, '0');
      return `${year}-${month}-${day}`;
    },




    async compartirNoticia(noticia) {
      try {

        const url = `${window.location.origin}/noticia/${noticia.id}`;

        // Construir texto para compartir con todos los detalles
        const shareText = `SportFamilyRD - ${noticia.title}\n\n${noticia.description.substring(0, 100) +
          (noticia.description.length > 300 ? '...' : '')
          }\n\nVen a ver mas noticias: ${url}`;

        if (navigator.share) {
          await navigator.share({
            title: `SportFamilyRD: ${noticia.title}`,
            text: shareText,
          });
        } else {
          await navigator.clipboard.writeText(shareText);
         
          this.alertType = 'success';
          this.alertMessage = '¡Contenido copiado! Puedes compartir esta noticia:\n\n' + shareText;
          this.openModal = true;
        }
      } catch (error) {
        console.error('Error al compartir:', error);
        if (error.name !== 'AbortError') {
          
          this.alertType = 'error';
          this.alertMessage = 'Error al compartir. Por favor, inténtalo de nuevo.';
          this.openModal = true;
        }
      }
    }

  },

  async mounted() {

    // Cargar usuario
    this.user = JSON.parse(sessionStorage.getItem('user')) || {};

    // Cargar noticias
    await this.cargarNoticias();

    // Cargar estado de guardado si hay usuario
    if (this.user) {
      await this.cargarNoticiasGuardadas();
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
  top: -190px;
  left: 10px;
  z-index: 3;
  background: rgba(255, 255, 255, 0.95);
  padding: 6px;
  border-radius: 50%;
  width: 35px;
  height: 35px;
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

.noticia-title {
  font-size: large;
  padding-bottom: 10px;
  font-weight: 700;
  color: #2c3e50;
  background: black;
  -webkit-background-clip: text;
  background-clip: text;
  -webkit-text-fill-color: transparent;
}






.no-news {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  text-align: center;
}

.center-wrapper {
  display: flex;
  justify-content: center;
  margin-bottom: 20px;
}

.empty-image {
  max-width: 150px;
  /* Ajusta el tamaño según necesites */
  height: auto;
  opacity: 0.7;
}

.no-news h3 {
  font-size: 1.5rem;
  color: #555;
  margin-bottom: 10px;
}

.no-news p {
  color: #777;
  font-size: 1rem;
  max-width: 400px;
  margin: 0 auto;
}




.image-upload {
  position: absolute;
  bottom: 15px;
  right: 15px;
  z-index: 10;
}

.upload-btn {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 8px 16px;
  background: rgba(255, 255, 255, 0.9);
  border-radius: 4px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s;
  border: 1px solid #ddd;
}

.upload-btn:hover {
  background: white;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
}

.hidden-upload {
  display: none;
}

.edit-select {
  width: 100%;
  padding: 10px;
  margin-bottom: 15px;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 16px;
}

.edit-title,
.edit-author,
.edit-date {
  width: 100%;
  padding: 10px;
  margin-bottom: 10px;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 16px;
}

.edit-description {
  width: 100%;
  min-height: 300px;
  padding: 15px;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 16px;
  line-height: 1.6;
  resize: vertical;
}

</style>
