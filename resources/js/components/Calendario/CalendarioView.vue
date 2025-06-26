<template>
  <div class="app-container">



    <!-- Navbar -->
    <Navbar />



    <!-- Vista principal del calendario -->
    <div class="calendar-view" :class="{ 'event-view-active': selectedEvent }">
      <!-- Vista de calendario -->
      <div class="calendar-main">
        <div class="calendar-controls">
          <button @click="changeMonth('prev')" class="control-btn">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
              stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <polyline points="15 18 9 12 15 6"></polyline>
            </svg>
          </button>
          <h1 class="calendar-title">{{ monthNames[currentMonth] }} {{ currentYear }}</h1>
          <button @click="changeMonth('next')" class="control-btn">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
              stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <polyline points="9 18 15 12 9 6"></polyline>
            </svg>
          </button>
        </div>

        <div class="calendar-grid">
          <div v-for="(day, index) in daysOfWeek" :key="index" class="calendar-day-header">
            {{ day }}
          </div>
          <div v-for="day in daysInMonth" :key="day" :class="['calendar-day', {
            'has-events': day && hasEvents(day),
            'selected-day': day && selectedDay === day,
            'current-day': day && isCurrentDay(day)
          }]" @click="day && selectDay(day)">
            <span class="day-number" v-if="day">{{ day }}</span>
            <div v-if="day && hasEvents(day)" class="event-dots">
              <span v-for="(event, index) in getEventsForDay(day)" :key="index"
                :style="{ backgroundColor: event.categoryColor || '#3498db' }"></span>
            </div>
          </div>
        </div>
      </div>



      <!-- Panel lateral de eventos del día -->
      <div class="events-sidebar">
        <h2 v-if="selectedDay">Eventos del {{ selectedDay }} de {{ monthNames[currentMonth] }}</h2>
        <h2 v-else>Selecciona un día</h2>

        <div v-if="selectedDayEvents.length > 0" class="events-list">
          <div v-for="event in selectedDayEvents" :key="event.id" class="event-card" @click="openEventDetail(event)"
            :style="{ borderLeft: `4px solid ${event.categoryColor || '#3498db'}` }">
            <div class="event-time">{{ formatTime(event.startTime) }} - {{ formatTime(event.endTime) }}</div>
            <h3 class="event-title">{{ event.nombre }}</h3>
            <div class="event-meta">
              <span class="event-location">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                  stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                  <circle cx="12" cy="10" r="3"></circle>
                </svg>
                {{ event.location || 'Ubicación no especificada' }}
              </span>
              <span class="event-price" v-if="event.precio">${{ event.precio }}</span>
              <span class="event-price free" v-else>Gratis</span>
            </div>
          </div>
        </div>
        <div v-else-if="selectedDay" class="no-events">
          <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none"
            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"></circle>
            <line x1="4.93" y1="4.93" x2="19.07" y2="19.07"></line>
          </svg>
          <p>No hay eventos programados para este día</p>
        </div>
        <div v-else class="select-day-prompt">
          <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none"
            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
            <line x1="16" y1="2" x2="16" y2="6"></line>
            <line x1="8" y1="2" x2="8" y2="6"></line>
            <line x1="3" y1="10" x2="21" y2="10"></line>
          </svg>
          <p>Selecciona un día para ver los eventos</p>
        </div>
      </div>
    </div>


    <!-- Vista detallada del evento -->
    <div class="event-detail-view" v-if="selectedEvent" @click.self="closeEventDetail">
      <div class="event-detail-container">
        <button class="close-btn" @click="closeEventDetail">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <line x1="18" y1="6" x2="6" y2="18"></line>
            <line x1="6" y1="6" x2="18" y2="18"></line>
          </svg>
        </button>

        <div class="event-header">
          <div class="event-date">
            <div class="event-day">{{ new Date(selectedEvent.fecha).getDate() }}</div>
            <div class="event-month">{{ monthNames[new Date(selectedEvent.fecha).getMonth()].substring(0, 3) }}</div>
          </div>
          <div class="event-title-container">
            <h2>{{ selectedEvent.nombre }}</h2>
            <div class="event-meta">
              <span class="event-time">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                  stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <circle cx="12" cy="12" r="10"></circle>
                  <polyline points="12 6 12 12 16 14"></polyline>
                </svg>
                {{ formatTime(selectedEvent.startTime) }} - {{ formatTime(selectedEvent.endTime) }}
              </span>
              <span class="event-location">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                  stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                  <circle cx="12" cy="10" r="3"></circle>
                </svg>
                {{ selectedEvent.location || 'Ubicación no especificada' }}
              </span>
            </div>
          </div>
        </div>

        <div class="event-content">
          <!-- <div class="event-description">
            <h3>Descripción</h3>
            <p>{{ selectedEvent.descripcion }}</p>
          </div> -->

          <div class="event-tickets">
            <h3>Boletos</h3>
            <div class="ticket-info">
              <span class="ticket-price">${{ selectedEvent.precio }} <small>c/u</small></span>
              <span class="ticket-available">{{ selectedEvent.boletosDisponibles }} disponibles</span>
            </div>

            <div class="ticket-controls" v-if="selectedEvent.boletosDisponibles > 0">
              <div class="quantity-selector">
                <button @click="decrementTicket" :disabled="ticketQuantity <= 1">-</button>
                <span>{{ ticketQuantity }}</span>
                <button @click="incrementTicket"
                  :disabled="ticketQuantity >= selectedEvent.boletosDisponibles">+</button>
              </div>
              <button class="add-to-cart-btn" @click="addToCart">
                Añadir al carrito - ${{ selectedEvent.precio * ticketQuantity }}
              </button>
            </div>
            <div v-else class="sold-out">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="4.93" y1="4.93" x2="19.07" y2="19.07"></line>
              </svg>
              <span>AGOTADO</span>
            </div>
          </div>
        </div>
      </div>
    </div>


  </div>
</template>

<script>
import axios from 'axios';
import Navbar from '../navbarComponent.vue';

export default {
  name: 'Calendario',
  components: {
    Navbar
  },
  data() {
    return {
      currentMonth: new Date().getMonth(),
      currentYear: new Date().getFullYear(),
      selectedDay: null, // Cambiado de new Date().getDate() a null
      selectedDayEvents: [],
      selectedEvent: null,
      ticketQuantity: 1,
      showCartPopup: false,
      calendarData: [],
      cartItems: [],
      user: null,
      monthNames: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
      daysOfWeek: ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'],
      eventos: [
        {
          id: 1,
          nombre: 'Torneo de Fútbol',
          fecha: '2025-03-15',
          startTime: '09:00',
          endTime: '18:00',
          descripcion: 'Un emocionante torneo de fútbol local con equipos de toda la región. Ven a apoyar a tu equipo favorito y disfruta de un día lleno de deporte y diversión.',
          boletosDisponibles: 100,
          precio: 50,
          location: 'Estadio Municipal',
          categoryColor: '#3498db'
        },
        {
          id: 2,
          nombre: 'Maratón Ciudad',
          fecha: '2025-03-25',
          startTime: '07:00',
          endTime: '12:00',
          descripcion: 'Participa en nuestra maratón anual de 10k. Rutas certificadas, medallas para todos los participantes y premios para los ganadores.',
          boletosDisponibles: 200,
          precio: 30,
          location: 'Parque Central',
          categoryColor: '#e74c3c'
        },
        {
          id: 3,
          nombre: 'Conferencia Deportiva',
          fecha: '2025-03-15',
          startTime: '14:00',
          endTime: '17:00',
          descripcion: 'Conferencia con expertos en deporte y salud. Aprende sobre las últimas tendencias en entrenamiento, nutrición y psicología deportiva.',
          boletosDisponibles: 50,
          precio: 20,
          location: 'Centro de Convenciones',
          categoryColor: '#2ecc71'
        },
        {
          id: 4,
          nombre: 'Partido de Tenis',
          fecha: '2025-04-18',
          startTime: '10:00',
          endTime: '13:00',
          descripcion: 'Partido amistoso de tenis entre los equipos regionales. Exhibición de dobles con jugadores profesionales.',
          boletosDisponibles: 75,
          precio: 40,
          location: 'Club de Tenis',
          categoryColor: '#9b59b6'
        },
        {
          id: 5,
          nombre: 'Torneo de Baloncesto',
          fecha: '2025-04-22',
          startTime: '08:00',
          endTime: '20:00',
          descripcion: 'Torneo local de baloncesto con equipos regionales. Tres categorías: infantil, juvenil y adultos.',
          boletosDisponibles: 120,
          precio: 25,
          location: 'Coliseo Municipal',
          categoryColor: '#f39c12'
        },
        {
          id: 6,
          nombre: 'Clínica de Natación',
          fecha: '2025-05-05',
          startTime: '15:00',
          endTime: '18:00',
          descripcion: 'Clínica intensiva para nadadores principiantes e intermedios. Instructores certificados y grupos reducidos.',
          boletosDisponibles: 60,
          precio: 35,
          location: 'Piscina Olímpica',
          categoryColor: '#1abc9c'
        }
      ],
      scrapEvents: [] // Aquí se guardarán los eventos del scrap
    };
  },
  computed: {
    daysInMonth() {
      const daysInMonth = new Date(this.currentYear, this.currentMonth + 1, 0).getDate();
      const firstDay = new Date(this.currentYear, this.currentMonth, 1).getDay();
      let days = [];

      // Días vacíos para alinear el primer día del mes
      for (let i = 0; i < firstDay; i++) {
        days.push(null);
      }

      // Días del mes
      for (let i = 1; i <= daysInMonth; i++) {
        days.push(i);
      }

      return days;
    },
    cartTotal() {
      return this.cartItems.reduce((total, item) => total + (item.price * item.quantity), 0);
    },
    // Nuevo: obtener los eventos a mostrar (scrap si hay, si no los de ejemplo)
    eventosToShow() {
      return this.scrapEvents.length > 0 ? this.scrapEvents : this.eventos;
    }
  },


  methods: {

    // getCalendarScrap() {
    //   axios.get('/scrap-calendar')
    //     .then(response => {
    //       console.log("Calendar data fetched successfully:", response.data.events);

    //       // Guardar los eventos scrappeados en la base de datos y actualizar scrapEvents con los IDs reales
    //       this.saveScrapEventsToDB(response.data.events);
    //     })
    //     .catch(error => {
    //       console.error("Error fetching calendar data:", error);
    //     });
    // },


    // Guarda los eventos scrappeados en la base de datos y actualiza scrapEvents con los IDs reales
    saveScrapEventsToDB(scrapRawEvents) {
      // Mapear los eventos al formato esperado por el backend
      const mappedEvents = scrapRawEvents.map(e => ({
        nombre: e.title,
        fecha: this.parseScrapDate(e.date),
        startTime: e.hour ? this.parseScrapHour(e.hour) : '',
        endTime: '',
        descripcion: '',
        boletosDisponibles: 100, // valor por defecto
        price: this.parseScrapPrice(e.price),
        place: e.place || '',
        categoryColor: '#3498db',
        links: e.links || [],
        image: e.image || ''
      }));
      axios.post('/scrap-calendar', { events: mappedEvents })
        .then(saveRes => {
          this.scrapEvents = saveRes.data;
          this.updateSelectedDayEvents();
        })
        .catch(saveErr => {
          console.error('Error guardando eventos scrappeados:', saveErr);
        });
    },
    // Parsear fecha tipo "6 jul., 2025" a formato YYYY-MM-DD
    parseScrapDate(dateStr) {
      const months = ['ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'];
      const match = dateStr.match(/(\d{1,2}) ([a-záéíóúñ]+)\., (\d{4})/i);
      if (!match) return '';
      const day = match[1].padStart(2, '0');
      const month = months.findIndex(m => match[2].toLowerCase().startsWith(m)) + 1;
      const year = match[3];
      return `${year}-${month.toString().padStart(2, '0')}-${day}`;
    },
    // Parsear hora tipo "06:00 AM" a "06:00"
    parseScrapHour(hourStr) {
      if (!hourStr) return '';
      const [time, ampm] = hourStr.split(' ');
      let [h, m] = time.split(':');
      h = parseInt(h);
      if (ampm && ampm.toUpperCase() === 'PM' && h !== 12) h += 12;
      if (ampm && ampm.toUpperCase() === 'AM' && h === 12) h = 0;
      return `${h.toString().padStart(2, '0')}:${m}`;
    },
    // Parsear precio tipo "RD$ 1,850.00" a número
    parseScrapPrice(priceStr) {
      if (!priceStr) return 0;
      const num = priceStr.replace(/[^\d.,]/g, '').replace(',', '');
      return parseFloat(num) || 0;
    },
    changeMonth(direction) {
      if (direction === 'prev') {
        if (this.currentMonth === 0) {
          this.currentMonth = 11;
          this.currentYear--;
        } else {
          this.currentMonth--;
        }
      } else {
        if (this.currentMonth === 11) {
          this.currentMonth = 0;
          this.currentYear++;
        } else {
          this.currentMonth++;
        }
      }

      this.selectedDay = null; // Asegura que ningún día esté seleccionado al cambiar de mes
      this.selectedDayEvents = [];
      this.updateSelectedDayEvents();
    },
    selectDay(day) {
      if (day) {
        this.selectedDay = day;
        this.updateSelectedDayEvents();
      }
    },
    updateSelectedDayEvents() {
      // Usar eventosToShow para filtrar los eventos del día
      this.selectedDayEvents = this.eventosToShow.filter(evento => {
        const eventDate = new Date(evento.fecha);
        return eventDate.getDate() === this.selectedDay &&
          eventDate.getMonth() === this.currentMonth &&
          eventDate.getFullYear() === this.currentYear;
      });
    },
    hasEvents(day) {
      return this.eventosToShow.some(evento => {
        const eventDate = new Date(evento.fecha);
        return eventDate.getDate() === day &&
          eventDate.getMonth() === this.currentMonth &&
          eventDate.getFullYear() === this.currentYear;
      });
    },
    getEventsForDay(day) {
      return this.eventosToShow.filter(evento => {
        const eventDate = new Date(evento.fecha);
        return eventDate.getDate() === day &&
          eventDate.getMonth() === this.currentMonth &&
          eventDate.getFullYear() === this.currentYear;
      });
    },
    isCurrentDay(day) {
      const today = new Date();
      return day === today.getDate() &&
        this.currentMonth === today.getMonth() &&
        this.currentYear === today.getFullYear();
    },
    formatTime(time) {
      if (!time) return '';
      const [hours, minutes] = time.split(':');
      const hour = parseInt(hours);
      const ampm = hour >= 12 ? 'PM' : 'AM';
      const hour12 = hour % 12 || 12;
      return `${hour12}:${minutes} ${ampm}`;
    },
    openEventDetail(event) {
      this.selectedEvent = event;
      this.ticketQuantity = 1;
    },
    closeEventDetail() {
      this.selectedEvent = null;
    },
    incrementTicket() {
      if (this.ticketQuantity < this.selectedEvent.boletosDisponibles) {
        this.ticketQuantity++;
      }
    },
    decrementTicket() {
      if (this.ticketQuantity > 1) {
        this.ticketQuantity--;
      }
    },
    async addToCart() {
      if (!this.user) {
        alert('Debes iniciar sesión para agregar eventos al carrito');
        return;
      }
      try {
        await axios.post('/cart/items', {
          item_type: 'event',
          item_id: this.selectedEvent.id,
          quantity: this.ticketQuantity,
          user_id: this.user.id
        });
        alert(`${this.selectedEvent.nombre} ha sido añadido al carrito`);
        window.dispatchEvent(new CustomEvent('cart-updated'));
        // Lógica visual local (opcional, para UX inmediata)
        const existingItem = this.cartItems.find(item => item.id === this.selectedEvent.id);
        if (existingItem) {
          existingItem.quantity += this.ticketQuantity;
        } else {
          this.cartItems.push({
            id: this.selectedEvent.id,
            name: this.selectedEvent.nombre,
            price: this.selectedEvent.precio,
            quantity: this.ticketQuantity
          });
        }
        // Actualizar disponibilidad local
        const event = this.eventos.find(e => e.id === this.selectedEvent.id);
        if (event) {
          event.boletosDisponibles -= this.ticketQuantity;
        }
        this.closeEventDetail();
        this.showCartPopup = true;
      } catch (error) {
        console.error('Error al agregar al carrito:', error);
        alert('No se pudo agregar el evento al carrito');
      }
    },
    removeFromCart(index) {
      const removedItem = this.cartItems[index];

      // Devolver boletos al evento
      const event = this.eventos.find(e => e.id === removedItem.id);
      if (event) {
        event.boletosDisponibles += removedItem.quantity;
      }

      this.cartItems.splice(index, 1);
    },
    toggleCartPopup() {
      this.showCartPopup = !this.showCartPopup;
    },
    checkout() {
      alert(`Compra realizada por $${this.cartTotal}. ¡Gracias por tu compra!`);
      this.cartItems = [];
      this.showCartPopup = false;
    },

    // Obtiene los eventos desde la base de datos usando /calendar
    getCalendarFromDB() {
      axios.get('/calendar')
        .then(response => {
          console.log('Eventos recibidos desde /calendar:', response.data.events);
          // Mapear los eventos al formato esperado por el frontend
          this.scrapEvents = response.data.events.map(e => ({
            id: e.id,
            nombre: e.name || e.title || e.place || 'Sin nombre',
            fecha: e.date,
            startTime: e.time || '',
            endTime: '',
            descripcion: e.description || '',
            boletosDisponibles: e.quantity || 100,
            precio: parseFloat(e.price) || 0,
            location: e.place || '',
            categoryColor: '#3498db',
            image: e.image || '',
            links: e.links || []
          }));
          this.updateSelectedDayEvents();
        })
        .catch(error => {
          console.error('Error obteniendo eventos desde la base de datos:', error);
        });
    }


    // FUNCIONES PARA ADMINISTRADOR

    // EDITAR EVENTO
    // ELIMINAR EVENTO
    // AGREGAR EVENTO


  },
  mounted() {
    document.title = 'Calendario';
    // this.getCalendarScrap(); // usar para cuando vayas a pasar el scrap a la base de datos
    this.getCalendarFromDB();

    this.user = JSON.parse(sessionStorage.getItem('user'));
    console.log("🚀 ~ mounted ~ this.user:", this.user)
    // No seleccionar ningún día al cargar la vista
    this.selectedDay = null;
  },
};
</script>




<style scoped>
@import '../../../scss/Calendario/calendario_navbar.scss';
@import '../../../scss/Calendario/calendario.scss';
</style>