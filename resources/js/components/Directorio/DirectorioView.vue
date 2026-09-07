<template>
  <div class="sports-app" ref="scrollRoot">


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
        <button @click="volverAlListado" class="sd-back">
          <i class="fas fa-arrow-left" aria-hidden="true"></i> Volver al listado
        </button>

        <!-- Portada: el nombre del deporte va SOBRE la foto. Antes el titulo
             iba encima y la imagen debajo, asi que en pantallas medianas el
             nombre quedaba solo en una linea con mucho aire alrededor. -->
        <header class="sd-hero">
          <img :src="selectedSport.image" :alt="selectedSport.name" class="sd-hero__img">
          <div class="sd-hero__veil"></div>
          <div class="sd-hero__content">
            <h2 class="sd-hero__title">{{ selectedSport.name }}</h2>
            <div class="sd-chips">
              <span class="sd-chip" v-if="selectedSport.region">
                <i class="fas fa-map-marked-alt" aria-hidden="true"></i> {{ selectedSport.region }}
              </span>
              <span class="sd-chip" v-if="selectedSport.type">
                <i class="fas fa-users" aria-hidden="true"></i> {{ selectedSport.type }}
              </span>
              <span class="sd-chip sd-chip--accent" v-if="selectedSport.popularity">
                <i class="fas fa-fire" aria-hidden="true"></i> {{ selectedSport.popularity }}
              </span>
            </div>
          </div>
        </header>

        <!-- En escritorio la descripcion y el equipo necesario van lado a lado:
             la descripcion es larga y el equipo son items cortos, asi que en
             una sola columna la lista dejaba media pantalla vacia. -->
        <div class="sd-grid">
          <section class="sd-main">
            <p class="sd-lead" v-if="selectedSport.shortDescription">
              {{ selectedSport.shortDescription }}
            </p>
            <h3 class="sd-title">
              <i class="fas fa-circle-info" aria-hidden="true"></i> Sobre el deporte
            </h3>
            <p class="sd-text">{{ selectedSport.description }}</p>
          </section>

          <aside class="sd-aside" v-if="selectedSport.requirements?.length">
            <h3 class="sd-title">
              <i class="fas fa-clipboard-check" aria-hidden="true"></i> Qué necesitas
              <span class="sd-count">{{ selectedSport.requirements.length }}</span>
            </h3>
            <ul class="sd-gear">
              <li v-for="(item, index) in selectedSport.requirements" :key="index">
                <i class="fas fa-check" aria-hidden="true"></i>
                <span>{{ item }}</span>
              </li>
            </ul>
          </aside>
        </div>

        <section class="sd-section" v-if="selectedSport.places?.length">
          <h3 class="sd-title">
            <i class="fas fa-map-marked-alt" aria-hidden="true"></i> Dónde practicarlo
            <span class="sd-count">{{ selectedSport.places.length }}</span>
          </h3>
          <div class="sd-places">
            <article class="sd-place" v-for="place in selectedSport.places" :key="place.name">
              <h4 class="sd-place__name">{{ place.name }}</h4>
              <p class="sd-place__row" v-if="place.location">
                <i class="fas fa-location-dot" aria-hidden="true"></i> {{ place.location }}
              </p>
              <p class="sd-place__cost" v-if="place.cost">{{ place.cost }}</p>
              <!-- El "website" viene de la base de datos: solo se pinta si es
                   http(s) real, y con rel="noopener noreferrer" para que la
                   pestaña destino no pueda tocar la nuestra. Antes este enlace
                   ni se mostraba, aunque el dato ya estaba guardado. -->
              <a v-if="safeUrl(place.website)" class="sd-place__link" :href="safeUrl(place.website)" target="_blank"
                rel="noopener noreferrer">
                Visitar sitio web <i class="fas fa-arrow-up-right-from-square" aria-hidden="true"></i>
              </a>
            </article>
          </div>
        </section>
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

// Solo se pintan enlaces http(s). Sin este filtro, un "website" guardado como
// javascript:... en la base de datos se volveria un enlace ejecutable al
// hacerle click.
const safeUrl = (url) => {
  if (!url) return null;
  try {
    const parsed = new URL(String(url), window.location.origin);
    return ['http:', 'https:'].includes(parsed.protocol) ? parsed.href : null;
  } catch (error) {
    return null;
  }
};

// Punto del listado donde estaba el usuario antes de entrar a un deporte,
// para volver ahi (no al tope) al darle "Volver al listado". Quien
// realmente scrollea aqui es .sports-app (el div raiz: height:100vh +
// overflow-y:auto, ver <style> mas abajo), no la ventana ni .main-content
// (appContainer): scrollTo() sobre appContainer no hacia nada visible
// porque ese elemento no tiene su propio scroll.
const scrollRoot = ref(null);
const savedScrollTop = ref(0);

const selectSport = async (sport) => {
  if (scrollRoot.value) {
    savedScrollTop.value = scrollRoot.value.scrollTop;
  }

  selectedSport.value = sport;

  await nextTick();
  scrollRoot.value?.scrollTo({ top: 0, behavior: 'smooth' });

  // Enfocar el título del deporte para accesibilidad
  const sportTitle = document.querySelector('.sd-hero__title');
  if (sportTitle) {
    sportTitle.tabIndex = -1;
    sportTitle.focus();
  }
};

const volverAlListado = async () => {
  selectedSport.value = null;

  await nextTick();
  scrollRoot.value?.scrollTo({ top: savedScrollTop.value, behavior: 'auto' });
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

@import '../../../scss/Directorio/directorio_detalle.scss';

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
