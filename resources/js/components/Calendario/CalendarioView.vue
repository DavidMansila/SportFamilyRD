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
            <div class="event-time">{{ formatTime(event.time) }}</div>
            <h3 class="event-title">{{ event.Title }}</h3>
            <div class="event-meta">
              <span class="event-location">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                  stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                  <circle cx="12" cy="10" r="3"></circle>
                </svg>
                {{ event.place || 'Ubicación no especificada' }}
              </span>
              <span class="event-price" v-if="event.price">${{ event.price }}</span>
              <span class="event-price free" v-else>Gratis</span>
            </div>
            <div v-if="user?.user_type === 'admin'" class="event-actions">

              <button class="btn-editar" @click.stop="openEventForm(event)">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                  stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                  <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                </svg>
              </button>

              <button class="btn-eliminar" @click.stop="deleteEvent(event.id)">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                  stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <polyline points="3 6 5 6 21 6"></polyline>
                  <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                </svg>
              </button>

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

    <transition name="fade">
      <div v-if="showEventSuccess" class="success-notification">
        <div class="notification-content">
          <i class="fas fa-check-circle"></i>
          {{ successEventMessage }}
        </div>
      </div>
    </transition>

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
            <div class="event-day">{{ new Date(selectedEvent.date).getDate() }}</div>
            <div class="event-month">{{ monthNames[new Date(selectedEvent.date).getMonth()].substring(0, 3) }}</div>
          </div>
          <div class="event-title-container">
            <h2>{{ selectedEvent.Title }}</h2>
            <div class="event-meta">
              <span class="event-time">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                  stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <circle cx="12" cy="12" r="10"></circle>
                  <polyline points="12 6 12 12 16 14"></polyline>
                </svg>
                {{ formatTime(selectedEvent.time) }}
              </span>
              <span class="event-location">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                  stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                  <circle cx="12" cy="10" r="3"></circle>
                </svg>
                {{ selectedEvent.place || 'Ubicación no especificada' }}
              </span>
            </div>
          </div>
        </div>

        <div class="event-content">
          <div class="event-description">
            <h3>Descripción</h3>
            <p> {{ selectedEvent.Description || 'No hay descripcion' }}</p>
          </div>

          <div class="event-tickets">
            <h3>Boletos</h3>
            <div class="ticket-info">
              <span class="ticket-price">${{ selectedEvent.price }} <small>c/u</small></span>
              <span class="ticket-available">{{ selectedEvent.quantity }} disponibles</span>
            </div>

            <div class="ticket-controls" v-if="selectedEvent.quantity > 0">
              <div class="quantity-selector">
                <button @click="decrementTicket" :disabled="ticketQuantity <= 1">-</button>
                <span>{{ ticketQuantity }}</span>
                <button @click="incrementTicket" :disabled="ticketQuantity >= selectedEvent.quantity">+</button>
              </div>
              <button class="add-to-cart-btn" @click="addToCart">
                Añadir al carrito - ${{ selectedEvent.price * ticketQuantity }}
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

    <!-- Botón flotante admin -->
    <button v-if="user?.user_type === 'admin'" @click="openEventForm" class="floating-admin-btn">
      <i class="fas fa-plus"></i>
    </button>

    <!-- Modal formulario evento -->
    <div class="admin-modal" v-if="showEventForm" @click.self="closeEventForm">
      <div class="admin-modal-content">
        <h2>{{ editingEvent ? 'Editar Evento' : 'Nuevo Evento' }}</h2>

        <form @submit.prevent="saveEvent">
          <div class="form-group">
            <input v-model="formEvent.Title" placeholder="Nombre del evento" required>
          </div>

          <div class="form-group">
            <label>Fecha</label>
            <input type="date" v-model="formEvent.date" required>
          </div>

          <div class="form-group time-group">
            <div>
              <label>Hora</label>
              <input type="time" v-model="formEvent.time" required>
            </div>
          </div>

          <div class="form-group">
            <textarea v-model="formEvent.Description" placeholder="Descripción"></textarea>
          </div>

          <div class="form-group">
            <input type="number" v-model="formEvent.price" placeholder="Precio" step="0.01" required>
          </div>

          <div class="form-group">
            <input type="number" v-model="formEvent.quantity" placeholder="Boletos disponibles" required>
          </div>

          <div class="form-group">
            <input v-model="formEvent.place" placeholder="Ubicación" required>
          </div>

          <div class="form-group">
            <input v-model="formEvent.image" placeholder="URL de imagen">
          </div>

          <div class="form-actions">
            <button type="button" @click="closeEventForm" class="btn-cancelar">Cancelar</button>
            <button type="submit" class="btn-guardar">{{ editingEvent ? 'Actualizar' : 'Crear' }}</button>
          </div>
        </form>
      </div>
    </div>



  </div>

  <!-- Burbuja de Mensajes Flotante -->
  <ChatBubbleComponent v-if="user && !selectedEvent" :user="user" />


</template>

<script>
import axios from 'axios';
import Navbar from '../navbarComponent.vue';
import ChatBubbleComponent from '../ChatBubbleComponent.vue';

export default {
  name: 'Calendario',
  components: {
    Navbar,
    ChatBubbleComponent
  },
  data() {

    return {
      currentMonth: new Date().getMonth(),
      currentYear: new Date().getFullYear(),
      selectedDay: null, // Cambiado de new Date().getDate() a null
      selectedDayEvents: [],
      selectedEvent: null,
      ticketQuantity: '',
      showCartPopup: false,
      calendarData: [],
      cartItems: [],
      user: null,
      monthNames: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
      daysOfWeek: ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'],
      eventos: [],
      scrapEvents: [],
      showEventSuccess: false,
      successEventMessage: '',
      successEventTimer: null,
      showEventForm: false,
      editingEvent: null,
      formEvent: this.resetEventForm(),
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


    openEventDetail(event) {
      this.selectedEvent = event;
      this.ticketQuantity = 1;
      document.body.classList.add('no-scroll');
    },

    closeEventDetail() {
      this.selectedEvent = null;
      document.body.classList.remove('no-scroll');
    },


    // Guarda los eventos scrappeados en la base de datos y actualiza scrapEvents con los IDs reales
    saveScrapEventsToDB(scrapRawEvents) {
      // Mapear los eventos al formato esperado por el backend
      const mappedEvents = scrapRawEvents.map(e => ({
        Title: e.title,
        date: this.parseScrapDate(e.date),
        time: e.hour ? this.parseScrapHour(e.hour) : '',
        endTime: '',
        Description: '',
        quantity: 100, // valor por defecto
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
          console.log('Eventos scrappeados guardados correctamente:', this.scrapEvents);
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
        const eventDate = new Date(evento.date);//cualquier cosa se le pone date
        return eventDate.getDate() === this.selectedDay &&
          eventDate.getMonth() === this.currentMonth &&
          eventDate.getFullYear() === this.currentYear;
      });
    },

    hasEvents(day) {
      return this.eventosToShow.some(evento => {
        const eventDate = new Date(evento.date);
        return eventDate.getDate() === day &&
          eventDate.getMonth() === this.currentMonth &&
          eventDate.getFullYear() === this.currentYear;
      });
    },

    getEventsForDay(day) {
      return this.eventosToShow.filter(evento => {
        const eventDate = new Date(evento.date);
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

    incrementTicket() {
      if (this.ticketQuantity < this.selectedEvent.quantity) {
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

        // Mostrar mensaje de éxito
        this.successEventMessage = `¡${this.selectedEvent.Title} agregado al carrito!`;
        this.showEventSuccess = true;

        // Ocultar después de 3 segundos
        clearTimeout(this.successEventTimer);
        this.successEventTimer = setTimeout(() => {
          this.showEventSuccess = false;
        }, 3000);

        window.dispatchEvent(new CustomEvent('cart-updated'));

        // Actualizar disponibilidad local
        const event = this.eventos.find(e => e.id === this.selectedEvent.id);
        if (event) {
          event.quantity -= this.ticketQuantity;
        }
        this.closeEventDetail();
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
        event.quantity += removedItem.quantity;
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
            Title: e.Title || e.place || 'Sin nombre',
            date: e.date,
            time: e.time || '',
            endTime: '',
            Description: e.Description || '',
            quantity: e.quantity || 100,
            price: parseFloat(e.price) || 0,
            place: e.place || '',
            categoryColor: '#3498db',
            image: e.image || '',
            links: e.links || []
          }));
          this.updateSelectedDayEvents();
        })
        .catch(error => {
          console.error('Error obteniendo eventos desde la base de datos:', error);
        });
    },

    // FUNCIONES PARA ADMINISTRADOR

    resetEventForm() {
      return {
        Title: '',
        date: new Date().toISOString().split('T')[0],
        time: '18:00',
        endTime: '20:00',
        Description: '',
        price: 0,
        quantity: 100,
        place: '',
        image: ''
      };
    },

    openEventForm(event = null) {
      if (event) {
        console.log("🚀 ~ openEventForm ~ event:", event)
        this.editingEvent = event.id;
        this.formEvent = { ...event };
      } else {
        this.editingEvent = null;
        this.formEvent = this.resetEventForm();
      }
      this.showEventForm = true;
    },

    closeEventForm() {
      this.showEventForm = false;
    },

    async saveEvent() {
      try {
        const config = {
          headers: {
            Authorization: `Bearer ${localStorage.getItem('token')}`
          }
        };

        if (this.editingEvent) {
          await axios.put(`/calendar/${this.editingEvent}`, this.formEvent, config);
        } else {
          await axios.post('/calendar', this.formEvent, config);
        }

        this.getCalendarFromDB();
        this.closeEventForm();
        this.showEventSuccess = true;
        this.successEventMessage = `Evento ${this.editingEvent ? 'actualizado' : 'creado'} correctamente`;

        setTimeout(() => {
          this.showEventSuccess = false;
        }, 3000);
      } catch (error) {
        console.error('Error guardando evento:', error);
        alert(error.response?.data?.message || 'Error al guardar');
      }
    },

    async deleteEvent(eventId) {
      if (confirm('¿Eliminar este evento permanentemente?')) {
        try {
          await axios.delete(`/calendar/${eventId}`, {
            headers: {
              Authorization: `Bearer ${localStorage.getItem('token')}`
            }
          });
          this.getCalendarFromDB();
        } catch (error) {
          console.error('Error eliminando evento:', error);
        }
      }
    },

  },
  mounted() {
    document.title = 'Calendario';
    //this.getCalendarScrap(); // usar para cuando vayas a pasar el scrap a la base de datos
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


/* Notificación de éxito */
.success-notification {
  position: fixed;
  bottom: 30px;
  right: 30px;
  background: #4CAF50;
  color: white;
  padding: 15px 25px;
  border-radius: 8px;
  box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
  z-index: 10000;
  display: flex;
  align-items: center;
  animation: slideIn 0.3s ease-out;
}

.notification-content {
  display: flex;
  align-items: center;
  gap: 10px;
}

.fa-check-circle {
  font-size: 1.5rem;
}

@keyframes slideIn {
  from {
    transform: translateX(100%);
    opacity: 0;
  }

  to {
    transform: translateX(0);
    opacity: 1;
  }
}

.fade-leave-active {
  transition: opacity 0.5s;
}

.fade-leave-to {
  opacity: 0;
}







/* Estilos para el botón flotante */
.floating-admin-btn {
  position: fixed;
  bottom: 30px;
  right: 30px;
  width: 60px;
  height: 60px;
  border-radius: 50%;
  background: #357e36;
  color: white;
  border: none;
  font-size: 24px;
  cursor: pointer;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
  z-index: 100;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s ease;
}

.floating-admin-btn:hover {
  background: #0f6110;
  transform: scale(1.1);
}

/* Estilos para el modal de admin */
.admin-modal {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.7);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.admin-modal-content {
  background: white;
  border-radius: 12px;
  padding: 30px;
  width: 90%;
  max-width: 600px;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
}

.admin-modal-content h2 {
  margin-top: 0;
  color: #2a4d69;
  text-align: center;
}

.form-group {
  margin-bottom: 20px;
}

.form-group label {
  display: block;
  margin-bottom: 8px;
  font-weight: 600;
  color: #34495e;
}

.form-group input,
.form-group textarea,
.form-group select {
  width: 100%;
  padding: 12px 15px;
  border: 1px solid #ddd;
  border-radius: 8px;
  font-size: 1rem;
}

.form-group textarea {
  min-height: 100px;
  resize: vertical;
}

.time-group {
  display: flex;
  gap: 15px;
}

.time-group>div {
  flex: 1;
}

.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 15px;
  margin-top: 20px;
}

.btn-cancelar {
  background: #e74c3c;
  color: white;
  border: none;
  padding: 12px 20px;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 600;
}

.btn-guardar {
  background: #2a4d69;
  color: white;
  border: none;
  padding: 12px 20px;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 600;
}

/* Botones admin en tarjetas de eventos */
.event-actions {
  display: flex;
  gap: 10px;
  margin-top: 10px;
}



/* Botones de edición en tarjetas */
.btn-editar,
.btn-eliminar {
  bottom: 20px;
  right: 20px;
  width: 36px;
  height: 36px;
  border-radius: 50%;
  background: #46696f;
  color: rgb(0, 0, 0);
  border: none;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  cursor: pointer;
  font-size: 1.8rem;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s ease;
}

.btn-editar {
  right: 50px;
  background: #aeffb2;
}

.btn-eliminar {
  right: 10px;
  background: #ffaeae;
}

.btn-editar:hover,
.btn-eliminar:hover {
  opacity: 1;
  transform: scale(1.1);
}

.btn-editar i {
  font-size: 14px;
}

.btn-eliminar i {
  font-size: 14px;
}

.btn-editar i::before {
  content: "\f304";
  font-family: "Font Awesome 5 Free";
  font-weight: 900;
}

.btn-eliminar i::before {
  content: "\f2ed";
  font-family: "Font Awesome 5 Free";
  font-weight: 900;
}

.floating-admin-btn i::before {
  content: "\2b";
  /* Icono de más */
  font-family: "Font Awesome 5 Free";
  font-weight: 900;
  font-size: 1.4rem;
  /* Un poco más grande porque es un botón flotante */
  padding: 4px;
}


.no-scroll {
  overflow: hidden;
}

</style>