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
    <div class="controls">
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
    </div>

    <!-- Contenido principal -->
    <main class="main-content">
      <!-- Listado de deportes -->
      <div class="sports-list" v-if="!selectedSport">
        <div v-for="sport in filteredSports" :key="sport.id" class="sport-card" @click="selectSport(sport)">
          <div class="sport-image">
            <img :src="sport.image" :alt="sport.name">
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
          <div class="detail-meta">
            <span><i class="fas fa-map-marker-alt"></i> {{ selectedSport.region }}</span>
            <span><i class="fas fa-users"></i> {{ selectedSport.type }}</span>
            <span><i class="fas fa-star"></i> {{ selectedSport.popularity }}</span>
          </div>
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
                <!-- <a v-if="place.website" :href="place.website" target="_blank">
                  <i class="fas fa-globe"></i> Sitio web
                </a> -->
              </div>
            </div>
          </div>


        </div>
      </div>
    </main>

  </div>
</template>


<script setup>
import { ref, computed } from 'vue';
import Navbar from '../navbarComponent.vue';
  

// export default {
//   name: 'Directorio',
//   components: {
//     Navbar
//   },

const searchTerm = ref('')
const activeRegion = ref('Todas')
const selectedSport = ref(null);

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

const sports = ref([
  {
    id: 1,
    name: 'Béisbol',
    region: 'Todo el país',
    type: 'Equipo',
    popularity: 'Muy popular',
    image: 'imagenes/DirectorioDeDeportes/baseball.jpg',
    shortDescription: 'El deporte rey de República Dominicana',
    description: 'El béisbol es el deporte más popular en República Dominicana, con una gran tradición y numerosos peloteros en las Grandes Ligas. Cada ciudad tiene sus equipos locales y ligas amateur.',
    requirements: [
      'Guante de béisbol',
      'Bate',
      'Pelota',
      'Zapatos de tacos',
      'Casco para batear'
    ],
    places: [
      {
        name: 'Estadio Quisqueya Juan Marichal',
        location: 'Santo Domingo',
        cost: 'Desde RD$300 para partidos profesionales',
        website: 'https://quisqueya.com'
      },
      {
        name: 'Academia de Béisbol Prospecto',
        location: 'San Pedro de Macorís',
        cost: 'RD$2,000/mes para entrenamiento',
        website: 'https://prospectoacademy.com'
      }
    ],
  },
  {
    id: 2,
    name: 'Dominó',
    region: 'Todo el país',
    type: 'Parejas',
    popularity: 'Muy popular',
    image: 'imagenes/DirectorioDeDeportes/Domino.jpg',
    shortDescription: 'El juego nacional dominicano',
    description: 'Más que un juego, el dominó es una tradición social en RD. Se juega en parques, colmados y casas por todo el país, con torneos importantes y mucha pasión.',
    requirements: [
      'Juego de dominó (28 fichas)',
      'Mesa adecuada',
      '4 jugadores (2 parejas)'
    ],
    places: [
      {
        name: 'Parque Colón',
        location: 'Zona Colonial, Santo Domingo',
        cost: 'Gratis'
      },
      {
        name: 'Club de Domino RD',
        location: 'Santiago de los Caballeros',
        cost: 'RD$500 membresía anual'
      }
    ],
  },
  {
    id: 3,
    name: 'Windsurf y Kitesurf',
    region: 'Cabarete, Puerto Plata',
    type: 'Individual',
    popularity: 'Popular entre turistas y locales',
    image: 'imagenes/DirectorioDeDeportes/WindsurfyKitesurf.jpg',
    shortDescription: 'Deporte acuático de aventura en las playas del norte',
    description: 'Cabarete es conocida internacionalmente por sus condiciones ideales para windsurf y kitesurf, con escuelas que ofrecen clases para todos los niveles.',
    requirements: [
      'Tabla de windsurf/kitesurf',
      'Vela/cometa',
      'Traje de neopreno (opcional)',
      'Chaleco salvavidas'
    ],
    places: [
      {
        name: 'Cabarete Kite Beach',
        location: 'Cabarete, Puerto Plata',
        cost: 'RD$3,500 por clase de 2 horas',
        website: 'https://cabaretekite.com'
      },
      {
        name: 'Vela Cabarete',
        location: 'Playa de Cabarete',
        cost: 'RD$4,000 alquiler equipo por día',
        website: 'https://velacabarete.com'
      }
    ],
  },
  {
    id: 4,
    name: 'Sofbol',
    region: 'Santo Domingo y Santiago',
    type: 'Equipo',
    popularity: 'Popular',
    image: 'imagenes/DirectorioDeDeportes/Sofbol.jpg',
    shortDescription: 'Variante del béisbol muy practicada en ligas locales',
    description: 'El sóftbol es muy popular en ligas recreativas y empresariales, especialmente en Santo Domingo. Se juega con una pelota más grande y lanzamiento por debajo del brazo.',
    requirements: [
      'Guante de sóftbol',
      'Bate de sóftbol',
      'Pelota de sóftbol',
      'Zapatos de tacos'
    ],
    places: [
      {
        name: 'Parque Mirador Sur',
        location: 'Santo Domingo',
        cost: 'Gratis (ligas organizadas tienen costos)'
      },
      {
        name: 'Complejo Deportivo de Santiago',
        location: 'Santiago',
        cost: 'RD$1,500 por temporada en ligas locales'
      }
    ],
  },
  {
    id: 5,
    name: 'Pesca Deportiva',
    region: 'Samaná, Punta Cana, Barahona',
    type: 'Individual/Grupo',
    popularity: 'Media',
    image: 'imagenes/DirectorioDeDeportes/Pesca.jpg',
    shortDescription: 'Deporte de aventura en las costas dominicanas',
    description: 'RD ofrece excelentes lugares para pesca deportiva, especialmente de pez vela, dorado y atún. Torneos internacionales se realizan cada año.',
    requirements: [
      'Caña de pescar',
      'Carrete',
      'Carnadas',
      'Licencia de pesca (para torneos)'
    ],
    places: [
      {
        name: 'Marina de Casa de Campo',
        location: 'La Romana',
        cost: 'Desde RD$15,000 por excursión',
        website: 'https://casadecampo.com.do'
      },
      {
        name: 'Pesca en Samaná',
        location: 'Samaná',
        cost: 'RD$12,000 por excursión compartida'
      }
    ],
  },
  {
    id: 6,
    name: 'Baloncesto',
    region: 'Santo Domingo, Santiago, San Pedro de Macorís',
    type: 'Equipo',
    popularity: 'Muy popular',
    image: 'imagenes/DirectorioDeDeportes/Baloncesto.jpg',
    shortDescription: 'Segundo deporte más popular en RD',
    description: 'Con fuerte influencia de la NBA, tiene ligas locales y canchas en casi todos los barrios. La Liga Nacional de Baloncesto (LNB) es la competencia profesional.',
    requirements: [
      'Balón de baloncesto',
      'Zapatos deportivos',
      'Cancha adecuada'
    ],
    places: [
      {
        name: 'Palacio de los Deportes Virgilio Travieso Soto',
        location: 'Santo Domingo',
        cost: 'RD$200 entrada general'
      },
      {
        name: 'Polideportivo de San Pedro de Macorís',
        location: 'San Pedro de Macorís',
        cost: 'Gratis para entrenamientos'
      }
    ],
  },
  {
    id: 7,
    name: 'Volleyball',
    region: 'Boca Chica, Juan Dolio, Punta Cana',
    type: 'Equipo',
    popularity: 'Media-Alta',
    image: 'imagenes/DirectorioDeDeportes/Volleyball.jpg',
    shortDescription: 'Popular en playas y ligas universitarias',
    description: 'El voleibol playero es común en zonas costeras, mientras el de sala se practica en clubes y universidades. Existen ligas competitivas nacionales.',
    requirements: [
      'Balón de volleyball',
      'Red',
      'Zapatos deportivos'
    ],
    places: [
      {
        name: 'Playa Boca Chica',
        location: 'Boca Chica',
        cost: 'Gratis (torneos informales)'
      },
      {
        name: 'Centro Olímpico Juan Pablo Duarte',
        location: 'Santo Domingo',
        cost: 'RD$500 por sesión de entrenamiento'
      }
    ],
  },
  {
    id: 8,
    name: 'Atletismo',
    region: 'San Cristóbal, Santo Domingo',
    type: 'Individual',
    popularity: 'Media',
    image: 'imagenes/DirectorioDeDeportes/Atletismo.jpg',
    shortDescription: 'Con tradición de medallistas olímpicos',
    description: 'RD ha destacado en carreras de velocidad y maratones. Luguelín Santos (medallista olímpico) es uno de sus máximos exponentes.',
    requirements: [
      'Zapatos para correr',
      'Ropa deportiva'
    ],
    places: [
      {
        name: 'Pista Atlética del Centro Olímpico',
        location: 'Santo Domingo',
        cost: 'Gratis para entrenamientos'
      }
    ],
  },
  {
    id: 9,
    name: 'Boxeo',
    region: 'Santo Domingo, San Cristóbal, La Romana',
    type: 'Individual',
    popularity: 'Alta',
    image: 'imagenes/DirectorioDeDeportes/Boxeo.jpg',
    shortDescription: 'Tradición de campeones mundiales',
    description: 'Con figuras como Joan Guzmán y Javier Fortuna. El Gimnasio Nacional ofrece programas gratuitos para jóvenes.',
    requirements: [
      'Guantes de boxeo',
      'Vendas',
      'Saco de boxeo'
    ],
    places: [
      {
        name: 'Gimnasio de Boxeo del Centro Olímpico',
        location: 'Santo Domingo',
        cost: 'Gratis'
      }
    ],
  },
  {
    id: 10,
    name: 'Golf',
    region: 'Punta Cana, La Romana, Santo Domingo',
    type: 'Individual',
    popularity: 'Media-Alta',
    image: 'imagenes/DirectorioDeDeportes/Golf.jpg',
    shortDescription: 'En resorts y clubes exclusivos',
    description: 'RD cuenta con campos de clase mundial como Punta Espada (top 10 global) y Teeth of the Dog. Ideal para turismo deportivo.',
    requirements: [
      'Palos de golf',
      'Zapatos especiales',
      'Bolas'
    ],
    places: [
      {
        name: 'Punta Espada Golf Club',
        location: 'Punta Cana',
        cost: 'RD$8,000 por ronda',
        website: 'https://puntaspada.com'
      },
      {
        name: 'Teeth of the Dog',
        location: 'Casa de Campo',
        cost: 'RD$6,500 por ronda',
        website: 'https://casadecampo.com.do'
      }
    ],
  },
  {
    id: 11,
    name: 'Surf',
    region: 'Cabarete, Encuentro, Macao',
    type: 'Individual',
    popularity: 'Creciente',
    image: 'imagenes/DirectorioDeDeportes/Surf.jpg',
    shortDescription: 'Aprovechando las olas del Caribe',
    description: 'Playa Encuentro en Puerto Plata es el epicentro del surf nacional, con escuelas para todos los niveles y competencias internacionales.',
    requirements: [
      'Tabla de surf',
      'Traje de neopreno (opcional)',
      'Leash'
    ],
    places: [
      {
        name: 'Playa Encuentro',
        location: 'Puerto Plata',
        cost: 'RD$2,500 por clase',
        website: 'https://surfencuentro.com'
      }
    ],
  },
  {
    id: 12,
    name: 'Tenis',
    region: 'Santo Domingo, Santiago, Punta Cana',
    type: 'Individual/Parejas',
    popularity: 'Media',
    image: 'imagenes/DirectorioDeDeportes/Tenis.jpg',
    shortDescription: 'Practicado en clubes privados',
    description: 'Con canchas de calidad en resorts y clubes exclusivos. La Asociación Dominicana de Tenis organiza torneos nacionales.',
    requirements: [
      'Raqueta',
      'Pelotas',
      'Zapatos de tenis'
    ],
    places: [
      {
        name: 'Club de Tenis de Santo Domingo',
        location: 'La Julia',
        cost: 'RD$1,500 por hora',
        website: 'https://clubtenis.com.do'
      }
    ],
  },
  {
    id: 13,
    name: 'Ciclismo',
    region: 'Jarabacoa, Constanza, Santo Domingo',
    type: 'Individual/Grupo',
    popularity: 'Media',
    image: 'imagenes/DirectorioDeDeportes/Ciclismo.jpg',
    shortDescription: 'Rutas de montaña y competencias',
    description: 'Las montañas del Cibao ofrecen rutas desafiantes. La Vuelta a la Independencia es el evento más importante.',
    requirements: [
      'Bicicleta',
      'Casco',
      'Ropa adecuada'
    ],
    places: [
      {
        name: 'Ruta Jarabacoa-Constanza',
        location: 'Jarabacoa',
        cost: 'Gratis'
      }
    ],
  },
  {
    id: 14,
    name: 'Judo',
    region: 'Santo Domingo, Santiago',
    type: 'Individual',
    popularity: 'Media',
    image: 'imagenes/DirectorioDeDeportes/Judo.jpg',
    shortDescription: 'Arte marcial olímpico',
    description: 'Practicado en academias y centros deportivos. La Federación Dominicana organiza torneos nacionales.',
    requirements: [
      'Kimono',
      'Tatami'
    ],
    places: [
      {
        name: 'Dojo Central de Judo',
        location: 'Santo Domingo',
        cost: 'RD$1,500/mes'
      }
    ],
  },
  {
    id: 15,
    name: 'Equitación',
    region: 'Santo Domingo, Bonao',
    type: 'Individual',
    popularity: 'Media',
    image: 'imagenes/DirectorioDeDeportes/Equitacion.jpg',
    shortDescription: 'Deporte ecuestre tradicional',
    description: 'Practicado en clubes hípicos y fincas privadas. Las carreras de caballos son populares los domingos.',
    requirements: [
      'Equipo de montar',
      'Caballo'
    ],
    places: [
      {
        name: 'Club Hípico Santo Domingo',
        location: 'Santo Domingo',
        cost: 'RD$2,000 por clase',
        website: 'https://clubhipico.com.do'
      }
    ],
  },
  {
    id: 16,
    name: 'Palo Ensebado',
    region: 'San Juan de la Maguana',
    type: 'Individual',
    popularity: 'Tradicional',
    image: 'https://deblogsyjuegos.wordpress.com/wp-content/uploads/2015/10/10999718_1071777816183272_8268205380560369201_o.jpg?w=1536&h=1500&crop=1',
    shortDescription: 'Juego tradicional de destreza física',
    description: 'Competencia donde participantes escalan un poste engrasado para alcanzar premios en la cima. Muy popular en fiestas patronales como las de San Juan Bautista.',
    requirements: [
      'Poste de madera de 5-6 metros',
      'Grasa o aceite',
      'Premios (generalmente dinero en efectivo)'
    ],
    places: [
      {
        name: 'Fiestas Patronales de San Juan',
        location: 'San Juan de la Maguana',
        cost: 'Gratis para espectadores'
      }
    ],
  },
  // {
  //   id: 17,
  //   name: 'Gallera',
  //   region: 'Santiago, San Cristóbal, Higüey',
  //   type: 'Individual',
  //   popularity: 'Tradicional',
  //   image: 'http://e00-elmundo.uecdn.es/assets/multimedia/imagenes/2017/02/24/14879512766963.jpg',
  //   shortDescription: 'Deporte tradicional controvertido',
  //   description: 'Aunque polémico, es parte de la cultura rural dominicana. Las peleas de gallos se realizan en coliseos especializados llamados "galleras".',
  //   requirements: [
  //     'Gallos de pelea',
  //     'Espuelas especiales',
  //     'Gallera autorizada'
  //   ],
  //   places: [
  //     {
  //       name: 'Coliseo Gallístico de Santiago',
  //       location: 'Santiago',
  //       cost: 'RD$300-1,000 entrada'
  //     }
  //   ],
  // },
  {
    id: 18,
    name: 'Buceo',
    region: 'Bayahibe, Punta Cana, Sosúa',
    type: 'Individual/Grupo',
    popularity: 'Alta en zonas turísticas',
    image: 'imagenes/DirectorioDeDeportes/Buceo.jpg',
    shortDescription: 'Exploración del Caribe submarino',
    description: 'RD ofrece arrecifes vibrantes y naufragios históricos. Bayahibe es el punto de partida para el Parque Nacional Cotubanamá con su famoso "Wall".',
    requirements: [
      'Certificación PADI (para buceo profundo)',
      'Equipo de snorkel/buceo',
      'Guía local'
    ],
    places: [
      {
        name: 'Scuba Dive Bayahibe',
        location: 'Bayahibe',
        cost: 'RD$4,500 por inmersión',
        website: 'https://scubadivebayahibe.com'
      }
    ],
  },
  {
    id: 19,
    name: 'Parapente',
    region: 'Constanza, Jarabacoa',
    type: 'Individual',
    popularity: 'Emergente',
    image: 'imagenes/DirectorioDeDeportes/Parapente.jpg',
    shortDescription: 'Vuelo libre en las montañas',
    description: 'Las montañas de Constanza y Jarabacoa ofrecen condiciones ideales para parapente. Escuelas certificadas ofrecen cursos y vuelos tandem.',
    requirements: [
      'Equipo de parapente',
      'Casco',
      'Paracaídas de emergencia'
    ],
    places: [
      {
        name: 'EcoParapente RD',
        location: 'Jarabacoa',
        cost: 'RD$6,000 vuelo tandem',
        website: 'https://ecoparapenterd.com'
      }
    ],
  },
  {
    id: 20,
    name: 'Yaque Rafting',
    region: 'Jarabacoa',
    type: 'Grupo',
    popularity: 'Alta en temporada',
    image: 'imagenes/DirectorioDeDeportes/YaqueRafting.jpg',
    shortDescription: 'Aventura en aguas blancas',
    description: 'El río Yaque del Norte ofrece rápidos clase II-IV. Operadores como Rancho Baiguate tienen paquetes para todos los niveles.',
    requirements: [
      'Chaleco salvavidas',
      'Casco',
      'Remo'
    ],
    places: [
      {
        name: 'Rancho Baiguate',
        location: 'Jarabacoa',
        cost: 'RD$3,500 por persona',
        website: 'https://ranchobaiguate.com'
      }
    ],
  },
  {
    id: 21,
    name: 'Carreras de Caballos',
    region: 'Santo Domingo, Santiago, Higüey',
    type: 'Individual',
    popularity: 'Tradicional',
    image: 'imagenes/DirectorioDeDeportes/CarreraDeCaballos.jpg',
    shortDescription: 'Emoción ecuestre dominicana',
    description: 'Las carreras se realizan en hipódromos profesionales (como el de Santo Domingo) y en eventos informales llamados "corridas" en zonas rurales.',
    requirements: [
      'Caballo de carrera',
      'Jinete profesional'
    ],
    places: [
      {
        name: 'Hipódromo V Centenario',
        location: 'Santo Domingo',
        cost: 'RD$200 entrada general',
        website: 'https://hipodromord.com'
      }
    ],
  },
  {
    id: 22,
    name: 'Tiro Deportivo',
    region: 'Santo Domingo, Santiago',
    type: 'Individual',
    popularity: 'Media',
    image: 'imagenes/DirectorioDeDeportes/TiroDeportivo.jpg',
    shortDescription: 'Precisión y concentración',
    description: 'Practicado en clubes especializados. La Federación Dominicana organiza competencias nacionales de tiro con pistola, rifle y escopeta.',
    requirements: [
      'Arma registrada',
      'Equipo de protección auditiva',
      'Carnet de tirador'
    ],
    places: [
      {
        name: 'Club de Tiro de Santo Domingo',
        location: 'Santo Domingo Este',
        cost: 'RD$1,500 por sesión',
        website: 'https://clubtiro.com.do'
      }
    ],
  },
  {
    id: 23,
    name: 'Yoga en Playa',
    region: 'Punta Cana, Cabarete, Las Terrenas',
    type: 'Individual/Grupo',
    popularity: 'Creciente',
    image: 'imagenes/DirectorioDeDeportes/YogaEnPlaya.jpg',
    shortDescription: 'Bienestar con vista al mar',
    description: 'Clases al amanecer o atardecer en playas paradisíacas. Combinación perfecta de deporte y relajación.',
    requirements: [
      'Mat de yoga',
      'Ropa cómoda'
    ],
    places: [
      {
        name: 'Yoga Punta Cana',
        location: 'Playa Bávaro',
        cost: 'RD$800 por clase',
        website: 'https://yogapuntacana.com'
      }
    ],
  },
  {
    id: 24,
    name: 'Padel',
    region: 'Santo Domingo, Punta Cana',
    type: 'Parejas',
    popularity: 'Emergente',
    image: 'imagenes/DirectorioDeDeportes/Padel.jpg',
    shortDescription: 'Deporte de raqueta en auge',
    description: 'Mezcla de tenis y squash, muy popular en clubes privados y resorts. La Federación Dominicana organiza torneos desde 2021.',
    requirements: [
      'Raqueta de padel',
      'Pelotas específicas'
    ],
    places: [
      {
        name: 'Padel Center SD',
        location: 'Santo Domingo',
        cost: 'RD$1,200 por hora',
        website: 'https://padelcenterrd.com'
      }
    ],
  },
  {
    id: 25,
    name: 'Ultimate Frisbee',
    region: 'Santo Domingo, Santiago',
    type: 'Equipo',
    popularity: 'Emergente',
    image: 'imagenes/DirectorioDeDeportes/UltimateFrisbee.jpg',
    shortDescription: 'Deporte universitario en crecimiento',
    description: 'Combinación de fútbol americano y baloncesto con frisbee. Popular en universidades como INTEC y PUCMM.',
    requirements: [
      'Disco volador',
      'Conos para demarcar'
    ],
    places: [
      {
        name: 'Parque Mirador Sur',
        location: 'Santo Domingo',
        cost: 'Gratis'
      }
    ],
  }
]);


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

const selectSport = (sport) => {
  selectedSport.value = sport;
  window.scrollTo({ top: 0, behavior: 'smooth' });
};


</script>




<style scoped>
@import '../../../scss/Directorio/directorio.scss';

.navbar {
    background: linear-gradient(to right, #000000, #a13300);
}
</style>