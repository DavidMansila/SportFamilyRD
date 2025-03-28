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

            <a href= "/Ajustes" class="Ajustes">
                <img src="/imagenes/Ajustes-Icon.png" alt="Ajustes" class="ajustes-icon"/>
            </a>

            <a class="Perfil">
                <img src="/imagenes/Perfil-Icon.png" alt="Perfil" class="perfil-icon"/>
            </a>

            <a :href=" login ? '/Login' : '/Logout' " class="Logout">
                <img src="/imagenes/Logout-Icon.png" alt="Logout" class="logout-icon"/>
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
        <div v-for="noticia in paginatedNews" :key="noticia.id" class="noticia-card">
          <div class="noticia-image">
            <img :src="noticia.image" alt="Imagen de noticia" class="image" />
          </div>

          <div class="noticia-content">
            <h3 class="noticia-title">{{ noticia.title }}</h3>
            <p class="noticia-subtitle">{{ noticia.subtitle }}</p> <!-- todo no hay campo subtitle, esos 2 se pueden ir, acomoda el front en base a ese -->
            <p class="noticia-author">
                <span class="author-name">{{ noticia.author }}</span>   · 
                <span class="noticia-date">{{ noticia.date }}</span>
            </p>
            <button @click="abrirNoticia(noticia)" class="read-more">Read more</button>
          </div>
        </div>
      </div>
    </div>
    
    <paginatorComponent
     
      v-model="currentPage"
      :total-items="noticias.length"
      :items-per-page="itemsPerPage"
      :max-pages-shown="5"
    />

    <!-- Pop-up de noticia completa -->
    <div v-if="noticiaSeleccionada" class="popup-overlay" @click="cerrarNoticia">
      <div class="popup-content" @click.stop>
        <button class="btn-cerrar" @click="cerrarNoticia">×</button>
        <img :src="noticiaSeleccionada.image" alt="Imagen de noticia" class="image" />
        <div class="popup-info">
          <h3 class="popup-titulo">{{ noticiaSeleccionada.title }}</h3>
          <p class="popup-descripcion">{{ noticiaSeleccionada.description }}</p>
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
      //paginator
      currentPage:1,
      itemsPerPage: 7,

      noticias: [], // Lista completa de noticias
      noticiasFutbol: [], // Lista completa de noticias
      isLoading: true,
      errorMessage: '',
      noticiaSeleccionada: null,
      deporteSeleccionado: 'todos', // Deporte seleccionado (valor inicial: 'todos')
      deportes: [
        { value: 'todos', label: 'Todos' },
        { value: 'futbol', label: 'Fútbol' },
        { value: 'baloncesto', label: 'Baloncesto' },
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

    paginatedNews() {
          const start = (this.currentPage - 1) * this.itemsPerPage;
          const end = start + this.itemsPerPage;
          return this.noticias.slice(start, end);
      },
  },

  methods: {
    abrirNoticia(noticia) {
      this.noticiaSeleccionada = noticia;
    },

    cerrarNoticia() {
      this.noticiaSeleccionada = null;
    },

    async getBaseballNews() {
      try {
        const response = await axios.get('/baseball_news');


       // switch (this.deporteSeleccionado) {
       //   case 'futbol':
        //    this.noticias = response.data.soccer_news;
        //    break;
        //  case 'baloncesto':
        //    this.noticias = response.data.basketball_news;
       //     break;
       //   case 'beisbol':
        //    this.noticias = response.data.baseball_news;
       //     break;
      //    case 'natacion':
      //      this.noticias = response.data.swimming_news;
     //       break;
     //     case 'voleyball':
     //       this.noticias = response.data.volleyball_news;
      //      break;
       //   default:
      //      this.noticias = response.data.news;
     //   }

        console.log('Datos de noticias:', response.data);
        this.noticias = response.data.baseball_news;
      }
      catch (error) {
        console.error('Error al obtener las noticias:', error);
        this.errorMessage = 'Algo salió mal al cargar las noticias. Por favor, intenta de nuevo más tarde.';
      } finally {
        this.isLoading = false;
      }
    },
    
    async getFutbolNews() {
      try {
        const response = await axios.get('/futbol_news');

        console.log('Datos de noticias:', response.data);
        this.noticias = response.data.futbol_news;
      }
      catch (error) {
        console.error('Error al obtener las noticias:', error);
        this.errorMessage = 'Algo salió mal al cargar las noticias. Por favor, intenta de nuevo más tarde.';
      } finally {
        this.isLoading = false;
      }
    },



  },

  mounted() {
    this.getBaseballNews(); 
    this.getFutbolNews(); 
  },
};
</script>


<style scoped>
@import '../../../scss/Noticias/noticias.scss';
</style>