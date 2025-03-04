<template>
  <div class="calendario-page">
    <h1 class="page-title">Calendario de Eventos Deportivos</h1>

    <!-- Muestra el mes y los botones para navegar entre meses -->
    <div class="calendar-header">
      <button @click="changeMonth('prev')">Anterior</button>
      <h2>{{ monthNames[currentMonth] }} {{ currentYear }}</h2>
      <button @click="changeMonth('next')">Siguiente</button>
    </div>

    <!-- Cuadrícula de días del mes -->
    <div class="calendar-grid">
      <div v-for="(day, index) in daysOfWeek" :key="index" class="calendar-day-name">
        {{ day }}
      </div>
      
      <div 
        v-for="day in daysInMonth" 
        :key="day" 
        :class="['calendar-day', { 'event-day': hasEvents(day) }]" 
        @click="selectDay(day)"
      >
        {{ day }}
      </div>
    </div>

    <!-- Modal para ver los eventos de un día -->
    <div v-if="selectedDayEvents.length" class="event-modal">
      <div class="modal-content">
        <h3>Eventos del día {{ selectedDay }}</h3>
        <ul>
          <li v-for="evento in selectedDayEvents" :key="evento.id">
            <h4>{{ evento.nombre }}</h4>
            <p>{{ evento.descripcion }}</p>
          </li>
        </ul>
        <button @click="closeModal">Cerrar</button>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      currentMonth: new Date().getMonth(), // Mes actual (0 - 11)
      currentYear: new Date().getFullYear(), // Año actual
      selectedDay: null, // Día seleccionado
      selectedDayEvents: [], // Eventos del día seleccionado
      eventos: [
            { id: 1, nombre: 'Torneo de Fútbol', fecha: '2025-03-15', descripcion: 'Un torneo de fútbol local.' },
            { id: 2, nombre: 'Maratón', fecha: '2025-03-25', descripcion: 'Una maratón de 10k.' },
            { id: 3, nombre: 'Conferencia Deportiva', fecha: '2025-03-15', descripcion: 'Una conferencia sobre el futuro del deporte.' },
            { id: 4, nombre: 'Partido de Tenis', fecha: '2025-04-18', descripcion: 'Partido amistoso de tenis entre dos equipos.' },
            { id: 5, nombre: 'Torneo de Baloncesto', fecha: '2025-04-22', descripcion: 'Torneo local de baloncesto con equipos regionales.' },
            { id: 6, nombre: 'Clínica de Natación', fecha: '2025-05-05', descripcion: 'Clínica intensiva para nadadores principiantes.' },
            { id: 7, nombre: 'Competencia de Skateboarding', fecha: '2025-05-10', descripcion: 'Competencia de skateboarding en el parque central.' },
            { id: 8, nombre: 'Curso de Primeros Auxilios Deportivos', fecha: '2025-06-01', descripcion: 'Curso para entrenadores y deportistas sobre primeros auxilios.' },
            { id: 9, nombre: 'Exhibición de Artes Marciales', fecha: '2025-06-20', descripcion: 'Exhibición de varias disciplinas de artes marciales.' },
            { id: 10, nombre: 'Torneo de Volleyball', fecha: '2025-07-05', descripcion: 'Torneo de volleyball en la playa con equipos locales.' },
            { id: 11, nombre: 'Desafío de Ciclismo', fecha: '2025-07-15', descripcion: 'Desafío de ciclismo de montaña con recorrido por rutas difíciles.' }
                  ],
      monthNames: [
        'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 
        'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
      ],
      daysOfWeek: ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'],
    };
  },
  computed: {
    // Calcula los días del mes
    daysInMonth() {
      const daysInMonth = new Date(this.currentYear, this.currentMonth + 1, 0).getDate();
      const firstDay = new Date(this.currentYear, this.currentMonth, 1).getDay();
      
      let days = [];
      // Añadir espacios en blanco antes del primer día del mes
      for (let i = 0; i < firstDay; i++) {
        days.push(null);
      }
      // Añadir los días del mes
      for (let i = 1; i <= daysInMonth; i++) {
        days.push(i);
      }
      return days;
    }
  },
  methods: {
    // Cambia el mes (siguiente o anterior)
    changeMonth(direction) {
      if (direction === 'prev') {
        if (this.currentMonth === 0) {
          this.currentMonth = 11;
          this.currentYear--;
        } else {
          this.currentMonth--;
        }
      } else if (direction === 'next') {
        if (this.currentMonth === 11) {
          this.currentMonth = 0;
          this.currentYear++;
        } else {
          this.currentMonth++;
        }
      }
    },

    // Selecciona un día y muestra sus eventos
    selectDay(day) {
      if (day) {
        this.selectedDay = day;
        this.selectedDayEvents = this.eventos.filter(evento => {
          const eventDate = new Date(evento.fecha);
          return eventDate.getDate() === day && eventDate.getMonth() === this.currentMonth;
        });
      }
    },

    // Cierra el modal de eventos
    closeModal() {
      this.selectedDay = null;
      this.selectedDayEvents = [];
    },

    // Verifica si un día tiene eventos
    hasEvents(day) {
      return this.eventos.some(evento => {
        const eventDate = new Date(evento.fecha);
        return eventDate.getDate() === day && eventDate.getMonth() === this.currentMonth;
      });
    }
  }
};
</script>

<style scoped>

h1 {
  font-size: 2.5rem;
  margin-bottom: 20px;
  color: black;
}

.calendario-page {
  max-width: 1000px;
  margin: 0 auto;
  padding: 20px;
}

.page-title {
  font-size: 2.5rem;
  margin-bottom: 20px;
  text-align: center;
}

.calendar-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.calendar-grid {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  gap: 10px;
  text-align: center;
}

.calendar-day-name {
  font-weight: bold;
}

.calendar-day {
  padding: 10px;
  border-radius: 5px;
  cursor: pointer;
  background: #f0f0f0;
  transition: background 0.3s;
}

.calendar-day:hover {
  background: #d0d0d0;
}

.event-day {
  background-color: #007bff;
  color: white;
}

.event-modal {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
}

.modal-content {
  background-color: white;
  padding: 20px;
  border-radius: 10px;
  width: 90%;
  max-width: 600px;
}

button {
  margin-top: 20px;
  padding: 10px 20px;
  background-color: #007bff;
  color: white;
  border: none;
  border-radius: 5px;
  cursor: pointer;
}

button:hover {
  background-color: #0056b3;
}
</style>
