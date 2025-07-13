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
import Navbar from '../navbarComponent.vue';
import ChatBubbleComponent from '../ChatBubbleComponent.vue';


const appContainer = ref(null);
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
  /* --- 1. BÉISBOL --- */
  {
    id: 1,
    name: 'Béisbol',
    region: 'Todo el país',
    type: 'Equipo',
    popularity: 'Muy popular',
    image: 'imagenes/DirectorioDeDeportes/baseball.jpg',
    shortDescription: 'Pasión nacional con presencia en MLB',
    description:
      'El béisbol es el deporte insignia dominicano. Desde ligas infantiles hasta la LIDOM, forma parte de la identidad cultural y ha producido decenas de peloteros de Grandes Ligas.',
    requirements: [
      'Guante y bate reglamentarios',
      'Pelota de béisbol',
      'Zapatos con tacos',
      'Casco para batear'
    ],
    places: [
      {
        name: 'Estadio Quisqueya Juan Marichal',
        location: 'Santo Domingo',
        cost: 'Boletas RD$150 – 1 550 (según sección y rival)',
        website: 'https://quisqueya.com'
      },
      {
        name: 'Play municipal Batey Esperanza',
        location: 'San Pedro de Macorís',
        cost: 'Gratis (ligas barriales)'
      }
    ]
  },

  /* --- 2. DOMINÓ --- */
  {
    id: 2,
    name: 'Dominó',
    region: 'Todo el país',
    type: 'Parejas',
    popularity: 'Muy popular',
    image: 'imagenes/DirectorioDeDeportes/Domino.jpg',
    shortDescription: 'Juego de mesa emblemático',
    description:
      'El dominó es la actividad social por excelencia: se juega en parques, colmados y clubes. Existen torneos municipales y nacionales organizados por la Asociación de Clubes de Dominó.',
    requirements: ['Juego de 28 fichas', 'Mesa o superficie plana', '4 jugadores'],
    places: [
      {
        name: 'Parque Colón',
        location: 'Zona Colonial, Santo Domingo',
        cost: 'Gratis (mesas públicas)'
      },
      {
        name: 'Club de Dominó Santiago',
        location: 'Santiago de los Caballeros',
        cost: 'RD$500 membresía anual'
      }
    ]
  },

  /* --- 3. WINDSURF / KITESURF --- */
  {
    id: 3,
    name: 'Windsurf y Kitesurf',
    region: 'Cabarete, Puerto Plata',
    type: 'Individual',
    popularity: 'Popular entre locales y turistas',
    image: 'imagenes/DirectorioDeDeportes/WindsurfyKitesurf.jpg',
    shortDescription: 'Capital del viento en el Caribe',
    description:
      'Cabarete es reconocido mundialmente por sus vientos consistentes y su arrecife protector. Escuelas certificadas ofrecen cursos para todos los niveles durante todo el año.',
    requirements: [
      'Tabla y vela/cometa',
      'Arnés',
      'Chaleco salvavidas',
      'Neopreno (invierno)'
    ],
    places: [
      {
        name: 'Kite Beach (Kite Club / Champion Kite School)',
        location: 'Cabarete',
        cost: 'Clases desde RD$3 200 (2 h)',
        website: 'https://championkite.com'
      },
      {
        name: 'Vela Cabarete',
        location: 'Playa Cabarete',
        cost: 'Alquiler equipo RD$4 000/día',
        website: 'https://velacabarete.com'
      }
    ]
  },

  /* --- 4. SÓFTBOL --- */
  {
    id: 4,
    name: 'Sóftbol',
    region: 'Santo Domingo y Santiago',
    type: 'Equipo',
    popularity: 'Popular',
    image: 'imagenes/DirectorioDeDeportes/Sofbol.jpg',
    shortDescription: 'Variante recreativa del béisbol',
    description:
      'Muy practicado en ligas empresariales y universitarias; se juega con pelota más grande y lanzamientos sub‑mano.',
    requirements: ['Guante', 'Bate especial', 'Pelota de sóftbol', 'Zapatos con tacos'],
    places: [
      {
        name: 'Campo de Sóftbol – Centro Olímpico',
        location: 'Santo Domingo',
        cost: 'Entrenamiento gratuito; ligas desde RD$1 500 / temporada'
      },
      {
        name: 'Play Los Mameyes',
        location: 'Santo Domingo Este',
        cost: 'Gratis (uso comunitario)'
      }
    ]
  },

  /* --- 5. PESCA DEPORTIVA --- */
  {
    id: 5,
    name: 'Pesca Deportiva',
    region: 'La Romana, Samaná, Punta Cana',
    type: 'Individual/Grupo',
    popularity: 'Media',
    image: 'imagenes/DirectorioDeDeportes/Pesca.jpg',
    shortDescription: 'Marlin, dorado y atún en aguas azules',
    description:
      'RD alberga torneos internacionales de marlin y pez vela. Las temporadas altas son entre junio y septiembre.',
    requirements: [
      'Caña y carrete tipo salt‑water',
      'Arnés y cinturón de pelea',
      'Licencia de pesca (en torneos)'
    ],
    places: [
      {
        name: 'Marina Casa de Campo',
        location: 'La Romana',
        cost: 'Charters desde RD$15 000 el día',
        website: 'https://casadecampo.com.do'
      },
      {
        name: 'Excursiones Samaná Fishing',
        location: 'Samaná',
        cost: 'RD$12 000 excursión compartida'
      }
    ]
  },

  /* --- 6. BALONCESTO --- */
  {
    id: 6,
    name: 'Baloncesto',
    region: 'Santo Domingo, Santiago, San Pedro',
    type: 'Equipo',
    popularity: 'Muy popular',
    image: 'imagenes/DirectorioDeDeportes/Baloncesto.jpg',
    shortDescription: 'Canastas en cada barrio',
    description:
      'La influencia NBA se siente en todo el país; existen ligas escolares y la Liga Nacional de Baloncesto (LNB) profesional.',
    requirements: ['Balón oficial', 'Zapatillas', 'Cancha'],
    places: [
      {
        name: 'Palacio de los Deportes',
        location: 'Santo Domingo',
        cost: 'Boletas desde RD$200'
      },
      {
        name: 'Canchas Parque Mirador Sur',
        location: 'Santo Domingo',
        cost: 'Gratis (uso abierto)'
      }
    ]
  },

  /* --- 7. VOLEIBOL --- */
  {
    id: 7,
    name: 'Voleibol',
    region: 'Playas y clubes nacionales',
    type: 'Equipo',
    popularity: 'Media‑Alta',
    image: 'imagenes/DirectorioDeDeportes/Volleyball.jpg',
    shortDescription: 'Playero y de sala con tradición olímpica',
    description:
      'El voleibol femenino es potencia en NORCECA; en playas de Boca Chica y Juan Dolio se celebran torneos open.',
    requirements: ['Balón de vóley', 'Red', 'Calzado (o pies descalzos en playa)'],
    places: [
      {
        name: 'Playa Boca Chica (cancha pública)',
        location: 'Boca Chica',
        cost: 'Gratis (torneos veraniegos)'
      },
      {
        name: 'Pabellón de Voleibol – Centro Olímpico',
        location: 'Santo Domingo',
        cost: 'RD$500 por sesión de entrenamiento'
      }
    ]
  },

  /* --- 8. ATLETISMO --- */
  {
    id: 8,
    name: 'Atletismo',
    region: 'Santo Domingo',
    type: 'Individual',
    popularity: 'Media',
    image: 'imagenes/DirectorioDeDeportes/Atletismo.jpg',
    shortDescription: 'Velocidad y fondo dominicanos',
    description:
      'La pista Félix Sánchez ha moldeado medallistas olímpicos; la práctica recreativa es gratuita en horarios abiertos.',
    requirements: ['Zapatillas de correr', 'Ropa ligera'],
    places: [
      {
        name: 'Pista Félix Sánchez – Centro Olímpico',
        location: 'Santo Domingo',
        cost: 'Gratis para público (horarios libres)'
      },
      {
        name: 'Avenida de la Salud (Mirador Sur)',
        location: 'Santo Domingo',
        cost: 'Gratis (circuito 5 km)'
      }
    ]
  },

  /* --- 9. BOXEO --- */
  {
    id: 9,
    name: 'Boxeo',
    region: 'Santo Domingo, San Cristóbal',
    type: 'Individual',
    popularity: 'Alta',
    image: 'imagenes/DirectorioDeDeportes/Boxeo.jpg',
    shortDescription: 'Semillero de campeones mundiales',
    description:
      'Gimnasios públicos y de federación ofrecen programas gratuitos para niños y jóvenes; figuras como Joan Guzmán salieron de aquí.',
    requirements: ['Guantes homologados', 'Vendas', 'Bucal'],
    places: [
      {
        name: 'Gimnasio Nacional de Boxeo',
        location: 'Centro Olímpico, Santo Domingo',
        cost: 'Gratis (programa MIDEREC)'
      }
    ]
  },

  /* --- 10. GOLF --- */
  {
    id: 10,
    name: 'Golf',
    region: 'Punta Cana, La Romana',
    type: 'Individual',
    popularity: 'Media‑Alta',
    image: 'imagenes/DirectorioDeDeportes/Golf.jpg',
    shortDescription: 'Campos de clase mundial frente al mar',
    description:
      'RD es destino top del Caribe con diseños de Nicklaus, Dye y Fazio. Punta Espada y Teeth of the Dog figuran en rankings globales.',
    requirements: ['Juego de palos', 'Bolas', 'Zapatos con soft spikes'],
    places: [
      {
        name: 'Punta Espada Golf Club',
        location: 'Cap Cana, Punta Cana',
        cost: 'Green‑fee desde RD$8 000',
        website: 'https://puntaspada.com'
      },
      {
        name: 'Teeth of the Dog',
        location: 'Casa de Campo',
        cost: 'Green‑fee desde RD$6 500',
        website: 'https://casadecampo.com.do'
      }
    ]
  },

  /* --- 11. SURF --- */
  {
    id: 11,
    name: 'Surf',
    region: 'Playa Encuentro, Macao, Cabarete',
    type: 'Individual',
    popularity: 'Creciente',
    image: 'imagenes/DirectorioDeDeportes/Surf.jpg',
    shortDescription: 'Olas todo el año en la costa norte',
    description:
      'Playa Encuentro es considerada la “cuna del surf dominicano”. Escuelas profesionales enseñan desde principiantes hasta nivel avanzado.',
    requirements: ['Tabla de surf', 'Leash', 'Rashguard o neopreno'],
    places: [
      {
        name: 'Cabarete Surf Company – Encuentro',
        location: 'Puerto Plata',
        cost: 'Lección 2 h RD$2 800',
        website: 'https://cabaretesurf.com'
      }
    ]
  },

  /* --- 12. TENIS --- */
  {
    id: 12,
    name: 'Tenis',
    region: 'Santo Domingo, Santiago',
    type: 'Individual/Parejas',
    popularity: 'Media',
    image: 'imagenes/DirectorioDeDeportes/Tenis.jpg',
    shortDescription: 'Canchas de arcilla y hard court',
    description:
      'Clubes sociales albergan academias; la Federación Dominicana organiza torneos FUT y Challenger.',
    requirements: ['Raqueta', 'Pelotas', 'Zapatillas específicas'],
    places: [
      {
        name: 'Santo Domingo Tennis Club “La Bocha”',
        location: 'Santo Domingo',
        cost: 'Invitados RD$300‑1 000/h',
        website: 'http://labocha.org'
      }
    ]
  },

  /* --- 13. CICLISMO --- */
  {
    id: 13,
    name: 'Ciclismo',
    region: 'Jarabacoa, Constanza, Sto. Dgo.',
    type: 'Individual/Grupo',
    popularity: 'Media',
    image: 'imagenes/DirectorioDeDeportes/Ciclismo.jpg',
    shortDescription: 'Rutas de montaña y carretera',
    description:
      'La Cordillera Central ofrece ascensos exigentes; la Vuelta Independencia es la prueba reina del calendario nacional.',
    requirements: ['Bicicleta ajustada', 'Casco obligatorio', 'Kit de reparación'],
    places: [
      {
        name: 'Ruta Jarabacoa – Constanza (Carretera La Vega‑El Río)',
        location: 'Jarabacoa',
        cost: 'Gratis (ruta escénica, 60 km ida)'
      }
    ]
  },

  /* --- 14. JUDO --- */
  {
    id: 14,
    name: 'Judo',
    region: 'Santo Domingo y Santiago',
    type: 'Individual',
    popularity: 'Media',
    image: 'imagenes/DirectorioDeDeportes/Judo.jpg',
    shortDescription: 'Arte marcial olímpico',
    description:
      'Dojos federados ofrecen clases infantiles y adultas; la selección nacional compite en Panamericanos y Centroamericanos.',
    requirements: ['Judogi', 'Cinturón'],
    places: [
      {
        name: 'Dojo Central – Federación de Judo',
        location: 'Centro Olímpico, Santo Domingo',
        cost: 'RD$1 500/mes'
      }
    ]
  },

  /* --- 15. EQUITACIÓN --- */
  {
    id: 15,
    name: 'Equitación',
    region: 'Santo Domingo, Bonao',
    type: 'Individual',
    popularity: 'Media',
    image: 'imagenes/DirectorioDeDeportes/Equitacion.jpg',
    shortDescription: 'Salto y adiestramiento clásico',
    description:
      'Clubes hípicos ofrecen salto, doma clásica y paseos guiados; carreras ecuestres se celebran los domingos.',
    requirements: ['Casco ecuestre', 'Bota y pantalón de montar'],
    places: [
      {
        name: 'Club Hípico Santo Domingo',
        location: 'Santo Domingo',
        cost: 'Lección RD$2 000',
        website: 'https://clubhipico.com.do'
      }
    ]
  },

  /* --- 16. PALO ENSEBADO --- */
  {
    id: 16,
    name: 'Palo Ensebado',
    region: 'San Juan de la Maguana',
    type: 'Individual',
    popularity: 'Tradicional',
    image: 'https://deblogsyjuegos.wordpress.com/wp-content/uploads/2015/10/10999718_1071777816183272_8268205380560369201_o.jpg',
    shortDescription: 'Juego patrimonial en fiestas patronales',
    description:
      'Competencia donde se escala un tronco engrasado para alcanzar premios. Suele celebrarse cada junio en las fiestas de San Juan Bautista.',
    requirements: ['Poste de 6 m', 'Grasa', 'Premio en la cima'],
    places: [
      {
        name: 'Fiestas Patronales de San Juan',
        location: 'San Juan de la Maguana',
        cost: 'Gratis para público'
      }
    ]
  },

  /* --- 18. BUCEO --- */
  {
    id: 18,
    name: 'Buceo',
    region: 'Bayahibe, Sosúa, Punta Cana',
    type: 'Individual/Grupo',
    popularity: 'Alta en zonas turísticas',
    image: 'imagenes/DirectorioDeDeportes/Buceo.jpg',
    shortDescription: 'Arrecifes y pecios del Caribe',
    description:
      'Bayahibe es la base para el Parque Cotubanamá y naufragios como “Saint George”. Centros PADI ofrecen cursos desde Discover hasta Divemaster.',
    requirements: ['Certificación PADI (para buceo guiado >12 m)', 'Equipo completo o alquiler'],
    places: [
      {
        name: 'Coral Point Diving',
        location: 'Bayahibe',
        cost: 'Inmersión guiada RD$4 500',
        website: 'https://coralpointdiving.com'
      }
    ]
  },

  /* --- 19. PARAPENTE --- */
  {
    id: 19,
    name: 'Parapente',
    region: 'Constanza y Jarabacoa',
    type: 'Individual',
    popularity: 'Emergente',
    image: 'imagenes/DirectorioDeDeportes/Parapente.jpg',
    shortDescription: 'Vuelos en la Cordillera Central',
    description:
      'Tándems certificados despegan desde La Vega o El Jamito. Condiciones térmicas ideales casi todo el año.',
    requirements: ['Arnés y vela homologada', 'Casco', 'Paracaídas de emergencia'],
    places: [
      {
        name: 'EcoParapente RD',
        location: 'Jarabacoa',
        cost: 'Vuelo tándem RD$6 000',
        website: 'https://ecoparapenterd.com'
      }
    ]
  },

  /* --- 20. RAFTING YAQUE --- */
  {
    id: 20,
    name: 'Yaque Rafting',
    region: 'Jarabacoa',
    type: 'Grupo',
    popularity: 'Alta en temporada',
    image: 'imagenes/DirectorioDeDeportes/YaqueRafting.jpg',
    shortDescription: 'Rápidos clase II‑IV',
    description:
      'El río Yaque del Norte es el más largo del Caribe; operadores certificados conducen balsas en secciones seguras para principiantes y avanzados.',
    requirements: ['Chaleco salvavidas', 'Casco', 'Remo'],
    places: [
      {
        name: 'Rancho Baiguate',
        location: 'Jarabacoa',
        cost: 'Paquete rafting RD$3 500',
        website: 'https://ranchobaiguate.com'
      }
    ]
  },

  /* --- 21. CARRERAS DE CABALLOS --- */
  {
    id: 21,
    name: 'Carreras de Caballos',
    region: 'Santo Domingo, Santiago',
    type: 'Individual',
    popularity: 'Tradicional',
    image: 'imagenes/DirectorioDeDeportes/CarreraDeCaballos.jpg',
    shortDescription: 'Velocidad en pista de arena',
    description:
      'El Hipódromo V Centenario acoge reuniones semanales; también se realizan “corridas” populares en el interior.',
    requirements: ['Caballo inscrito', 'Jinete licenciado'],
    places: [
      {
        name: 'Hipódromo V Centenario',
        location: 'Santo Domingo',
        cost: 'Entrada RD$200',
        website: 'https://hipodromord.com'
      }
    ]
  },

  /* --- 22. TIRO DEPORTIVO --- */
  {
    id: 22,
    name: 'Tiro Deportivo',
    region: 'Santo Domingo, Santiago',
    type: 'Individual',
    popularity: 'Media',
    image: 'imagenes/DirectorioDeDeportes/TiroDeportivo.jpg',
    shortDescription: 'Precisión con pistola, rifle y escopeta',
    description:
      'Clubes federados cumplen normas ISSF; se compite en modalidades de 10 m / 25 m / 50 m.',
    requirements: ['Arma registrada', 'Protección auditiva y ocular', 'Licencia de tirador'],
    places: [
      {
        name: 'Club de Tiro Santo Domingo',
        location: 'Santo Domingo Este',
        cost: 'RD$1 500 por sesión',
        website: 'https://clubtiro.com.do'
      }
    ]
  },

  /* --- 23. YOGA EN PLAYA --- */
  {
    id: 23,
    name: 'Yoga en Playa',
    region: 'Punta Cana, Cabarete, Las Terrenas',
    type: 'Individual/Grupo',
    popularity: 'Creciente',
    image: 'imagenes/DirectorioDeDeportes/YogaEnPlaya.jpg',
    shortDescription: 'Bienestar con el sonido del mar',
    description:
      'Clases al amanecer o atardecer combinan vinyasa suave y meditación guiada. Populares entre turistas de bienestar.',
    requirements: ['Mat antideslizante', 'Ropa cómoda'],
    places: [
      {
        name: 'Yoga Punta Cana',
        location: 'Playa Bávaro',
        cost: 'Clase grupal RD$800',
        website: 'https://yogapuntacana.com'
      }
    ]
  },

  /* --- 24. PÁDEL --- */
  {
    id: 24,
    name: 'Pádel',
    region: 'Santo Domingo, Punta Cana',
    type: 'Parejas',
    popularity: 'Emergente',
    image: 'imagenes/DirectorioDeDeportes/Padel.jpg',
    shortDescription: 'Deporte de raqueta en expansión',
    description:
      'Desde 2021 la Federación Dominicana organiza rankings; clubes invierten en canchas panorámicas.',
    requirements: ['Pala de pádel', 'Pelotas presurizadas', 'Zapatillas de court'],
    places: [
      {
        name: 'Padel Center SD',
        location: 'Santo Domingo',
        cost: 'Alquiler cancha RD$1 200/h',
        website: 'https://padelcenterrd.com'
      }
    ]
  },

  /* --- 25. ULTIMATE FRISBEE --- */
  {
    id: 25,
    name: 'Ultimate Frisbee',
    region: 'Santo Domingo, Santiago',
    type: 'Equipo',
    popularity: 'Emergente',
    image: 'imagenes/DirectorioDeDeportes/UltimateFrisbee.jpg',
    shortDescription: 'Espíritu deportivo sin árbitros',
    description:
      'Universidades como INTEC y PUCMM impulsan ligas; el juego combina resistencia, estrategia y fair‑play.',
    requirements: ['Disco oficial (175 g)', 'Conos para marcar líneas'],
    places: [
      {
        name: 'Parque Mirador Sur – campo central',
        location: 'Santo Domingo',
        cost: 'Gratis'
      }
    ]
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
</style>