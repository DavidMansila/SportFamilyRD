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
        <div v-for="entrenador in entrenadoresFiltrados" :key="entrenador.trainer_id" class="entrenador-card"
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
                <span v-for="(esp, i) in entrenadorSeleccionado.especialidades" :key="i" class="especialidad-tag">
                  {{ esp }}
                </span>
              </div>
            </div>

            <div class="section">
              <h3>Logros</h3>
              <div class="logros">
                <ul class="logros">
                  <li v-for="(logro, index) in entrenadorSeleccionado.logros" :key="index">
                    {{ logro }}
                  </li>
                </ul>
              </div>
            </div>


            <div class="section" v-if="entrenadorSeleccionado && entrenadorSeleccionado.horario">
              <h3 class="horario-titulo">🗓️ Horario Disponible</h3>
              <div class="horario-grid">
                <div v-for="(diaAbrev, index) in ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom']" :key="index"
                  class="horario-dia"
                  :class="{ disponible: isDisponible(diaAbrev), noDisponible: !isDisponible(diaAbrev) }">
                  <span class="dia-nombre">{{ diaAbrev }}</span>
                  <span class="estado-icono">
                    <template v-if="isDisponible(diaAbrev)">
                      ✅ Disponible
                      <br />
                      <small>{{ getHorario(diaAbrev).desde }} - {{ getHorario(diaAbrev).hasta }}</small>
                    </template>
                    <template v-else>
                      ❌ No Disponible
                    </template>
                  </span>
                </div>
              </div>
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

          <div class="profile-info">
            <div class="user-details">
              <span class="user-avatar">
                <img :src="user.image || 'public/storage/users/Perfil-Icon.png'" alt="Tu foto de perfil">
              </span>
              <div>
                <p><strong>Nombre:</strong> {{ user.name }}</p>
                <p v-if="user.email"><strong>Email:</strong> {{ user.email }}</p>
              </div>
            </div>
            <p class="mensaje"> <strong> Tus datos de perfil serán enviados al entrenador para que pueda conocerte mejor
              </strong> </p>
          </div>

          <form @submit.prevent="enviarFormularioContacto" class="contact-form">
            <div class="form-group">
              <label for="objetivos">¿Qué buscas aprender o lograr?</label>
              <textarea id="objetivos" v-model="formularioContacto.objetivos" rows="4"
                placeholder="Ej: Mejorar mi técnica de tiro, prepararme para una competencia, perder peso..."
                required></textarea>
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

            <p class="data-notice">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path
                  d="M12 8V12M12 16H12.01M22 12C22 17.5228 17.5228 22 12 22C6.47715 22 2 17.5228 2 12C2 6.47715 6.47715 2 12 2C17.5228 2 22 6.47715 22 12Z"
                  stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
              </svg>
              Solo podras mandar una solicitud por semana a cada entrenador. Si ya has enviado una solicitud reciente,
              no
              podrás enviar otra hasta que se procese la anterior.
            </p>

            <button type="submit" class="submit-btn">Enviar Solicitud</button>
          </form>
        </div>
      </div>
    </transition>



    <!-- Burbuja de Mensajes Flotante -->
    <div v-if="user" class="message-bubble" :class="{ 'expanded': mostrarMensajes }">
      <div class="message-icon-container" @click="toggleMensajes">
        <svg class="message-icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path
            d="M21 15C21 15.5304 20.7893 16.0391 20.4142 16.4142C20.0391 16.7893 19.5304 17 19 17H7L3 21V5C3 4.46957 3.21071 3.96086 3.58579 3.58579C3.96086 3.21071 4.46957 3 5 3H19C19.5304 3 20.0391 3.21071 20.4142 3.58579C20.7893 3.96086 21 4.46957 21 5V15Z"
            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
        <span class="notification-badge" v-if="nuevosMensajes > 0">{{ nuevosMensajes }}</span>
      </div>

      <div v-if="mostrarMensajes" class="messages-container">
        <div class="messages-header" v-if="!activeChat">
          <h3>Chats</h3>
          <button class="close-btn" @click="toggleMensajes">×</button>
        </div>

        <div v-if="!activeChat" class="contact-list">
          <div v-for="chat in chatsAprobados" :key="chat.id" class="contact-item" @click="openChat(chat)">
            <img :src="chat.other_participant.image" class="message-avatar" />
            <div class="message-content">
              <div class="message-header">
                <span class="sender-name">{{ chat.other_participant.name }}</span>
                <span v-if="chat.unread" class="unread-badge">{{ chat.unread }}</span>
              </div>
              <p v-if="chat.last_message" class="message-preview">
                <span v-if="chat.last_message.sender_id === user.id">Tú: </span>
                {{ truncateText(chat.last_message.message, 30) }}
              </p>
              <p v-else class="no-messages">No hay mensajes aún</p>
            </div>
          </div>

          <div v-if="chatsAprobados.length === 0" class="empty-chats">
            <p>Aun no tienes Chats</p>
          </div>
        </div>

        <div v-else class="active-chat-container">
          <ChatComponent ref="chatComponent" :active-chat="activeChat" :user="user" @close-chat="cerrarChat"
            @messages-read="loadChats" />
        </div>
      </div>
    </div>




  </div>
</template>



<script>
import axios from 'axios';
import Navbar from '../navbarComponent.vue';
import ChatComponent from '../ChatComponent.vue';

export default {
  name: 'Entrenadores',
  components: {
    Navbar,
    ChatComponent
  },
  data() {
    return {
      user: [],
      scrollPosition: 0,
      busqueda: '',
      deporteActivo: 'Todos',
      dias: ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'],
      deportes: ['Todos', 'Fútbol', 'Tenis', 'Baloncesto', 'Natación', 'Ciclismo', 'Atletismo', 'Artes Marciales'],
      entrenadorSeleccionado: null,
      mostrarFormularioContacto: false,
      contactoEntrenador: null,
      formularioContacto: {
        nivel: '',
        objetivos: ''
      },
      entrenadores: [],

      chats: [],
      mostrarMensajes: false,
      nuevosMensajes: 0,
      activeChat: null,
      echoListener: null,
      presenceListener: null
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
    },
    chatsAprobados() {
      return this.chats.filter(chat => chat.status === 'accepted');
    }
  },
  methods: {

    // async obtenerChats() {
    //   const res = await axios.get('/chats', { params: { user_id: this.user.id } });
    //   this.chats = res.data;
    //   this.chatsAprobados = this.chats.filter(chat => chat.estado === 'aprobado');
    // },

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
        trainer_id: entrenador.trainer_id,  // Usar trainer_id del entrenador
        user_id: entrenador.user_id,        // Usar user_id del entrenador
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


    async enviarFormularioContacto() {
      if (!this.user?.id) {
        alert('Debes iniciar sesión para contactar a un entrenador');
        return;
      }

      try {
        // 1. Verificar si ya existe solicitud
        const checkResponse = await axios.post('/training/check-existing', {
          user_id: this.user.id,
          trainer_id: this.contactoEntrenador.trainer_id
        });

        if (checkResponse.data.exists) {
          alert(`Ya has enviado una solicitud a ${this.contactoEntrenador.nombre} recientemente.`);
          return;
        }

        // 2. Enviar la solicitud principal
        const formData = {
          user_id: this.user.id,
          trainer_id: this.contactoEntrenador.trainer_id,
          sport_level: this.formularioContacto.nivel,
          description: this.formularioContacto.objetivos,
          status: 'pending'
        };

        const response = await axios.post('/training', formData);

        if (response.status === 201) {
          alert(`Solicitud enviada a ${this.contactoEntrenador.nombre} con éxito`);
          this.cerrarFormularioContacto();
          this.cerrarPerfil();
        }
      } catch (error) {
        // Manejo de errores unificado
        if (error.response?.status === 422) {
          const errors = error.response.data.errors;
          let errorMsg = Object.values(errors).flat().join('\n');
          alert(`Error de validación:\n${errorMsg}`);
        } else {
          console.error('Error completo:', error);
          alert('Error al procesar la solicitud: ' + error.message);
        }
      }
    },


    cargarEntrenadores() {
      axios.get('/trainer/approved')
        .then(response => {
          this.entrenadores = response.data.trainers.map(trainer => ({
            trainer_id: trainer.id,
            user_id: trainer.user_id,
            nombre: trainer.name,
            deporte: trainer.sport_category,
            experiencia: trainer.experience,
            foto: trainer.image,
            rating: trainer.rating || 5,
            reseñas: trainer.reviews || 0,
            biografia: trainer.description || '',
            horario: trainer.schedule,
            especialidades: trainer.specialties ? trainer.specialties.map(e => e.description || e.name) : [],
            logros: trainer.achievements ? trainer.achievements.map(a => `${a.title}${a.date ? ` (${a.date})` : ''}`) : []
          }));
        })
        .catch(error => {
          console.error('Error al cargar entrenadores aprobados:', error);
          alert('Error al cargar entrenadores aprobados.');
        });
    },


    diaCompleto(diaAbrev) {
      const mapaDias = {
        'Lun': 'Lunes',
        'Mar': 'Martes',
        'Mié': 'Miércoles',
        'Jue': 'Jueves',
        'Vie': 'Viernes',
        'Sáb': 'Sábado',
        'Dom': 'Domingo'
      };
      return mapaDias[diaAbrev] || diaAbrev;
    },

    // Devuelve true si el día está disponible
    isDisponible(diaAbrev) {
      if (!this.entrenadorSeleccionado || !this.entrenadorSeleccionado.horario) return false;
      try {
        const horario = typeof this.entrenadorSeleccionado.horario === 'string'
          ? JSON.parse(this.entrenadorSeleccionado.horario)
          : this.entrenadorSeleccionado.horario;

        const dia = this.diaCompleto(diaAbrev);
        return horario[dia]?.available === true;
      } catch (error) {
        console.error('Error parseando horario:', error);
        return false;
      }
    },

    // Devuelve objeto {desde, hasta} con horas para ese día o vacíos si no disponible
    getHorario(diaAbrev) {
      if (!this.entrenadorSeleccionado || !this.entrenadorSeleccionado.horario) return { desde: '', hasta: '' };
      try {
        const horario = typeof this.entrenadorSeleccionado.horario === 'string'
          ? JSON.parse(this.entrenadorSeleccionado.horario)
          : this.entrenadorSeleccionado.horario;

        const dia = this.diaCompleto(diaAbrev);
        if (horario[dia]?.available) {
          return {
            desde: horario[dia].hours?.desde || '',
            hasta: horario[dia].hours?.hasta || ''
          };
        } else {
          return { desde: '', hasta: '' };
        }
      } catch (error) {
        console.error('Error parseando horario:', error);
        return { desde: '', hasta: '' };
      }
    },


    toggleMensajes() {
      this.mostrarMensajes = !this.mostrarMensajes;
      if (this.mostrarMensajes) {
        this.loadChats();
        document.body.classList.add('chat-open');
      } else {
        document.body.classList.remove('chat-open');
      }
    },

    truncateText(text, maxLength) {
      if (!text) return '';
      if (text.length <= maxLength) return text;
      return text.substring(0, maxLength) + '...';
    },

    async openChat(chat) {
      this.activeChat = chat;
      await this.markMessagesAsRead(chat.id);

      // Solo configurar canales en producción
      if (process.env.NODE_ENV === 'production') {
        this.setupChatChannel(chat.id);
      }

      await this.$nextTick();

      if (this.$refs.chatComponent?.loadMessages) {
        await this.$refs.chatComponent.loadMessages();
      }
    },

    cerrarChat() {
      this.leaveChatChannel();
      this.activeChat = null;
      this.loadChats();
      document.body.classList.remove('chat-open');
    },



    async loadChats() {
      if (!this.user) return;
      try {
        const response = await axios.get('/chats');
        console.log('Respuesta de /chats:', response.data);

        this.chats = response.data.map(chat => {
          let otherParticipant = null;

          if (this.user.user_type === 'user') {
            // Acceder al usuario asociado al entrenador
            otherParticipant = {
              id: chat.trainer.id,
              name: chat.trainer.user.name,
              image: chat.trainer.user.image
                ? `/storage/users/${chat.trainer.user.id}/${chat.trainer.user.image}`
                : 'public/storage/users/Perfil-Icon.png',
              type: 'trainer'
            };
          }
          else if (this.user.user_type === 'entrenador') {
            otherParticipant = {
              id: chat.user.id,
              name: chat.user.name,
              image: chat.user.image
                ? `/storage/users/${chat.user.id}/${chat.user.image}`
                : 'public/storage/users/Perfil-Icon.png',
              type: 'user'
            };
          }

          return {
            id: chat.id,
            user_id: chat.user_id,
            trainer_id: chat.trainer_id,
            status: chat.status,
            unread: chat.unread_count,
            last_message: chat.last_message,
            other_participant: otherParticipant
          };
        });

        this.nuevosMensajes = this.chats.reduce((total, chat) => total + chat.unread, 0);
      } catch (error) {
        console.error('Error cargando chats', error);
      }
    },



    getChatAvatar(chat) {
      return chat.other_participant?.image || 'public/storage/users/Perfil-Icon.png';
    },

    getChatName(chat) {
      return chat.other_participant?.name || 'Usuario desconocido';
    },

    calculateUnreadMessages() {
      this.nuevosMensajes = this.chatsAprobados.reduce((total, chat) => {
        return total + (parseInt(chat.unread) || 0);
      }, 0);
    },

    async markMessagesAsRead(chatId) {
      try {
        await axios.post(`/chats/${chatId}/read`);
        // Actualizar solo el chat actual en lugar de recargar todos
        this.chats = this.chats.map(chat => {
          if (chat.id === chatId) {
            return { ...chat, unread: 0 };
          }
          return chat;
        });
      } catch (error) {
        console.error('Error marcando mensajes como leídos', error);
      }
    },

    setupChatChannel(chatId) {
      this.leaveChatChannel();

      if (typeof window.Echo === 'undefined') return;

      // Suscribirse al canal privado usando el prefijo correcto
      this.echoListener = window.Echo.private(`private-chat.${chatId}`)
        .listen('.message.sent', (data) => {
          if (this.activeChat && this.activeChat.id === chatId) {
            this.$refs.chatComponent?.handleNewMessage(data);
          }
          this.loadChats();
        })
        .listen('.message.read', (data) => {
          if (this.activeChat && this.activeChat.id === chatId) {
            this.$refs.chatComponent?.updateReadStatus(data);
          }
        });

      // Canal de presencia para estado en línea
      this.presenceListener = window.Echo.join(`presence-chat.${chatId}`)
        .here((users) => {
          if (this.activeChat && this.activeChat.id === chatId) {
            this.$refs.chatComponent?.updateOnlineStatus(users);
          }
        })
        .joining((user) => {
          if (this.activeChat && this.activeChat.id === chatId) {
            this.$refs.chatComponent?.userJoined(user);
          }
        })
        .leaving((user) => {
          if (this.activeChat && this.activeChat.id === chatId) {
            this.$refs.chatComponent?.userLeft(user);
          }
        });
    },

    leaveChatChannel() {
      if (this.echoListener) {
        window.Echo.leave(`chat.${this.activeChat?.id}`);
        this.echoListener = null;
      }
      if (this.presenceListener) {
        window.Echo.leave(`presence-chat.${this.activeChat?.id}`);
        this.presenceListener = null;
      }
    },

    setupGlobalListeners() {
      if (typeof window.Echo === 'undefined') return;

      if (this.user && this.user.token) {
        window.Echo.private(`user.${this.user.id}`)
          .listen('.message.sent', (data) => {
            console.log('Nuevo mensaje recibido globalmente:', data);

            if (this.activeChat && this.activeChat.id === data.chat_id) {
              if (this.$refs.chatComponent?.handleNewMessage) {
                this.$refs.chatComponent.handleNewMessage(data);
              }
            }
            this.loadChats();
          })
          .listen('.message.read', (data) => {
            if (this.activeChat && this.activeChat.id === data.chat_id) {
              if (this.$refs.chatComponent?.updateReadStatus) {
                this.$refs.chatComponent.updateReadStatus(data);
              }
            }
          });
      }
    },


    async loadEchoLibrary() {
      try {
        const EchoModule = await import('laravel-echo');
        const PusherModule = await import('pusher-js');

        window.Pusher = PusherModule.default;

        // Configuración completa para todos los entornos
        window.Echo = new EchoModule.default({
          broadcaster: 'pusher',
          key: import.meta.env.VITE_PUSHER_APP_KEY,
          cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
          wsHost: `ws-${import.meta.env.VITE_PUSHER_APP_CLUSTER}.pusher.com`,
          wssHost: `ws-${import.meta.env.VITE_PUSHER_APP_CLUSTER}.pusher.com`,
          forceTLS: true,
          encrypted: true,
          disableStats: true,
          enabledTransports: ['ws', 'wss'],
          authEndpoint: "/broadcasting/auth",
          auth: {
            headers: {
              Authorization: `Bearer ${this.user.token}`,
              'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content || ''
            }
          }
        });

        this.setupGlobalListeners();
      } catch (error) {
        console.error('Error cargando Echo:', error);
      }
    },

  },
  mounted() {
    this.user = JSON.parse(sessionStorage.getItem('user'));
    if (this.user) {
      this.cargarEntrenadores();
      this.loadChats();
      this.loadEchoLibrary();
    }
  },
  beforeUnmount() {
    this.leaveChatChannel();

    if (window.Echo && this.user) {
      window.Echo.leave(`user.${this.user.id}`);
    }
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


.horario-titulo {
  font-size: 1.25rem;
  margin-bottom: 1rem;
  color: #333;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.horario-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
  gap: 0.75rem;
}

.horario-dia {
  padding: 0.75rem;
  border-radius: 10px;
  background-color: #f1f1f1;
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
  text-align: center;
  transition: 0.3s ease;
}

.horario-dia.disponible {
  background-color: #e6ffec;
  border: 1px solid #8de4a3;
}

.horario-dia:not(.disponible) {
  background-color: #ffeaea;
  border: 1px solid #f5a8a8;
}

.dia-nombre {
  font-weight: bold;
  font-size: 1rem;
  display: block;
  margin-bottom: 0.25rem;
}

.estado-icono {
  font-size: 0.9rem;
}


.profile-info {
  background-color: #f8f9fa;
  border-radius: 8px;
  padding: 15px;
  margin-bottom: 20px;

  p {
    margin-bottom: 10px;
    color: #495057;
  }
}

.user-details {
  display: flex;
  align-items: center;
  gap: 15px;
  background: white;
  padding: 12px;
  border-radius: 8px;
  border: 1px solid #e9ecef;

  p {
    margin: 5px 0;
    font-size: 0.95rem;
  }
}

.user-avatar {
  width: 50px;
  height: 50px;
  border-radius: 50%;
  overflow: hidden;

  img {
    width: 100%;
    height: 100%;
    object-fit: cover;
  }
}

.data-notice {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 0.85rem;
  color: #6c757d;
  margin-top: 10px;
  padding: 10px;
  background-color: #e8f4fd;
  border-radius: 8px;

  svg {
    flex-shrink: 0;
  }
}

.mensaje {
  font-size: 0.9rem;
  color: #6c757d;
  margin-top: 10px;
}
</style>
