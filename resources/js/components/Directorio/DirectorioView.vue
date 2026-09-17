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

    <!-- Buscador y filtro por categoría. Se ocultan al abrir la ficha de un
         deporte: ahi no hay nada que filtrar y solo quitarian sitio. -->
    <div class="controls" v-if="!selectedSport">
      <div class="search-container">
        <label for="buscador-deporte" class="visually-hidden">Buscar deporte</label>
        <input
          id="buscador-deporte"
          type="search"
          v-model="searchTerm"
          placeholder="Buscar deporte, lugar o disciplina..."
          class="search-input"
          autocomplete="off">
        <button
          v-if="searchTerm"
          class="search-clear"
          type="button"
          @click="searchTerm = ''"
          aria-label="Borrar búsqueda">
          <i class="fas fa-times" aria-hidden="true"></i>
        </button>
        <span class="search-button" aria-hidden="true">
          <i class="fas fa-search"></i>
        </span>
      </div>

      <div class="filter-buttons" role="group" aria-label="Filtrar por categoría">
        <button
          v-for="cat in categoriasConTodas"
          :key="cat.valor"
          type="button"
          @click="filtrarPorCategoria(cat.valor)"
          :class="{ active: activeCategory === cat.valor }"
          :aria-pressed="activeCategory === cat.valor">
          <i :class="cat.icono" aria-hidden="true"></i>
          <span>{{ cat.etiqueta }}</span>
          <span v-if="sports.length" class="filter-count">{{ cat.total }}</span>
        </button>
      </div>

      <p class="results-summary" aria-live="polite">
        <template v-if="filteredSports.length">
          {{ filteredSports.length }}
          {{ filteredSports.length === 1 ? 'deporte' : 'deportes' }}
          <template v-if="activeCategory !== 'Todas'"> en {{ activeCategory }}</template>
          <template v-if="searchTerm"> para “{{ searchTerm }}”</template>
        </template>
      </p>
    </div>

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

      <!-- Nada coincide con la busqueda o el filtro -->
      <div class="sin-resultados" v-else-if="!selectedSport && !filteredSports.length">
        <i class="fas fa-search" aria-hidden="true"></i>
        <h3>No encontramos ese deporte</h3>
        <p>
          Prueba con otra palabra o mira todas las categorías.
        </p>
        <button type="button" class="sin-resultados__btn" @click="limpiarFiltros">
          Ver todos los deportes
        </button>
      </div>

      <!-- Listado de deportes -->
      <div class="sports-list" v-else-if="!selectedSport">
        <div v-for="sport in filteredSports" :key="sport.id" class="sport-card" @click="selectSport(sport)" role="button" tabindex="0" @keydown.enter.prevent="selectSport(sport)" @keydown.space.prevent="selectSport(sport)">
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
import { mapSport, cacheDeportesValido } from '../../utils/sports';

const store = useStore();
const appContainer = ref(null);
const searchTerm = ref('')
const selectedSport = ref(null);
const loading = ref(true);

;

const sports = ref([]);

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

// Las siete categorias del directorio, en el mismo orden que el seeder
// (DeportesNuevosSeeder::CATEGORIAS). El icono es de Font Awesome, que ya
// carga la aplicacion.
const CATEGORIAS = [
  { valor: 'Pelota', etiqueta: 'Pelota', icono: 'fas fa-baseball-ball' },
  { valor: 'Contacto', etiqueta: 'Contacto', icono: 'fas fa-hand-rock' },
  { valor: 'Agua', etiqueta: 'Agua', icono: 'fas fa-water' },
  { valor: 'Resistencia', etiqueta: 'Resistencia', icono: 'fas fa-running' },
  { valor: 'Precisión', etiqueta: 'Precisión', icono: 'fas fa-bullseye' },
  { valor: 'Aventura', etiqueta: 'Aventura', icono: 'fas fa-mountain' },
  { valor: 'Bienestar', etiqueta: 'Bienestar', icono: 'fas fa-heartbeat' },
];

const activeCategory = ref('Todas');

// Quita tildes y pasa a minusculas: sin esto, buscar "natacion" no encontraria
// "Natación", que es justo lo que teclea la mayoria de la gente.
const normalizar = (texto) =>
  String(texto ?? '')
    .toLowerCase()
    .normalize('NFD')
    .replace(/[̀-ͯ]/g, '');

// Deportes que pasan SOLO el filtro de categoria. Se usa para dos cosas: como
// base del filtro de texto y para contar cuantos hay en cada boton.
const porCategoria = (categoria) =>
  categoria === 'Todas'
    ? sports.value
    : sports.value.filter((s) => s.category === categoria);

// Cada boton lleva su conteo. Se calcula sobre el catalogo completo y no sobre
// el resultado de la busqueda: el numero indica cuantos hay en esa categoria,
// no cuantos quedan tras teclear, que seria confuso.
const categoriasConTodas = computed(() => [
  { valor: 'Todas', etiqueta: 'Todas', icono: 'fas fa-th-large', total: sports.value.length },
  ...CATEGORIAS
    .map((c) => ({ ...c, total: porCategoria(c.valor).length }))
    // Una categoria sin deportes no pinta nada en la barra. Mientras la lista
    // esta vacia (cargando o si fallo la peticion) se muestran todas: antes
    // quedaba solo "Todas" y parecia que las demas no existian. La activa
    // nunca se oculta.
    .filter((c) => c.total > 0 || sports.value.length === 0 || c.valor === activeCategory.value),
]);

const filteredSports = computed(() => {
  let result = porCategoria(activeCategory.value);

  const term = normalizar(searchTerm.value).trim();

  if (term) {
    // Se busca tambien en la descripcion, la categoria y el NOMBRE DE LOS
    // LUGARES: asi "Jarabacoa" o "Centro Olimpico" encuentran los deportes que
    // se practican ahi, que es una forma muy natural de buscar.
    result = result.filter((sport) => {
      const lugares = (sport.places || [])
        .map((p) => `${p.name ?? ''} ${p.location ?? ''}`)
        .join(' ');

      return [
        sport.name,
        sport.shortDescription,
        sport.description,
        sport.region,
        sport.category,
        sport.type,
        lugares,
      ].some((campo) => normalizar(campo).includes(term));
    });
  }

  return result;
});

const filtrarPorCategoria = (categoria) => {
  activeCategory.value = categoria;
};

const limpiarFiltros = () => {
  activeCategory.value = 'Todas';
  searchTerm.value = '';
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
// para volver ahi (no al tope) al darle "Volver al listado". Quien scrollea
// es la ventana, igual que en el resto de secciones.
const savedScrollTop = ref(0);

const selectSport = async (sport) => {
  savedScrollTop.value = window.scrollY;

  selectedSport.value = sport;

  await nextTick();
  window.scrollTo({ top: 0, behavior: 'smooth' });

  // Enfocar el título del deporte para accesibilidad. Con preventScroll
  // porque enfocar arrastra el scroll hasta el elemento, y eso deshacia el
  // scrollTo de arriba: la ficha abria a media altura, con el navbar fuera.
  const sportTitle = document.querySelector('.sd-hero__title');
  if (sportTitle) {
    sportTitle.tabIndex = -1;
    sportTitle.focus({ preventScroll: true });
  }
};

const volverAlListado = async () => {
  selectedSport.value = null;

  await nextTick();
  window.scrollTo({ top: savedScrollTop.value, behavior: 'auto' });
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
  if (cacheDeportesValido(cachedSports)) {
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

/* Sin height:100vh ni overflow-y:auto. Los tenia, y eso convertia el div raiz
   en su propio contenedor de scroll dentro del scroll de la pagina: salian dos
   barras a la derecha, una encima de otra. Esta seccion scrollea con la
   ventana, como el foro y el resto. */

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
