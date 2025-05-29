<template>
  <div class="entrenadores-page">
    <!-- Navbar -->
    <Navbar />

    <!-- Hero Section -->
    <div class="hero-section">
      <div class="hero-overlay"></div>
      <div class="hero-content">
        <h1 class="page-title">Conoce a Nuestros Expertos</h1>
        <p class="hero-subtitle">Entrenadores certificados para llevar tu rendimiento al siguiente nivel</p>
      </div>
    </div>

    <!-- Sección CTA -->
    <div class="cta-container">
      <div class="cta-card">
        <div class="cta-text">
          <h2>¿Tienes lo necesario para ser entrenador?</h2>
          <p>Únete a nuestra red de profesionales y comparte tu conocimiento</p>
        </div>
        <router-link v-if="user" to="/Solicitud" class="cta-button">
          Aplicar Ahora
          <svg class="arrow-icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M5 12H19M19 12L12 5M19 12L12 19" stroke="currentColor" stroke-width="2" stroke-linecap="round"
              stroke-linejoin="round" />
          </svg>
        </router-link>
      </div>
    </div>

    <!-- Filtros y Búsqueda -->
    <div class="controls-section">
      <div class="search-container">
        <input type="text" placeholder="Buscar entrenadores..." v-model="busqueda">
        <svg class="search-icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path
            d="M21 21L15 15M17 10C17 13.866 13.866 17 10 17C6.13401 17 3 13.866 3 10C3 6.13401 6.13401 3 10 3C13.866 3 17 6.13401 17 10Z"
            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
      </div>

      <div class="filter-tabs">
        <button v-for="deporte in deportes" :key="deporte" @click="filtrarPorDeporte(deporte)"
          :class="{ active: deporteActivo === deporte }">
          {{ deporte }}
        </button>
      </div>
    </div>

    <!-- Lista de Entrenadores -->
    <div class="entrenadores-container">
      <transition-group name="cards" tag="div" class="entrenadores-grid">
        <div v-for="entrenador in entrenadoresFiltrados" :key="entrenador.id" class="entrenador-card"
          @click="verPerfil(entrenador)">
          <div class="card-image-container">
            <img :src="entrenador.foto" :alt="`${entrenador.nombre} - ${entrenador.deporte}`">
            <div class="deporte-tag">{{ entrenador.deporte }}</div>
          </div>

          <div class="card-content">
            <div class="card-header">
              <h3>{{ entrenador.nombre }}</h3>
              <div class="rating">
                <span v-for="star in 5" :key="star" :class="{ filled: star <= entrenador.rating }">★</span>
              </div>
            </div>

            <p class="experiencia">{{ entrenador.experiencia }}</p>
            <!-- <p class="testimonio">"{{ entrenador.testimonio }}"</p> -->

            <div v-if="user" class="card-footer">
              <button class="contact-btn" @click.stop="contactarEntrenador(entrenador)">
                <svg class="message-icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path
                    d="M21 15C21 15.5304 20.7893 16.0391 20.4142 16.4142C20.0391 16.7893 19.5304 17 19 17H7L3 21V5C3 4.46957 3.21071 3.96086 3.58579 3.58579C3.96086 3.21071 4.46957 3 5 3H19C19.5304 3 20.0391 3.21071 20.4142 3.58579C20.7893 3.96086 21 4.46957 21 5V15Z"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
                Contactar
              </button>
            </div>
          </div>
        </div>
      </transition-group>
    </div>

    <!-- Modal de Perfil -->
    <transition name="modal">
      <div v-if="entrenadorSeleccionado" class="profile-modal" @click.self="cerrarPerfil">
        <div class="modal-content">
          <button class="close-modal" @click="cerrarPerfil">
            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M18 6L6 18M6 6L18 18" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                stroke-linejoin="round" />
            </svg>
          </button>

          <div class="modal-header">
            <div class="profile-image">
              <img :src="entrenadorSeleccionado.foto" :alt="entrenadorSeleccionado.nombre">
            </div>
            <div class="profile-info">
              <h2>{{ entrenadorSeleccionado.nombre }}</h2>
              <div class="deporte-badge">{{ entrenadorSeleccionado.deporte }}</div>
              <div class="modal-rating">
                <div class="stars">
                  <span v-for="star in 5" :key="star"
                    :class="{ filled: star <= entrenadorSeleccionado.rating }">★</span>
                </div>
                <span class="rating-text">{{ entrenadorSeleccionado.rating }}.0 ({{ entrenadorSeleccionado.reseñas }}
                  reseñas)</span>
              </div>
            </div>
          </div>

          <div class="modal-body">
            <div class="section">
              <h3>Biografía</h3>
              <p>{{ entrenadorSeleccionado.biografia }}</p>
            </div>

            <div class="section">
              <h3>Especialidades</h3>
              <div class="especialidades">
                <span v-for="(especialidad, index) in entrenadorSeleccionado.especialidades" :key="index">
                  {{ especialidad }}
                </span>
              </div>
            </div>

            <div class="section">
              <h3>Logros</h3>
              <ul class="logros">
                <li v-for="(logro, index) in entrenadorSeleccionado.logros" :key="index">{{ logro }}</li>
              </ul>
            </div>

            <!-- <div class="section">
              <h3>Testimonios</h3>
              <div class="testimonios">
                <div v-for="(testimonio, index) in entrenadorSeleccionado.testimonios" :key="index" class="testimonio">
                  <p>"{{ testimonio.texto }}"</p>
                  <span class="autor">- {{ testimonio.autor }}</span>
                </div>
              </div>
            </div> -->

          </div>

          <div v-if="user" class="modal-footer">
            <button class="primary-btn" @click="contactarEntrenador(entrenadorSeleccionado)">
              Contactar a {{ entrenadorSeleccionado.nombre.split(' ')[0] }}
            </button>
          </div>
        </div>
      </div>
    </transition>

    <!-- Modal de Contacto -->
    <transition name="modal">
      <div v-if="mostrarFormularioContacto" class="contact-modal" @click.self="cerrarFormularioContacto">
        <div class="modal-content">
          <button class="close-modal" @click="cerrarFormularioContacto">
            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M18 6L6 18M6 6L18 18" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                stroke-linejoin="round" />
            </svg>
          </button>

          <h2 class="modal-title">Contactar a {{ contactoEntrenador.nombre.split(' ')[0] }}</h2>

          <form @submit.prevent="enviarFormularioContacto" class="contact-form">
            <div class="form-group">
              <label for="edad">Edad</label>
              <input type="number" id="edad" v-model="formularioContacto.edad" min="10" max="100" required>
            </div>

            <div class="form-group">
              <label for="nivel">Nivel en el deporte</label>
              <select id="nivel" v-model="formularioContacto.nivel" required>
                <option value="" disabled selected>Selecciona tu nivel</option>
                <option value="Principiante">Principiante</option>
                <option value="Intermedio">Intermedio</option>
                <option value="Avanzado">Avanzado</option>
                <option value="Profesional">Profesional</option>
              </select>
            </div>

            <div class="form-group">
              <label for="objetivos">¿Qué buscas aprender o lograr?</label>
              <textarea id="objetivos" v-model="formularioContacto.objetivos" rows="4"
                placeholder="Ej: Mejorar mi técnica de tiro, prepararme para una competencia, perder peso..."
                required></textarea>
            </div>

            <button type="submit" class="submit-btn">Enviar Solicitud</button>
          </form>
        </div>
      </div>
    </transition>


    <!-- Burbuja de Mensajes Flotante -->
    <div v-if="user" class="message-bubble" :class="{ 'expanded': mostrarMensajes }" @click="toggleMensajes">
      <div class="message-icon">
        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path
            d="M21 15C21 15.5304 20.7893 16.0391 20.4142 16.4142C20.0391 16.7893 19.5304 17 19 17H7L3 21V5C3 4.46957 3.21071 3.96086 3.58579 3.58579C3.96086 3.21071 4.46957 3 5 3H19C19.5304 3 20.0391 3.21071 20.4142 3.58579C20.7893 3.96086 21 4.46957 21 5V15Z"
            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
        <span class="notification-badge" v-if="nuevosMensajes > 0">{{ nuevosMensajes }}</span>
      </div>

      <div class="messages-container" v-if="mostrarMensajes">
        <div class="messages-header">
          <h3>Mensajes</h3>
          <button class="close-btn" @click.stop="toggleMensajes">
            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M18 6L6 18M6 6L18 18" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                stroke-linejoin="round" />
            </svg>
          </button>
        </div>

        <div class="messages-list">
          <div v-for="(mensaje, index) in mensajes" :key="index" class="message-item">
            <img :src="mensaje.entrenador.foto" :alt="mensaje.entrenador.nombre" class="message-avatar">
            <div class="message-content">
              <div class="message-header">
                <span class="sender-name">{{ mensaje.entrenador.nombre }}</span>
                <span class="message-time">{{ mensaje.hora }}</span>
              </div>
              <p class="message-text">{{ mensaje.texto }}</p>
            </div>
          </div>

          <div v-if="mensajes.length === 0" class="empty-messages">
            No tienes mensajes nuevos
          </div>
        </div>

        <div class="messages-footer">
          <button class="view-all-btn" @click.stop="verTodosLosMensajes">
            Ver todos los mensajes
          </button>
        </div>
      </div>
    </div>

  </div>
</template>



<script>
import Navbar from '../navbarComponent.vue';

export default {
  name: 'Entrenadores',
  components: {
    Navbar
  },
  data() {
    return {
      user: [],
      scrollPosition: 0,
      busqueda: '',
      deporteActivo: 'Todos',
      deportes: ['Todos', 'Fútbol', 'Tenis', 'Baloncesto', 'Natación', 'Ciclismo', 'Atletismo', 'Artes Marciales'],
      entrenadorSeleccionado: null,
      mostrarMensajes: false,
      nuevosMensajes: 2,
      mostrarFormularioContacto: false,
      contactoEntrenador: null,
      formularioContacto: {
        edad: '',
        nivel: '',
        objetivos: '',
      },
      entrenadores: [
        {
          id: 1,
          nombre: 'Carlos Pérez',
          deporte: 'Fútbol',
          experiencia: '10 años entrenando equipos profesionales. Especialista en táctica.',
          foto: '/imagenes/Entrenador1.png',
          testimonio: 'Mi enfoque es desarrollar jugadores inteligentes que entiendan el juego',
          rating: 5,
          reseñas: 24,
          biografia: 'Ex jugador profesional con más de 200 partidos en primera división. Certificado UEFA Pro.',
          especialidades: ['Táctica avanzada', 'Preparación física', 'Análisis de video'],
          logros: [
            'Campeón Liga Nacional 2018',
            'Entrenador del año 2020',
            'Formador de 5 jugadores profesionales'
          ],
          testimonios: [
            {
              texto: 'Carlos transformó completamente mi visión del juego en solo 3 meses',
              autor: 'Juan Martínez'
            },
            {
              texto: 'El mejor entrenador que he tenido, su atención al detalle es increíble',
              autor: 'María González'
            }
          ]
        },
        {
          id: 2,
          nombre: 'Ana Gómez',
          deporte: 'Tenis',
          experiencia: '12 años formando tenistas profesionales. Especialista en técnica de golpes.',
          foto: '/imagenes/Entrenador2.png',
          testimonio: 'Enseño a mis alumnos a pensar como campeones dentro y fuera de la cancha',
          rating: 4.9,
          reseñas: 18,
          biografia: 'Ex tenista profesional (top 100 WTA). Certificada por la Federación Internacional de Tenis.',
          especialidades: ['Servicio y volea', 'Preparación mental', 'Estrategia de partido'],
          logros: [
            'Entrenadora de 3 campeones nacionales juveniles',
            'Premio Excelencia Docente 2019',
            'Capacitadora de entrenadores ITF'
          ],
          testimonios: [
            {
              texto: 'Ana me enseñó a superar mis límites mentales en la cancha',
              autor: 'Roberto Sánchez'
            },
            {
              texto: 'Su método de entrenamiento técnico es simplemente brillante',
              autor: 'Lucía Fernández'
            }
          ]
        },
        {
          id: 3,
          nombre: 'Juan Díaz',
          deporte: 'Baloncesto',
          experiencia: '15 años desarrollando talento en categorías formativas y profesionales.',
          foto: '/imagenes/Entrenador3.png',
          testimonio: 'Creo en el baloncesto como herramienta para formar carácter y disciplina',
          rating: 4.8,
          reseñas: 32,
          biografia: 'Ex seleccionado nacional y jugador profesional en Europa. Licenciado en Ciencias del Deporte.',
          especialidades: ['Fundamentos técnicos', 'Defensa intensiva', 'Lectura de juego'],
          logros: [
            'Campeón Nacional de Desarrollo 2021',
            'Formador de 8 jugadores NBA G-League',
            'Director Técnico Academia Nacional Juvenil'
          ],
          testimonios: [
            {
              texto: 'Juan tiene la paciencia y conocimiento para sacar lo mejor de cada jugador',
              autor: 'Carlos Rivera'
            },
            {
              texto: 'Transformó completamente nuestro equipo en una temporada',
              autor: 'Equipo Colegio San Andrés'
            }
          ]
        },
        {
          id: 4,
          nombre: 'Laura Martínez',
          deporte: 'Natación',
          experiencia: '10 años entrenando nadadores de élite y categorías inferiores.',
          foto: '/imagenes/Entrenador4.png',
          testimonio: 'Mi filosofía es combinar técnica perfecta con resistencia mental',
          rating: 5,
          reseñas: 27,
          biografia: 'Ex nadadora olímpica y medallista panamericana. Certificada ASCA Nivel 5.',
          especialidades: ['Técnica de estilos', 'Entrenamiento en seco', 'Preparación para competencia'],
          logros: [
            '3 nadadores en Juegos Olímpicos',
            'Récord nacional en 200m mariposa (entrenadora)',
            'Premio Excelencia en Natación 2022'
          ],
          testimonios: [
            {
              texto: 'Laura me llevó de nadador amateur a competencias nacionales en un año',
              autor: 'Diego Ramírez'
            },
            {
              texto: 'Su conocimiento biomecánico es extraordinario',
              autor: 'Federación Nacional de Natación'
            }
          ]
        },
        {
          id: 5,
          nombre: 'Ricardo Gómez',
          deporte: 'Ciclismo',
          experiencia: '8 años formando ciclistas para competencias internacionales.',
          foto: '/imagenes/Entrenador5.png',
          testimonio: 'Enseño a mis atletas a amar el proceso tanto como los resultados',
          rating: 4.7,
          reseñas: 15,
          biografia: 'Ex ciclista profesional (Tour de Francia, Giro de Italia). Ingeniero en Alto Rendimiento.',
          especialidades: ['Estrategia de carrera', 'Nutrición deportiva', 'Preparación física específica'],
          logros: [
            '2 campeones nacionales de ruta',
            'Entrenador del año UCI 2021',
            'Desarrollo del programa "Ciclismo Seguro"'
          ],
          testimonios: [
            {
              texto: 'Ricardo me enseñó a competir inteligentemente, no solo fuerte',
              autor: 'Alejandro Castro'
            },
            {
              texto: 'Su programa de entrenamiento me llevó a otro nivel',
              autor: 'Mariana Torres'
            }
          ]
        },
        {
          id: 6,
          nombre: 'Sofia Martínez',
          deporte: 'Atletismo',
          experiencia: '9 años entrenando velocistas y fondistas de alto rendimiento.',
          foto: '/imagenes/Entrenador6.png',
          testimonio: 'Creo en el atletismo como escuela de vida y superación personal',
          rating: 4.9,
          reseñas: 21,
          biografia: 'Ex atleta olímpica en 400m vallas. Máster en Psicología del Deporte.',
          especialidades: ['Técnica de carrera', 'Preparación mental', 'Periodización de entrenamientos'],
          logros: [
            '3 atletas en Campeonatos Mundiales',
            'Récord nacional juvenil 400m (entrenadora)',
            'Premio Nacional de Atletismo 2020'
          ],
          testimonios: [
            {
              texto: 'Sofia me enseñó que los límites están en la mente',
              autor: 'Javier Mendoza'
            },
            {
              texto: 'Su enfoque holístico del entrenamiento cambió mi carrera',
              autor: 'Ana Lucía Rodríguez'
            }
          ]
        },
      ],
      mensajes: [
        {
          entrenador: {
            nombre: 'Carlos Pérez',
            foto: '/imagenes/Entrenador1.png'
          },
          texto: 'Hola, estoy interesado en tus servicios. ¿Podríamos hablar?',
          hora: '10:30 AM',
          leido: false
        },
        {
          entrenador: {
            nombre: 'Ana Gómez',
            foto: '/imagenes/Entrenador2.png'
          },
          texto: 'Confirmado el entrenamiento para el viernes a las 5pm',
          hora: 'Ayer',
          leido: false
        }
      ],
    }
  },
  computed: {

    entrenadoresFiltrados() {
      let filtrados = this.entrenadores;

      if (this.deporteActivo !== 'Todos') {
        filtrados = filtrados.filter(e => e.deporte === this.deporteActivo);
      }

      if (this.busqueda) {
        const term = this.busqueda.toLowerCase();
        filtrados = filtrados.filter(e =>
          e.nombre.toLowerCase().includes(term) ||
          e.deporte.toLowerCase().includes(term) ||
          e.especialidades.some(esp => esp.toLowerCase().includes(term))
        );
      }

      return filtrados;
    }
  },
  methods: {

    filtrarPorDeporte(deporte) {
      this.deporteActivo = deporte;
    },

    verPerfil(entrenador) {
      // Guardar posición actual del scroll antes de abrir el modal
      this.scrollPosition = window.pageYOffset || document.documentElement.scrollTop;

      // Deshabilitar scroll del body
      document.body.style.overflow = 'hidden';
      document.body.style.position = 'fixed';
      document.body.style.top = `-${this.scrollPosition}px`;
      document.body.style.width = '100%';

      this.entrenadorSeleccionado = entrenador;
    },

    cerrarPerfil() {
      try {
        // Habilitar scroll del body
        document.body.style.overflow = 'auto';
        document.body.style.position = '';
        document.body.style.top = '';
        document.body.style.width = '';

        // Restaurar posición del scroll
        window.scrollTo(0, this.scrollPosition);
      } finally {
        document.body.style.overflow = 'auto';
        this.entrenadorSeleccionado = null;
      }
    },

    contactarEntrenador(entrenador) {
      this.contactoEntrenador = {
        id: entrenador.id,
        nombre: entrenador.nombre
      };
      this.mostrarFormularioContacto = true;

      // Guardar posición actual del scroll antes de abrir el modal
      this.scrollPosition = window.pageYOffset || document.documentElement.scrollTop;

      // Deshabilitar scroll del body
      document.body.style.overflow = 'hidden';
      document.body.style.position = 'fixed';
      document.body.style.top = `-${this.scrollPosition}px`;
      document.body.style.width = '100%';
    },

    cerrarFormularioContacto() {
      try {
        // Habilitar scroll del body
        document.body.style.overflow = 'auto';
        document.body.style.position = '';
        document.body.style.top = '';
        document.body.style.width = '';

        // Restaurar posición del scroll
        window.scrollTo(0, this.scrollPosition);
      } finally {
        this.mostrarFormularioContacto = false;
        this.formularioContacto = {
          edad: '',
          nivel: '',
          objetivos: ''
        };
      }
    },

    toggleMensajes() {
      this.mostrarMensajes = !this.mostrarMensajes;
      if (this.mostrarMensajes && this.nuevosMensajes > 0) {
        this.nuevosMensajes = 0; // Resetear notificaciones al abrir
      }
    },

    verTodosLosMensajes() {
      // Navegar a la página completa de mensajes
      this.$router.push('/Mensajes');
    },

    enviarFormularioContacto() {
      // Verificar que el usuario está autenticado
      if (!this.user || !this.user.id) {
        alert('Debes iniciar sesión para contactar a un entrenador');
        return;
      }

      // Preparar los datos para enviar
      const formData = {
        user_id: this.user.id, // ID 
        trainer_id: this.contactoEntrenador.id, // ID del entrenador
        age: this.formularioContacto.edad,
        sport_level: this.formularioContacto.nivel,
        description: this.formularioContacto.objetivos,
        status: 'pending' // Estado
      };

      axios.post('/training', formData)
        .then(response => {
          if (response.status === 201) {
            alert(`Solicitud enviada a ${this.contactoEntrenador.nombre} con éxito`);
            this.cerrarFormularioContacto();
            this.cerrarPerfil();
          }
        })
        .catch(error => {
          if (error.response?.status === 422) {
            const errors = error.response.data.errors;
            let errorMsg = Object.values(errors).flat().join('\n');
            alert(`Error de validación:\n${errorMsg}`);
          } else {
            // Otros errores
            console.error('Error completo:', error);
            alert('Error al enviar la solicitud');
          }
        });
    },

    mostrarFeedbackExito() {
      console.log('Solicitud enviada con éxito');
    }

  },
  mounted() {
    this.user = JSON.parse(sessionStorage.getItem('user'));
    document.title = 'Entrenadores';
  }
};
</script>





<style scoped>
@import '../../../scss/Entrenadores/entrenadores.scss';

@import '../../../scss/Entrenadores/entrenadores_grid.scss';

@import '../../../scss/Entrenadores/entrenadores_mensajes.scss';

@import '../../../scss/Entrenadores/entrenadores_modal.scss';

@import '../../../scss/Entrenadores/entrenadores_navbar.scss';

@import '../../../scss/Entrenadores/entrenadores_responsive.scss';


/* Nuevos estilos para el modal de contacto */
.contact-modal {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.7);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.contact-modal .modal-content {
  background-color: white;
  border-radius: 12px;
  width: 90%;
  max-width: 500px;
  padding: 30px;
  position: relative;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
}

.modal-title {
  text-align: center;
  margin-bottom: 25px;
  color: #2c3e50;
  font-size: 1.5rem;
}

.contact-form {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.form-group {
  display: flex;
  flex-direction: column;
}

.form-group label {
  margin-bottom: 8px;
  font-weight: 600;
  color: #34495e;
}

.form-group input,
.form-group select,
.form-group textarea {
  padding: 12px 15px;
  border: 1px solid #ddd;
  border-radius: 8px;
  font-size: 1rem;
  transition: border-color 0.3s;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
  border-color: #3498db;
  outline: none;
  box-shadow: 0 0 0 2px rgba(52, 152, 219, 0.2);
}

.form-group textarea {
  resize: vertical;
  min-height: 100px;
}

.submit-btn {
  background-color: #3498db;
  color: white;
  border: none;
  border-radius: 8px;
  padding: 14px;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: background-color 0.3s;
}

.submit-btn:hover {
  background-color: #2980b9;
}

.close-modal {
  position: absolute;
  top: 15px;
  right: 15px;
  background: none;
  border: none;
  cursor: pointer;
  width: 30px;
  height: 30px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  transition: background-color 0.3s;
}

.close-modal:hover {
  background-color: #f0f0f0;
}

.close-modal svg {
  width: 20px;
  height: 20px;
}
</style>
