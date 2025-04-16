<template>
    <div class="sports-app">

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

        <a href="#" class="Carrito">
          <img src="/imagenes/Carrito-Icon.png" alt="Carrito" class="carrito-icon"/>
        </a>

        <a href= "/Ajustes" class="Ajustes">
          <img src="/imagenes/Ajustes-Icon.png" alt="Ajustes" class="ajustes-icon"/>
        </a>

        <a href= "/Perfil" class="Perfil">
          <img src="/imagenes/Perfil-Icon.png" alt="Perfil" class="perfil-icon"/>
        </a>

        <a :href=" login ? '/Login' : '/Logout' " class="Logout">
          <img src="/imagenes/Logout-Icon.png" alt="Logout" class="logout-icon"/>
        </a>

      </div>
    </nav>


      <!-- Header con imagen representativa -->
      <header class="app-header">
        <img src="/imagenes/republicadominicana.png" alt="Deportes Dominicana" class="header-image">
        <div class="header-content">
          <h1>Deportes en República Dominicana</h1>
          <p>Descubre los deportes autóctonos y populares de nuestro país</p>
        </div>
      </header>
  
      <!-- Barra de búsqueda y filtros -->
      <div class="controls">
        <div class="search-container">
          <input 
            type="text" 
            v-model="searchTerm" 
            placeholder="Buscar deporte..." 
            class="search-input"
          >
          <button class="search-button">
            <i class="fas fa-search"></i>
          </button>
        </div>
  
        <div class="filter-buttons">
          <button 
            v-for="region in regions" 
            :key="region" 
            @click="filterByRegion(region)"
            :class="{ active: activeRegion === region }"
          >
            {{ region }}
          </button>
        </div>
      </div>
  
      <!-- Contenido principal -->
      <main class="main-content">
        <!-- Listado de deportes -->
        <div class="sports-list" v-if="!selectedSport">
          <div 
            v-for="sport in filteredSports" 
            :key="sport.id" 
            class="sport-card"
            @click="selectSport(sport)"
          >
            <div class="sport-image">
              <img :src="sport.image" :alt="sport.name">
            </div>
            <div class="sport-info">
              <h3>{{ sport.name }}</h3>
              <div class="sport-meta">
                <span class="region-tag">{{ sport.region }}</span>
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
            <img :src="selectedSport.image" :alt="selectedSport.name" class="detail-image">
            <h2>{{ selectedSport.name }}</h2>
            <div class="detail-meta">
              <span><i class="fas fa-map-marker-alt"></i> {{ selectedSport.region }}</span>
              <span><i class="fas fa-users"></i> {{ selectedSport.type }}</span>
              <span><i class="fas fa-star"></i> {{ selectedSport.popularity }}</span>
            </div>
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
                  <a v-if="place.website" :href="place.website" target="_blank">
                    <i class="fas fa-globe"></i> Sitio web
                  </a>
                </div>
              </div>
            </div>
  
            <div class="detail-section" v-if="selectedSport.events.length > 0">
              <h3><i class="fas fa-calendar-alt"></i> Eventos y torneos</h3>
              <div class="events-container">
                <div class="event-card" v-for="event in selectedSport.events" :key="event.name">
                  <h4>{{ event.name }}</h4>
                  <p><i class="fas fa-calendar-day"></i> {{ event.date }}</p>
                  <p><i class="fas fa-location-dot"></i> {{ event.location }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </main>
  
      <footer class="app-footer">
        <p>© 2023 Directorio de Deportes de República Dominicana</p>
        <div class="social-links">
          <a href="#"><i class="fab fa-facebook"></i></a>
          <a href="#"><i class="fab fa-instagram"></i></a>
          <a href="#"><i class="fab fa-twitter"></i></a>
        </div>
      </footer>
    </div>
  </template>
  
  <script setup>
  import { ref, computed } from 'vue';
  
  const searchTerm = ref('');
  const activeRegion = ref('Todas');
  const selectedSport = ref(null);
  
  const regions = ref([
    'Todas',
    'Santo Domingo',
    'Santiago',
    'La Romana',
    'Puerto Plata',
    'Punta Cana',
    'Samaná',
    'Barahona'
  ]);
  
  const sports = ref([
    {
      id: 1,
      name: 'Béisbol',
      region: 'Todo el país',
      type: 'Equipo',
      popularity: 'Muy popular',
      image: 'https://example.com/beisbol-rd.jpg',
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
      events: [
        {
          name: 'Serie Nacional de Béisbol',
          date: 'Octubre - Enero',
          location: 'Estadios en todo el país'
        },
        {
          name: 'Torneo de Béisbol de Verano',
          date: 'Julio - Agosto',
          location: 'Santo Domingo y Santiago'
        }
      ]
    },
    {
      id: 2,
      name: 'Dominó',
      region: 'Todo el país',
      type: 'Parejas',
      popularity: 'Muy popular',
      image: 'https://example.com/domino-rd.jpg',
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
      events: [
        {
          name: 'Campeonato Nacional de Domino',
          date: 'Noviembre',
          location: 'Santo Domingo'
        }
      ]
    },
    {
      id: 3,
      name: 'Windsurf y Kitesurf',
      region: 'Cabarete, Puerto Plata',
      type: 'Individual',
      popularity: 'Popular entre turistas y locales',
      image: 'https://example.com/kitesurf-rd.jpg',
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
      events: [
        {
          name: 'Master of the Ocean',
          date: 'Febrero',
          location: 'Cabarete'
        }
      ]
    },
    {
      id: 4,
      name: 'Sofbol',
      region: 'Santo Domingo y Santiago',
      type: 'Equipo',
      popularity: 'Popular',
      image: 'https://example.com/softbol-rd.jpg',
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
      events: []
    },
    {
      id: 5,
      name: 'Pesca Deportiva',
      region: 'Samaná, Punta Cana, Barahona',
      type: 'Individual/Grupo',
      popularity: 'Media',
      image: 'https://example.com/pesca-rd.jpg',
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
      events: [
        {
          name: 'Torneo Internacional de Pesca de Pez Vela',
          date: 'Marzo',
          location: 'Samaná'
        }
      ]
    }
  ]);
  
  const filteredSports = computed(() => {
    let result = sports.value;
    
    // Filtrar por región
    if (activeRegion.value !== 'Todas') {
      result = result.filter(sport => 
        sport.region.includes(activeRegion.value)
      );
    }
    
    // Filtrar por término de búsqueda
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

   /* General styles */
body {
    font-family: Arial, sans-serif;
    margin: 0;
    padding: 0;
  }

  
/* Navbar */
.navbar {
  background: linear-gradient(to right, #000000, #a10013);
  padding: 1rem 2rem;
  display: flex;
  justify-content: space-between; /* Distribuye el espacio entre los elementos */
  align-items: center;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

/* Logo a la izquierda */
.logo-container {
  display: flex;
  align-items: center;
}

.logo {
  width: 200px; /* Tamaño del logo */
  height: 70px;
}

/* Enlaces en el centro */
.nav-links {
  display: flex;
  gap: 2rem;
  flex-grow: 1; /* Ocupa el espacio disponible */
  justify-content: center; /* Centra los enlaces */
}

.nav-link {
  color: white;
  text-decoration: none;
  font-size: 1.1rem;
  font-weight: 500;
  transition: color 0.3s ease-in-out;
  font-family: Arial, sans-serif;
}

.nav-link:hover {
  color: #fbbf24;
}

/* Imagenes del nav bar */

.Imagenes {
  display: flex;
  align-items: center;
  gap: 15px; /* Espaciado entre los iconos */
}

.Imagenes a {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 40px; /* Tamaño uniforme */
  height: 40px;
  border-radius: 50%; /* Forma circular */
  background: rgba(255, 255, 255, 0.1); /* Fondo semitransparente */
  transition: all 0.3s ease-in-out;
  position: relative;
  overflow: hidden;
}

.Imagenes a img {
  width: 24px; /* Tamaño del ícono */
  height: 24px;
  transition: transform 0.3s ease-in-out;
}

/* Efecto hover */
.Imagenes a:hover {
  background: rgba(255, 255, 255, 0.2);
  transform: scale(1.1);
  box-shadow: 0px 4px 10px rgba(255, 255, 255, 0.2);
}

.Imagenes a:hover img {
  transform: rotate(10deg) scale(1.2);
}

/* Animación sutil de entrada */
.Imagenes a::before {
  content: "";
  position: absolute;
  width: 100%;
  height: 100%;
  background: rgba(255, 255, 255, 0.05);
  transform: scale(0);
  border-radius: 50%;
  transition: transform 0.3s ease-in-out;
}

.Imagenes a:hover::before {
  transform: scale(1.3);
  opacity: 0;
}


  .sports-app {
    font-family: 'Arial', sans-serif;
    color: #333;
    margin: 0 auto;
    padding: 0 20px;
  }
  
  .app-header {
    position: relative;
    margin-bottom: 30px;
    overflow: hidden;
  }
    
  .header-image {
    width: 100%;
    height: 300px;
    object-fit: cover;
    opacity: 0.8;
  }
  
  .header-content {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    padding: 20px;
    background: linear-gradient(transparent, rgba(0,0,0,0.7));
    color: white;
  }
  
  .header-content h1 {
    margin: 0;
    font-size: 2.5rem;
  }
  
  .controls {
    display: flex;
    flex-direction: column;
    gap: 20px;
    margin-bottom: 30px;
  }
  
  .search-container {
    display: flex;
    max-width: 500px;
  }
  
  .search-input {
    flex: 1;
    padding: 12px 15px;
    border: 2px solid #ddd;
    border-radius: 4px 0 0 4px;
    font-size: 1rem;
  }
  
  .search-button {
    padding: 0 20px;
    background-color: #0056b3;
    color: white;
    border: none;
    border-radius: 0 4px 4px 0;
    cursor: pointer;
  }
  
  .filter-buttons {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
  }
  
  .filter-buttons button {
    padding: 8px 15px;
    background-color: #f0f0f0;
    border: 1px solid #ddd;
    border-radius: 20px;
    cursor: pointer;
    transition: all 0.3s;
  }
  
  .filter-buttons button.active {
    background-color: #0056b3;
    color: white;
    border-color: #0056b3;
  }
  
  .sports-list {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
    margin-bottom: 40px;
  }
  
  .sport-card {
    border: 1px solid #ddd;
    border-radius: 8px;
    overflow: hidden;
    cursor: pointer;
    transition: transform 0.3s, box-shadow 0.3s;
  }
  
  .sport-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
  }
  
  .sport-image {
    height: 180px;
    overflow: hidden;
  }
  
  .sport-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s;
  }
  
  .sport-card:hover .sport-image img {
    transform: scale(1.05);
  }
  
  .sport-info {
    padding: 15px;
  }
  
  .sport-info h3 {
    margin: 0 0 10px 0;
    color: #0056b3;
  }
  
  .sport-meta {
    display: flex;
    justify-content: space-between;
    margin-bottom: 10px;
    font-size: 0.9rem;
  }
  
  .region-tag {
    background-color: #e1f5fe;
    color: #0288d1;
    padding: 3px 8px;
    border-radius: 4px;
  }
  
  .popularity {
    color: #ff9800;
  }
  
  .short-description {
    margin: 0;
    color: #666;
    font-size: 0.95rem;
  }
  
  .sport-detail {
    margin-bottom: 40px;
  }
  
  .back-button {
    background: none;
    border: none;
    color: #0056b3;
    cursor: pointer;
    padding: 10px 0;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 5px;
  }
  
  .detail-header {
    text-align: center;
    margin-bottom: 30px;
  }
  
  .detail-image {
    width: 100%;
    max-height: 400px;
    object-fit: cover;
    border-radius: 8px;
    margin-bottom: 20px;
  }
  
  .detail-meta {
    display: flex;
    justify-content: center;
    gap: 20px;
    margin: 15px 0;
    color: #666;
  }
  
  .detail-section {
    margin-bottom: 30px;
  }
  
  .detail-section h3 {
    color: #0056b3;
    border-bottom: 2px solid #e0e0e0;
    padding-bottom: 5px;
    display: flex;
    align-items: center;
    gap: 10px;
  }
  
  .places-container, .events-container {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 20px;
    margin-top: 15px;
  }
  
  .place-card, .event-card {
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 15px;
    transition: box-shadow 0.3s;
  }
  
  .place-card:hover, .event-card:hover {
    box-shadow: 0 3px 10px rgba(0,0,0,0.1);
  }
  
  .place-card h4, .event-card h4 {
    margin-top: 0;
    color: #00796b;
  }
  
  .place-card a {
    display: inline-block;
    margin-top: 10px;
    color: #0056b3;
    text-decoration: none;
  }
  
  .place-card a:hover {
    text-decoration: underline;
  }
  
  .app-footer {
    text-align: center;
    padding: 20px;
    margin-top: 50px;
    border-top: 1px solid #e0e0e0;
    color: #666;
  }
  
  .social-links {
    margin-top: 10px;
  }
  
  .social-links a {
    color: #666;
    margin: 0 10px;
    font-size: 1.2rem;
  }
  
  @media (max-width: 768px) {
    .sports-list {
      grid-template-columns: 1fr;
    }
    
    .header-content h1 {
      font-size: 2rem;
    }
    
    .filter-buttons {
      overflow-x: auto;
      padding-bottom: 10px;
    }
    
    .places-container, .events-container {
      grid-template-columns: 1fr;
    }
  }
  
  /* Animaciones */
  .fade-enter-active, .fade-leave-active {
    transition: opacity 0.5s;
  }
  .fade-enter, .fade-leave-to {
    opacity: 0;
  }
  </style>