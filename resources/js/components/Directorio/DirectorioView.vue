<template>
  <div class="sports-app">


    <!-- Navbar -->
    <Navbar />


    <!-- Header -->
    <header class="app-header">
      <img src="imagenes/DirectorioDeDeportes/directoriofondo.jpg" alt="Deportes Dominicana" class="header-image">
      <div class="header-content">
        <h1>Deportes en República Dominicana</h1>
        <p>Descubre los deportes autóctonos y populares de nuestro país</p>
      </div>
    </header>

    <!-- Barra de búsqueda y filtros -->
    <!-- <div class="controls">
      <div class="search-container">
        <input type="text" v-model="searchTerm" placeholder="Buscar deporte..." class="search-input">
        <button class="search-button">
          <i class="fas fa-search"></i>
        </button>
      </div>

      <div class="filter-buttons">
        <button v-for="region in regions" :key="region" @click="filterByRegion(region)"
          :class="{ active: activeRegion === region }">
          {{ region }}
        </button>
      </div>
    </div> -->

    <!-- Contenido principal -->
    <main class="main-content" ref="appContainer">
      <!-- Estado de carga -->
      <div class="sports-list" v-if="loading">
        <div class="sport-card sport-card-skeleton" v-for="n in 8" :key="n">
          <div class="sport-image skeleton-block"></div>
          <div class="sport-info">
            <div class="skeleton-line" style="width: 60%;"></div>
            <div class="skeleton-line" style="width: 40%;"></div>
            <div class="skeleton-line" style="width: 90%;"></div>
          </div>
        </div>
      </div>

      <!-- Listado de deportes -->
      <div class="sports-list" v-else-if="!selectedSport">
        <div v-for="sport in filteredSports" :key="sport.id" class="sport-card" @click="selectSport(sport)">
          <div class="sport-image">
            <img :src="sport.image" :alt="sport.name" loading="lazy">
          </div>
          <div class="sport-info">
            <h3>{{ sport.name }}</h3>
            <span class="region-tag">{{ sport.region }}</span>
            <div class="sport-meta">

              <span class="popularity">{{ sport.popularity }}</span>
            </div>
            <p class="short-description">{{ sport.shortDescription }}</p>
          </div>
        </div>
      </div>

      <!-- Vista detallada de un deporte -->
      <div class="sport-detail" v-else>
        <button @click="selectedSport = null" class="back-button">
          <i class="fas fa-arrow-left"></i> Volver al listado
        </button>

        <div class="detail-header">
          <h2>{{ selectedSport.name }}</h2>
          <img :src="selectedSport.image" :alt="selectedSport.name" class="detail-image">
        </div>

        <div class="detail-content">
          <div class="detail-section">
            <h3><i class="fas fa-info-circle"></i> Descripción</h3>
            <p>{{ selectedSport.description }}</p>
          </div>

          <div class="detail-section">
            <h3><i class="fas fa-tools"></i> Qué necesitas</h3>
            <ul>
              <li v-for="(item, index) in selectedSport.requirements" :key="index">
                {{ item }}
              </li>
            </ul>
          </div>

          <div class="detail-section">
            <h3><i class="fas fa-map-marked-alt"></i> Lugares para practicar</h3>
            <div class="places-container">
              <div class="place-card" v-for="place in selectedSport.places" :key="place.name">
                <h4>{{ place.name }}</h4>
                <p><i class="fas fa-location-dot"></i> {{ place.location }}</p>
                <p v-if="place.cost"><i class="fas fa-money-bill-wave"></i> {{ place.cost }}</p>
              </div>
            </div>
          </div>


        </div>
      </div>
    </main>


    <!-- Burbuja de Mensajes Flotante -->
    <ChatBubbleComponent v-if="user" :user="user" />

  </div>
</template>


<script setup>
import { ref, computed, nextTick, onMounted } from 'vue';
import { useStore } from 'vuex';
import axios from 'axios';
import Navbar from '../navbarComponent.vue';
import ChatBubbleComponent from '../ChatBubbleComponent.vue';

const store = useStore();
const appContainer = ref(null);
const searchTerm = ref('')
const activeRegion = ref('Todas')
const selectedSport = ref(null);
const loading = ref(true);

const regions = ref([
  'Todas',
  'Santo Domingo',
  'Santiago',
  'La Romana',
  'Puerto Plata',
  'Punta Cana',
  'Samaná',
  'Barahona',
  'San Cristóbal',
  'Jarabacoa',
  'Constanza',
  'Higüey',
  'San Juan de la Maguana'
]);

const sports = ref([]);

const mapSport = (s) => ({
  id: s.id,
  name: s.name,
  region: s.region,
  type: s.type,
  popularity: s.popularity,
  image: s.image,
  shortDescription: s.short_description ?? s.shortDescription,
  description: s.description,
  requirements: s.requirements || [],
  places: s.places || [],
});

const getSports = () => {
  loading.value = true;
  axios.get('/sports')
    .then((response) => {
      const data = (response.data.sports || []).map(mapSport);
      sports.value = data;
      store.dispatch('cacheSection', { key: 'directorio', data });
    })
    .catch((error) => {
      console.error('Error al cargar el directorio de deportes:', error);
    })
    .finally(() => {
      loading.value = false;
    });
};

const filteredSports = computed(() => {
  let result = sports.value;

  if (activeRegion.value !== 'Todas') {
    result = result.filter(sport =>
      sport.region.includes(activeRegion.value)
    );
  }

  if (searchTerm.value) {
    const term = searchTerm.value.toLowerCase();
    result = result.filter(sport =>
      sport.name.toLowerCase().includes(term) ||
      sport.description.toLowerCase().includes(term) ||
      sport.region.toLowerCase().includes(term)
    );
  }

  return result;
});

const filterByRegion = (region) => {
  activeRegion.value = region;
};

const selectSport = async (sport) => {
  selectedSport.value = sport;

  await nextTick();

  // 2. Desplazar el contenedor principal en lugar de window
  if (appContainer.value) {
    appContainer.value.scrollTo({
      top: 0,
      behavior: 'smooth'
    });
  }

  // 3. Enfocar el título del deporte para accesibilidad
  const sportTitle = document.querySelector('.detail-header h2');
  if (sportTitle) {
    sportTitle.tabIndex = -1;
    sportTitle.focus();
  }
};



const user = ref(null);

onMounted(() => {
  try {
    const userData = sessionStorage.getItem('user');
    if (userData) {
      user.value = JSON.parse(userData);
    }
  } catch (error) {
    console.error('Error al obtener datos del usuario:', error);
  }

  const cachedSports = store.getters.sectionCache('directorio');
  if (cachedSports) {
    sports.value = cachedSports;
    loading.value = false;
  } else {
    getSports();
  }
});


</script>




<style scoped>
@import '../../../scss/Directorio/directorio.scss';

.navbar {
  background: linear-gradient(to right, #000000, #a13300);
}

.sports-app {
  height: 100vh;
  overflow-y: auto;
  -webkit-overflow-scrolling: touch;
  /* Scroll suave en iOS */
}

.sport-card-skeleton {
  cursor: default;
  pointer-events: none;
}

.skeleton-block,
.skeleton-line {
  background: linear-gradient(90deg, rgba(0, 0, 0, 0.06) 25%, rgba(0, 0, 0, 0.12) 37%, rgba(0, 0, 0, 0.06) 63%);
  background-size: 400% 100%;
  animation: skeleton-loading 1.4s ease infinite;
  border-radius: 6px;
}

.skeleton-block {
  width: 100%;
  height: 160px;
}

.skeleton-line {
  height: 12px;
  margin: 8px 0;
}

@keyframes skeleton-loading {
  0% {
    background-position: 100% 50%;
  }

  100% {
    background-position: 0 50%;
  }
}
</style>
