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
    shortDescription: 'Deporte nacional con gran legado en Grandes Ligas',
    description:
      'El béisbol es considerado el deporte nacional de la República Dominicana, con una arraigada tradición que va desde las ligas infantiles hasta el circuito profesional de la LIDOM. Es fuente de identidad y orgullo nacional, además de ser el principal semillero de peloteros latinoamericanos en las Grandes Ligas de EE. UU. El país cuenta con academias afiliadas a equipos de MLB, así como con programas escolares y municipales que fomentan su práctica desde edades tempranas.',
    requirements: [
      'Guante de béisbol (según posición)',
      'Bate reglamentario (de madera o aluminio)',
      'Pelotas oficiales',
      'Zapatos con tacos',
      'Casco protector para bateo',
      'Uniforme deportivo (opcional en práctica informal)'
    ],
    places: [
      {
        name: 'Estadio Quisqueya Juan Marichal',
        location: 'Santo Domingo',
        cost: 'Boletas RD$150 a RD$1 550 (según temporada y rival)',
        website: 'https://quisqueyajuanmarichal.com'
      },
      {
        name: 'Academia de Béisbol MLB – Boca Chica',
        location: 'Boca Chica',
        cost: 'Acceso privado (academias de formación afiliadas a equipos de MLB)'
      },
      {
        name: 'Campo de Béisbol Los Trinitarios',
        location: 'Santo Domingo Este',
        cost: 'Gratis (uso comunitario y ligas menores)'
      },
      {
        name: 'Play Municipal de Esperanza',
        location: 'Valverde',
        cost: 'Gratis (ligas juveniles y torneos escolares)'
      }
    ]
  },

  /* --- 2. DOMINÓ --- */
  /* --- 2. DOMINÓ --- */
  {
    id: 2,
    name: 'Dominó',
    region: 'Todo el país',
    type: 'Parejas',
    popularity: 'Muy popular',
    image: 'imagenes/DirectorioDeDeportes/Domino.jpg',
    shortDescription: 'Juego de estrategia y tradición dominicana',
    description:
      'El dominó es un juego de mesa profundamente arraigado en la vida social dominicana. Se practica principalmente en parques, colmados, clubes y centros comunitarios. Más que un pasatiempo, es un símbolo de convivencia intergeneracional y estrategia mental. La Federación Dominicana de Dominó organiza campeonatos nacionales, mientras que las asociaciones locales celebran torneos municipales y barriales, especialmente durante festividades.',
    requirements: [
      'Juego completo de 28 fichas (doble seis)',
      'Mesa o superficie plana',
      '4 jugadores (dos parejas enfrentadas)',
      'Lápiz y papel (opcional, para llevar puntuación) o Celular (para usar aplicaciones de dominó)',
    ],
    places: [
      {
        name: 'Parque Colón',
        location: 'Zona Colonial, Santo Domingo',
        cost: 'Gratis (mesas públicas al aire libre)'
      },
      {
        name: 'Club de Dominó Santiago',
        location: 'Santiago de los Caballeros',
        cost: 'RD$500 anuales (membresía de acceso y torneos)'
      },
      {
        name: 'Club Naco',
        location: 'Santo Domingo',
        cost: 'Acceso con membresía del club (eventos internos y ligas sociales)'
      },
      {
        name: 'Parque Duarte',
        location: 'San Pedro de Macorís',
        cost: 'Gratis (mesas públicas, ambiente comunitario)'
      }
    ]
  },

  /* --- 3. FÚTBOL --- */
  {
    id: 3,
    name: 'Fútbol',
    region: 'Todo el país',
    type: 'Equipo',
    popularity: 'Muy popular',
    image: 'imagenes/DirectorioDeDeportes/Futbol.jpg',
    shortDescription: 'Deporte en expansión y motor de desarrollo juvenil',
    description:
      'El fútbol ha crecido aceleradamente en popularidad, especialmente entre la juventud dominicana. Se practica en escuelas, clubes, ligas barriales y academias privadas. La creación de la Liga Dominicana de Fútbol (LDF) en 2015 marcó el inicio de su profesionalización. Desde entonces, el país ha incrementado su presencia en torneos internacionales, como la clasificación de la selección Sub-20 al Mundial de 2023 y a los Juegos Olímpicos de París 2024. Este crecimiento ha impulsado tanto la infraestructura como la formación de nuevos talentos a nivel nacional.',
    requirements: [
      'Balón de fútbol (talla 4 o 5, según la edad)',
      'Uniforme completo (camiseta, short y medias)',
      'Calzado con tacos o zapatillas adecuadas según la superficie',
      'Espinilleras (recomendadas)',
      'Conos, redes y vallas (para entrenamientos organizados)'
    ],
    places: [
      {
        name: 'Canchas comunitarias y escolares',
        location: 'Barrios y sectores de todo el país',
        cost: 'Gratis (uso libre y torneos comunitarios)'
      },
      {
        name: 'Club Deportivo y Cultural Mauricio Báez',
        location: 'Villa Juana, Santo Domingo',
        cost: 'Inscripciones desde RD$1 500 (cursos infantiles y juveniles)'
      },
      {
        name: 'Polideportivo de Invivienda',
        location: 'Santo Domingo Este',
        cost: 'Acceso gratuito para uso comunitario'
      },
      {
        name: 'Soccer Town SD',
        location: 'Santo Domingo',
        cost: 'RD$2 500 mensuales (programa formativo)'
      },
      {
        name: 'Barcelona Academy RD',
        location: 'Santo Domingo',
        cost: 'RD$3 500 mensuales (escuela privada de fútbol)'
      },
      {
        name: 'FCBEscola República Dominicana',
        location: 'Santiago',
        cost: 'RD$5 000 mensuales (modelo de formación FC Barcelona)'
      }
    ]
  },

  /* --- 26. WINDSURF / KITESURF --- */
  {
    id: 26,
    name: 'Windsurf y Kitesurf',
    region: 'Cabarete, Puerto Plata',
    type: 'Individual',
    popularity: 'Popular entre locales y turistas',
    image: 'imagenes/DirectorioDeDeportes/WindsurfyKitesurf.jpg',
    shortDescription: 'Capital caribeña de los deportes de viento',
    description:
      'Cabarete, en la costa norte dominicana, es reconocido internacionalmente como un destino de clase mundial para windsurf y kitesurf. Sus condiciones únicas —vientos alisios constantes, arrecifes que moderan el oleaje y clima cálido todo el año— hacen que sea ideal tanto para principiantes como para profesionales. Escuelas certificadas por IKO ofrecen clases y alquiler de equipos con altos estándares de seguridad.',
    requirements: [
      'Tabla de windsurf o kitesurf con vela o cometa',
      'Arnés de seguridad',
      'Chaleco salvavidas o impacto',
      'Traje de neopreno (recomendado en invierno)'
    ],
    places: [
      {
        name: 'Kite Beach – Champion Kite School',
        location: 'Cabarete, Puerto Plata',
        cost: 'Clases desde RD$3 200 (sesión de 2 horas)',
        website: 'https://championkite.com'
      },
      {
        name: 'Vela Cabarete',
        location: 'Playa Cabarete, Puerto Plata',
        cost: 'Alquiler de equipo completo desde RD$4 000 por día',
        website: 'https://velacabarete.com'
      }
    ]
  },

  /* --- 4. SÓFTBOL --- */
  {
    id: 4,
    name: 'Sóftbol',
    region: 'Santo Domingo, Santiago y otras provincias',
    type: 'Equipo',
    popularity: 'Popular',
    image: 'imagenes/DirectorioDeDeportes/Sofbol.jpg',
    shortDescription: 'Versión accesible y recreativa del béisbol',
    description:
      'El sóftbol es una variante del béisbol que se caracteriza por el uso de una pelota más grande y lanzamientos por debajo del hombro. Es ampliamente practicado en ligas empresariales, universitarias y municipales. La Federación Dominicana de Sóftbol organiza torneos nacionales y promueve el desarrollo juvenil y femenino. Su ambiente competitivo pero recreativo lo hace ideal para todas las edades.',
    requirements: [
      'Guante de sóftbol',
      'Bate de sóftbol',
      'Pelota oficial de sóftbol',
      'Zapatos con tacos o suela adecuada'
    ],
    places: [
      {
        name: 'Campo de Sóftbol del Centro Olímpico Juan Pablo Duarte',
        location: 'Santo Domingo',
        cost: 'Entrenamiento gratuito (ligas federadas desde RD$1 500 por temporada)'
      },
      {
        name: 'Play Los Mameyes',
        location: 'Santo Domingo Este',
        cost: 'Gratis (uso comunitario, ligas barriales)'
      },
      {
        name: 'Play de Sóftbol de Gurabo',
        location: 'Santiago',
        cost: 'Gratis o bajo costo según liga local'
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
    shortDescription: 'Pesca en alta mar de especies como marlín, dorado y atún',
    description:
      'La pesca deportiva en República Dominicana se concentra en sus costas orientales y del norte, siendo un destino destacado en el Caribe. Se realizan torneos de prestigio como el Torneo Internacional de Marlín Blanco en La Romana. Las especies más buscadas incluyen el marlín azul, el pez vela y el atún. La temporada alta va de junio a septiembre, y es común practicarla mediante excursiones organizadas o embarcaciones privadas.',
    requirements: [
      'Caña y carrete de pesca de agua salada (saltwater)',
      'Arnés y cinturón de pelea para pesca de gran tamaño',
      'Licencia de pesca (requerida en torneos oficiales)',
      'Chaleco salvavidas (obligatorio en embarcaciones)'
    ],
    places: [
      {
        name: 'Marina Casa de Campo',
        location: 'La Romana',
        cost: 'Charters privados desde RD$15 000 por jornada',
        website: 'https://www.casadecampo.com.do'
      },
      {
        name: 'Excursiones Samaná Fishing',
        location: 'Samaná',
        cost: 'Excursión compartida desde RD$12 000 por persona'
      },
      {
        name: 'Ocean Adventures Punta Cana',
        location: 'Punta Cana',
        cost: 'Salidas de medio día desde RD$10 000'
      }
    ]
  },

  /* --- 6. BALONCESTO --- */
  {
    id: 6,
    name: 'Baloncesto',
    region: 'Santo Domingo, Santiago, San Pedro de Macorís',
    type: 'Equipo',
    popularity: 'Muy popular',
    image: 'imagenes/DirectorioDeDeportes/Baloncesto.jpg',
    shortDescription: 'Canastas en cada barrio dominicano',
    description:
      'El baloncesto es uno de los deportes más practicados en la República Dominicana, especialmente entre jóvenes de zonas urbanas. Cuenta con fuerte presencia en ligas escolares, universitarias y municipales. La Liga Nacional de Baloncesto (LNB), fundada en 2005, es la principal competición profesional del país. La selección nacional ha obtenido importantes logros en Centrobasket y Juegos Centroamericanos. La influencia de la NBA se refleja en la afición y el estilo de juego local.',
    requirements: [
      'Balón oficial de baloncesto (talla 7 para varones, talla 6 para mujeres)',
      'Zapatillas deportivas de suela media o alta',
      'Ropa deportiva cómoda (camiseta y pantalón corto)',
      'Cancha con tableros y aros reglamentarios'
    ],
    places: [
      {
        name: 'Palacio de los Deportes Virgilio Travieso Soto',
        location: 'Centro Olímpico Juan Pablo Duarte, Santo Domingo',
        cost: 'Boletas desde RD$200 (eventos oficiales)'
      },
      {
        name: 'Canchas abiertas del Parque Mirador Sur',
        location: 'Santo Domingo',
        cost: 'Acceso gratuito (uso libre diario)'
      },
      {
        name: 'Club San Carlos',
        location: 'Distrito Nacional',
        cost: 'Programas juveniles desde RD$1 000 mensuales'
      },
      {
        name: 'Multiuso de Los Pepines',
        location: 'Santiago',
        cost: 'Entrada libre para entrenamientos comunitarios'
      }
    ]
  },


  /* --- 7. VOLEIBOL --- */
  {
    id: 7,
    name: 'Voleibol',
    region: 'Todo el país (playas y clubes)',
    type: 'Equipo',
    popularity: 'Muy popular',
    image: 'imagenes/DirectorioDeDeportes/Volleyball.jpg',
    shortDescription: 'Tradición olímpica y éxito internacional',
    description:
      'El voleibol es uno de los deportes colectivos más exitosos de la República Dominicana, especialmente en su modalidad femenina. La selección nacional ha ganado múltiples medallas de oro en los Juegos Panamericanos (2003, 2019, 2023) y es considerada una potencia en NORCECA. Se practica ampliamente tanto en canchas de sala como en playas caribeñas. El voleibol de playa es muy popular en torneos veraniegos y en el circuito juvenil. El país cuenta con una sólida infraestructura de clubes, ligas y entrenadores.',
    requirements: [
      'Balón de voleibol (interior o playa)',
      'Red de vóley (ajustada según tipo de superficie)',
      'Zapatillas deportivas (o pies descalzos en la arena)',
      'Ropa deportiva cómoda'
    ],
    places: [
      {
        name: 'Playa Boca Chica (cancha pública de vóley)',
        location: 'Boca Chica, Santo Domingo Este',
        cost: 'Gratis (torneos veraniegos y uso libre)'
      },
      {
        name: 'Pabellón de Voleibol – Centro Olímpico',
        location: 'Santo Domingo',
        cost: 'RD$500 por sesión de entrenamiento'
      },
      {
        name: 'Club Deportivo Naco',
        location: 'Santo Domingo',
        cost: 'Membresía o pago por clases (~RD$2 000 mensuales)'
      },
      {
        name: 'Playa Juan Dolio',
        location: 'San Pedro de Macorís',
        cost: 'Acceso libre (torneos organizados en verano)'
      }
    ]
  },


  /* --- 8. ATLETISMO --- */
  {
    id: 8,
    name: 'Atletismo',
    region: 'Santo Domingo y otras ciudades',
    type: 'Individual',
    popularity: 'Media',
    image: 'imagenes/DirectorioDeDeportes/Atletismo.jpg',
    shortDescription: 'Cuna de medallistas olímpicos dominicanos',
    description:
      'El atletismo en la República Dominicana cuenta con un legado olímpico, encabezado por Félix Sánchez, doble medallista de oro en 400 m vallas. El país dispone de pistas profesionales abiertas al público y circuitos urbanos para corredores de fondo y velocidad. Es una disciplina clave para el desarrollo físico juvenil, con programas de iniciación y clubes escolares. La práctica recreativa es común en parques y avenidas habilitadas, especialmente en Santo Domingo.',
    requirements: [
      'Zapatillas de correr o spike según especialidad',
      'Ropa deportiva ligera',
      'Cronómetro o reloj deportivo (opcional)',
      'Botella de agua e hidratación'
    ],
    places: [
      {
        name: 'Pista Félix Sánchez – Centro Olímpico Juan Pablo Duarte',
        location: 'Santo Domingo',
        cost: 'Acceso libre en horarios no federados'
      },
      {
        name: 'Avenida de la Salud (Mirador Sur)',
        location: 'Santo Domingo',
        cost: 'Gratis (circuito urbano de 5 km, bien iluminado)'
      },
      {
        name: 'Pista de Atletismo del Polideportivo',
        location: 'La Vega',
        cost: 'Acceso libre (entrenamientos comunitarios)'
      },
      {
        name: 'Parque Central de Santiago',
        location: 'Santiago de los Caballeros',
        cost: 'Gratis (espacios señalizados para correr y caminar)'
      }
    ]
  },


  /* --- 9. BOXEO --- */
  {
    id: 9,
    name: 'Boxeo',
    region: 'Santo Domingo, San Cristóbal y otras provincias',
    type: 'Individual',
    popularity: 'Alta',
    image: 'imagenes/DirectorioDeDeportes/Boxeo.jpg',
    shortDescription: 'Cuna de campeones mundiales y olímpicos',
    description:
      'El boxeo dominicano tiene una rica tradición que ha producido figuras como Carlos "Teo" Cruz, Joan Guzmán y el medallista olímpico Félix Díaz. Es uno de los deportes con mayor impacto social, especialmente en sectores populares. Gimnasios públicos y federativos ofrecen entrenamiento gratuito a niños, jóvenes y adultos, fomentando la disciplina, la autodefensa y la proyección atlética. Las competencias incluyen torneos nacionales, municipales y eventos internacionales con representación oficial.',
    requirements: [
      'Guantes de boxeo homologados',
      'Vendas para manos',
      'Protector bucal',
      'Ropa deportiva (short y camiseta sin mangas)',
      'Casco y careta (para entrenamientos amateur)'
    ],
    places: [
      {
        name: 'Gimnasio Nacional de Boxeo – Centro Olímpico',
        location: 'Santo Domingo',
        cost: 'Gratis (programas de MIDEREC y Federación Dominicana de Boxeo)'
      },
      {
        name: 'Gimnasio Municipal de Boxeo',
        location: 'San Cristóbal',
        cost: 'Entrenamiento gratuito para jóvenes comunitarios'
      },
      {
        name: 'Club Mauricio Báez',
        location: 'Villa Juana, Santo Domingo',
        cost: 'Clases federadas a bajo costo (desde RD$500/mes)'
      }
    ]
  },

  /* --- 10. GOLF --- */
  {
    id: 10,
    name: 'Golf',
    region: 'Punta Cana, La Romana y zonas turísticas',
    type: 'Individual',
    popularity: 'Media‑Alta',
    image: 'imagenes/DirectorioDeDeportes/Golf.jpg',
    shortDescription: 'Campos de clase mundial frente al mar Caribe',
    description:
      'La República Dominicana es considerada el principal destino de golf en el Caribe. Cuenta con más de 25 campos de nivel internacional, muchos de ellos diseñados por leyendas como Jack Nicklaus, Pete Dye y Tom Fazio. Punta Espada (Cap Cana) y Teeth of the Dog (Casa de Campo) figuran entre los mejores del hemisferio occidental según rankings especializados. Estos campos combinan desafíos técnicos con vistas espectaculares al mar, atrayendo tanto a golfistas profesionales como a turistas amateurs.',
    requirements: [
      'Juego completo de palos de golf',
      'Bolas oficiales de golf',
      'Zapatos con suela soft spikes',
      'Guante (opcional pero recomendado)',
      'Vestimenta formal de golf (polo, pantalón largo o bermuda)'
    ],
    places: [
      {
        name: 'Punta Espada Golf Club',
        location: 'Cap Cana, Punta Cana',
        cost: 'Green fee desde RD$8 000',
        website: 'https://puntaspadagolfclub.com'
      },
      {
        name: 'Teeth of the Dog – Casa de Campo',
        location: 'La Romana',
        cost: 'Green fee desde RD$6 500',
        website: 'https://www.casadecampo.com.do/golf/teeth-of-the-dog/'
      },
      {
        name: 'Playa Dorada Golf Course',
        location: 'Puerto Plata',
        cost: 'Desde RD$4 000 (green fee diario)',
        website: 'https://playadoradagolf.com'
      }
    ]
  },

  /* --- 11. SURF --- */
  {
    id: 11,
    name: 'Surf',
    region: 'Costa norte (Puerto Plata, Cabarete, Macao)',
    type: 'Individual',
    popularity: 'Creciente',
    image: 'imagenes/DirectorioDeDeportes/Surf.jpg',
    shortDescription: 'Olas todo el año en la costa norte dominicana',
    description:
      'El surf en la República Dominicana ha ganado notoriedad internacional gracias a las condiciones ideales de la costa norte. Playa Encuentro, cerca de Cabarete, es considerada la cuna del surf dominicano por sus rompientes consistentes aptas para todos los niveles. Además, playas como Macao (Punta Cana) y El Barco (Puerto Plata) ofrecen escenarios ideales para la práctica recreativa y competitiva. Escuelas certificadas imparten clases con instructores profesionales y alquilan equipos.',
    requirements: [
      'Tabla de surf adecuada al nivel (shortboard o longboard)',
      'Leash (soga de seguridad)',
      'Rashguard o traje de neopreno (según temporada)',
      'Cera para tabla (wax)',
      'Bloqueador solar resistente al agua'
    ],
    places: [
      {
        name: 'Cabarete Surf Company',
        location: 'Playa Encuentro, Puerto Plata',
        cost: 'Clases desde RD$2 800 por 2 horas',
        website: 'https://cabaretesurf.com'
      },
      {
        name: 'Macao Surf Camp',
        location: 'Playa Macao, Punta Cana',
        cost: 'Clase individual desde RD$3 000',
        website: 'https://macaosurfcamp.com'
      },
      {
        name: 'Dominican Surf School',
        location: 'Playa El Barco, Puerto Plata',
        cost: 'Clases desde RD$2 500',
        website: 'https://dominicansurfschool.com'
      }
    ]
  },

  /* --- 12. TENIS --- */
  {
    id: 12,
    name: 'Tenis',
    region: 'Santo Domingo, Santiago, La Romana',
    type: 'Individual/Parejas',
    popularity: 'Media',
    image: 'imagenes/DirectorioDeDeportes/Tenis.jpg',
    shortDescription: 'Canchas profesionales y academias en crecimiento',
    description:
      'El tenis en República Dominicana se practica principalmente en clubes privados y centros deportivos de alto nivel. La Federación Dominicana de Tenis organiza torneos federados (Futures ITF, Challenger ATP y torneos juveniles nacionales). Clubes como “La Bocha” y el Centro Nacional de Tenis impulsan la formación de atletas desde edades tempranas. La infraestructura incluye canchas de arcilla y superficie dura, con iluminación y entrenadores certificados.',
    requirements: [
      'Raqueta de tenis (acorde al nivel)',
      'Pelotas oficiales',
      'Zapatillas específicas para tenis (arcilla o hard court)',
      'Toalla y botella de hidratación'
    ],
    places: [
      {
        name: 'Santo Domingo Tennis Club “La Bocha”',
        location: 'Santo Domingo',
        cost: 'RD$300–1 000/h (alquiler para invitados)',
        website: 'http://labocha.org'
      },
      {
        name: 'Centro Nacional de Tenis (FEDOTENIS)',
        location: 'Parque del Este, Santo Domingo Este',
        cost: 'Clases y acceso desde RD$500/sesión',
        website: 'https://fedotenis.com.do'
      },
      {
        name: 'Club de Tenis Caribe',
        location: 'Santiago de los Caballeros',
        cost: 'RD$400–800 por hora (alquiler de cancha)',
        website: 'https://clubteniscaribe.com'
      }
    ]
  },


  /* --- 13. CICLISMO --- */
  {
    id: 13,
    name: 'Ciclismo',
    region: 'Jarabacoa, Constanza, Santo Domingo, Santiago',
    type: 'Individual/Grupo',
    popularity: 'Media',
    image: 'imagenes/DirectorioDeDeportes/Ciclismo.jpg',
    shortDescription: 'Montaña y carretera en paisajes caribeños',
    description:
      'El ciclismo dominicano combina rutas de alta montaña y tramos urbanos. La Cordillera Central ofrece desafíos como la ruta Jarabacoa–Constanza, considerada una de las más exigentes y bellas del Caribe. La Vuelta Ciclística Independencia (febrero) es la principal competencia profesional del país. Cada vez más ciclistas recreativos y clubes organizan salidas los fines de semana en todo el territorio.',
    requirements: [
      'Bicicleta de montaña o ruta en buen estado',
      'Casco obligatorio',
      'Kit de reparación básico (cámara, inflador, parches)',
      'Botella de hidratación y gafas solares'
    ],
    places: [
      {
        name: 'Ruta Jarabacoa – Constanza (La Vega – El Río)',
        location: 'Cordillera Central',
        cost: 'Gratis (ruta escénica de ~60 km con puertos altos)'
      },
      {
        name: 'Ciclovía Mirador Sur',
        location: 'Santo Domingo',
        cost: 'Gratis (uso libre domingos y festivos)'
      },
      {
        name: 'Valle Nuevo – La Pirámide',
        location: 'Constanza',
        cost: 'Gratis (ruta de montaña; ideal MTB o gravel)'
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
    shortDescription: 'Disciplina olímpica de respeto y control',
    description:
      'El judo dominicano se desarrolla bajo normas de la Federación Internacional de Judo. Se practica desde edades tempranas en dojos certificados, combinando técnica, táctica y valores como la cortesía, autocontrol y esfuerzo. La selección nacional ha representado a RD en eventos como los Juegos Panamericanos y Centroamericanos, con proyección internacional.',
    requirements: [
      'Judogi (traje oficial de judo)',
      'Cinturón correspondiente al grado',
      'Protección bucal (opcional)',
      'Botella de agua y toalla personal'
    ],
    places: [
      {
        name: 'Dojo Nacional de Judo – Centro Olímpico',
        location: 'Santo Domingo',
        cost: 'RD$1 500/mes (membresía federativa)',
        website: 'https://fedorjudo.org.do'
      },
      {
        name: 'Dojo Regional de Santiago – Complejo Deportivo La Barranquita',
        location: 'Santiago de los Caballeros',
        cost: 'Entrenamientos desde RD$1 200/mes'
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
    shortDescription: 'Montura, salto y elegancia ecuestre',
    description:
      'La equitación combina destreza técnica y conexión con el caballo. En República Dominicana se practica salto ecuestre, doma clásica y equitación recreativa. Los clubes hípicos ofrecen clases para niños y adultos, además de competencias locales los fines de semana. Es un deporte que fomenta disciplina, control corporal y cuidado animal.',
    requirements: [
      'Casco homologado de equitación',
      'Botas y pantalones de montar',
      'Chaleco protector (recomendado)',
      'Licencia federativa (para competencia)'
    ],
    places: [
      {
        name: 'Club Hípico Santo Domingo',
        location: 'Santo Domingo',
        cost: 'Clase individual desde RD$2 000',
        website: 'https://clubhipico.com.do'
      },
      {
        name: 'Club Ecuestre de Bonao',
        location: 'Bonao',
        cost: 'Clases y cabalgatas guiadas desde RD$1 800'
      }
    ]
  },

  /* --- 16. PALO ENSEBADO --- */
  // {
  //   id: 16,
  //   name: 'Palo Ensebado',
  //   region: 'San Juan de la Maguana',
  //   type: 'Individual',
  //   popularity: 'Tradicional',
  //   image: 'imagenes/DirectorioDeDeportes/PaloEnsebado.jpg',
  //   shortDescription: 'Juego patrimonial en fiestas patronales',
  //   description:
  //     'El palo ensebado es un juego típico dominicano que consiste en escalar un poste vertical cubierto de grasa para alcanzar un premio colgado en la cima. Se celebra principalmente en festividades patronales, especialmente durante las fiestas de San Juan Bautista (24 de junio), con fuerte arraigo en la provincia de San Juan. Es una actividad que mezcla destreza, humor y tradición, promoviendo la cultura popular.',
  //   requirements: [
  //     'Poste de madera de 5–7 metros',
  //     'Grasa o sebo en toda la superficie',
  //     'Premio colgado en la cima (dinero, productos, etc.)',
  //     'Ropa deportiva resistente (recomendada)'
  //   ],
  //   places: [
  //     {
  //       name: 'Fiestas Patronales de San Juan',
  //       location: 'San Juan de la Maguana',
  //       cost: 'Acceso gratuito (evento comunitario anual)'
  //     }
  //   ]
  // },

  /* --- 17. KARATE --- */
  {
    id: 17,
    name: 'Karate',
    region: 'Todo el país',
    type: 'Individual',
    popularity: 'Media‑Alta',
    image: 'imagenes/DirectorioDeDeportes/Karate.jpg',
    shortDescription: 'Disciplina marcial de formación integral',
    description:
      'El karate es una de las artes marciales más practicadas en el país, con cientos de dojos afiliados a la Federación Dominicana de Karate. Forma parte del programa deportivo escolar y universitario, y ha aportado medallistas internacionales en categorías juveniles y adultas. La práctica combina defensa personal, kata (formas) y kumite (combate).',
    requirements: [
      'Karategi (uniforme reglamentario)',
      'Cinturón (según grado)',
      'Protecciones (guantes, bucal, espinilleras)',
      'Licencia federativa (en competencias)'
    ],
    places: [
      {
        name: 'Dojo Central – Federación Dominicana de Karate',
        location: 'Centro Olímpico, Santo Domingo',
        cost: 'Desde RD$1 200/mes',
        website: 'https://fedokarate.org'
      },
      {
        name: 'Dojos privados y comunitarios',
        location: 'Santiago, San Cristóbal, La Vega, San Juan…',
        cost: 'RD$1 000–2 000 mensuales (según dojo)'
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
    shortDescription: 'Arrecifes, naufragios y vida marina del Caribe',
    description:
      'El buceo en República Dominicana ofrece inmersiones en arrecifes coralinos, cuevas submarinas y pecios históricos. Bayahibe es la principal base de exploración, con acceso al Parque Nacional Cotubanamá y naufragios como el “Saint George”. También destacan las aguas de Sosúa y Punta Cana. Centros certificados PADI imparten formación desde principiantes (Discover Scuba) hasta niveles profesionales.',
    requirements: [
      'Certificación PADI (obligatoria para inmersiones >12 m)',
      'Equipo completo de buceo (tanque, regulador, máscara, aletas)',
      'Opción de alquiler en centros locales',
      'Buen estado físico y saber nadar'
    ],
    places: [
      {
        name: 'Coral Point Diving',
        location: 'Bayahibe, La Romana',
        cost: 'Inmersión guiada desde RD$4 500',
        website: 'https://coralpointdiving.com'
      },
      {
        name: 'Ocean Adventures',
        location: 'Punta Cana',
        cost: 'Tour de buceo en arrecifes y pecios desde RD$4 000',
        website: 'https://oceanadventures-puntacana.com'
      },
      {
        name: 'Sosúa Diving Center',
        location: 'Sosúa, Puerto Plata',
        cost: 'Buceo en arrecifes desde RD$3 000',
        website: 'https://sosuadivingcenter.com'
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
    shortDescription: 'Vuelos panorámicos sobre montañas y valles',
    description:
      'El parapente es una experiencia de vuelo libre que gana popularidad en las zonas montañosas de RD. Jarabacoa y Constanza ofrecen condiciones óptimas durante todo el año gracias a sus vientos térmicos suaves. Escuelas certificadas organizan vuelos tándem con pilotos expertos, ideales tanto para principiantes como para aventureros.',
    requirements: [
      'Arnés y vela certificada para parapente',
      'Casco protector',
      'Paracaídas de emergencia (incluido en vuelos tándem)',
      'Ropa deportiva cómoda y abrigada'
    ],
    places: [
      {
        name: 'EcoParapente RD',
        location: 'Jarabacoa, La Vega',
        cost: 'Vuelo tándem RD$6 000 por persona',
        website: 'https://ecoparapenterd.com'
      },
      {
        name: 'Parapente RD',
        location: 'Constanza, La Vega',
        cost: 'Vuelo panorámico desde RD$6 000',
        website: 'https://parapenterd.com'
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
    shortDescription: 'Aventura acuática en rápidos caribeños',
    description:
      'El rafting en el río Yaque del Norte, el más extenso del Caribe, combina adrenalina y paisajes montañosos. Con rápidos clase II a IV, es ideal tanto para principiantes como para amantes de la aventura. Las excursiones incluyen instrucción técnica y acompañamiento de guías certificados, con salida desde La Confluencia y descenso hasta Jima Abajo.',
    requirements: [
      'Chaleco salvavidas aprobado',
      'Casco de protección',
      'Remo personal',
      'Ropa impermeable o de secado rápido',
      'Saber nadar (recomendado)'
    ],
    places: [
      {
        name: 'Rancho Baiguate',
        location: 'Jarabacoa',
        cost: 'Paquete completo RD$3 500 (incluye equipo y transporte)',
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
    shortDescription: 'Tradición ecuestre y adrenalina en la pista',
    description:
      'Las carreras de caballos forman parte del patrimonio deportivo dominicano. El Hipódromo V Centenario es el recinto oficial, con eventos todos los fines de semana y apuestas reguladas. En zonas rurales también se celebran carreras informales y tradicionales, como las “cintas”, durante fiestas patronales.',
    requirements: [
      'Caballo registrado en asociación ecuestre',
      'Jinete profesional o aprendiz licenciado',
      'Inscripción previa en la carrera',
      'Equipo de monta homologado'
    ],
    places: [
      {
        name: 'Hipódromo V Centenario',
        location: 'Santo Domingo',
        cost: 'Entrada general RD$200 (eventos sabatinos)',
        website: 'https://hipodromord.com'
      },
      {
        name: 'Hipódromo Real',
        location: 'Santiago (eventos ocasionales)',
        cost: 'Variable (según competencia y acceso)'
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
    shortDescription: 'Disciplina de precisión bajo normativa internacional',
    description:
      'El tiro deportivo en República Dominicana se practica en clubes federados bajo las normas de la ISSF. Incluye modalidades con pistola, rifle y escopeta en distancias de 10, 25 y 50 metros. Es una disciplina que exige control, seguridad y concentración, con campeonatos nacionales organizados por la Federación Dominicana de Tiro.',
    requirements: [
      'Arma deportiva registrada (pistola, rifle o escopeta según modalidad)',
      'Protección auditiva y ocular',
      'Licencia de tirador federado',
      'Conocimiento y cumplimiento de normas de seguridad'
    ],
    places: [
      {
        name: 'Club de Tiro Santo Domingo',
        location: 'Santo Domingo Este',
        cost: 'RD$1 500 por sesión de práctica',
        website: 'https://clubtiro.com.do'
      },
      {
        name: 'Academia de Tiro Santiago',
        location: 'Santiago de los Caballeros',
        cost: 'RD$1 000–1 800 (según modalidad y nivel)'
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
    shortDescription: 'Bienestar físico y mental frente al mar',
    description:
      'La práctica de yoga al aire libre ha ganado popularidad en destinos turísticos costeros. Las clases en playa combinan posturas de hatha o vinyasa con técnicas de respiración consciente y meditación, especialmente al amanecer o al atardecer. Es ideal para reducir el estrés y conectar con la naturaleza en un entorno caribeño.',
    requirements: [
      'Mat antideslizante (colchoneta)',
      'Ropa cómoda y transpirable',
      'Bloques o correas (opcional para nivel principiante)',
      'Hidratación adecuada'
    ],
    places: [
      {
        name: 'Yoga Punta Cana',
        location: 'Playa Bávaro, Punta Cana',
        cost: 'Clase grupal RD$800',
        website: 'https://yogapuntacana.com'
      },
      {
        name: 'Bahía Yoga',
        location: 'Playa Bonita, Las Terrenas',
        cost: 'Sesiones al atardecer RD$700–1 000 (según duración)'
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
    shortDescription: 'Raqueta, estrategia y dinamismo en cancha cerrada',
    description:
      'El pádel es un deporte de raqueta que se juega en parejas dentro de una cancha cerrada con paredes de cristal. Su popularidad ha crecido rápidamente en República Dominicana desde 2020, impulsada por la construcción de canchas en clubes sociales y gimnasios. La Federación Dominicana de Pádel organiza torneos oficiales y circuitos nacionales, atrayendo tanto a jugadores recreativos como competitivos.',
    requirements: [
      'Pala de pádel (sin cuerdas)',
      'Pelotas presurizadas específicas',
      'Zapatillas de pádel o tenis con suela apropiada',
      'Ropa deportiva ligera'
    ],
    places: [
      {
        name: 'Padel Center SD',
        location: 'Santo Domingo',
        cost: 'Alquiler de cancha RD$1 200/hora',
        website: 'https://padelcenterrd.com'
      },
      {
        name: 'Centro Deportivo Punta Cana',
        location: 'Punta Cana',
        cost: 'Canchas dobles disponibles (desde RD$1 000/hora)'
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
    shortDescription: 'Disciplina sin árbitros basada en el juego limpio',
    description:
      'El Ultimate Frisbee es un deporte colectivo sin contacto físico y sin árbitros, que promueve el “espíritu de juego”. En República Dominicana, se practica en entornos universitarios y comunitarios desde 2005. Ligas como la Santo Domingo Ultimate League (SDUL) y clubes en INTEC y PUCMM organizan torneos y encuentros regulares. Es una disciplina que combina velocidad, resistencia, trabajo en equipo y estrategia.',
    requirements: [
      'Disco oficial de 175 g (reglamentario)',
      'Conos para delimitar el campo de juego',
      'Zapatillas deportivas adecuadas para césped o tierra'
    ],
    places: [
      {
        name: 'Parque Mirador Sur – campo central',
        location: 'Santo Domingo',
        cost: 'Gratis (uso abierto)'
      },
      {
        name: 'Campus INTEC y PUCMM',
        location: 'Santo Domingo y Santiago',
        cost: 'Acceso universitario o con permiso previo'
      }
    ]
  },

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