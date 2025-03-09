<template>
  <div>
    <!-- Navbar -->
    <nav class="navbar">
      <div class="logo-container">
        <a href="/" class="logo-container">
          <img src="/imagenes/logo.png" alt="SportFamilyRD Logo" class="logo" />
        </a>
        <h1>SportFamilyRD</h1>
      </div>
      <div class="nav-links">
        <a href="/Noticias" class="nav-link">Noticias</a>
        <a href="/Calendario" class="nav-link">Calendario</a>
        <a href="/Tienda" class="nav-link">Tienda</a>
        <a href="/Entrenadores" class="nav-link">Entrenadores</a>
        <a href="/Foro" class="nav-link">Foro</a>
      </div>
      <div class="auth-buttons">
        <a href="/Ajustes">
          <button class="auth-btn">Ajustes</button>
        </a>
        <a href="/Login">
          <button class="auth-btn">Login</button>
        </a>
      </div>
    </nav>

    <!-- Calendario de eventos -->
    <div class="calendario-page">
    
      <h2 class="page-title">Calendario de Eventos Deportivos</h2>

      <!-- Muestra el mes y los botones para navegar entre meses -->
      <div class="calendar-header">
        <button @click="changeMonth('prev')" class="calendar-btn">Anterior</button>
        <h2>{{ monthNames[currentMonth] }} {{ currentYear }}</h2>
        <button @click="changeMonth('next')" class="calendar-btn">Siguiente</button>
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
        <p>Boletos disponibles: {{ evento.boletosDisponibles }}</p>
        <button @click="comprarBoleto(evento)" :disabled="evento.boletosDisponibles === 0">
          Comprar boleto
        </button>
      </li>
    </ul>
    <button @click="closeModal" class="close-btn">Cerrar</button>
  </div>
</div>

<!-- Sección de eventos ordenados -->
<div class="eventos-ordenados">
  <h2>Próximos Eventos</h2>
  <ul>
    <li v-for="evento in eventosOrdenados" :key="evento.id" class="evento-item">
      <div class="evento-fecha">{{ formatDate(evento.fecha) }}</div>
      <div class="evento-info">
        <h3>{{ evento.nombre }}</h3>
        <p>{{ evento.descripcion }}</p>
        <p>Boletos disponibles: {{ evento.boletosDisponibles }}</p>
        <button @click="comprarBoleto(evento)" :disabled="evento.boletosDisponibles === 0">
          Comprar boleto
        </button>
      </div>
    </li>
  </ul>
</div>

    <!-- Botón flotante del carrito -->
    <button class="carrito-btn" @click="mostrarCarrito = !mostrarCarrito">
      🛒 Carrito ({{ carrito.length }})
    </button>

    <!-- Popup del carrito -->
    <div v-if="mostrarCarrito" class="carrito-popup">
      <div class="carrito-contenido">
        <h2>Carrito de Compras</h2>
        <ul>
          <li v-for="(item, index) in carrito" :key="item.id" class="carrito-item">
            <div class="carrito-info">
              <h3>{{ item.nombre }}</h3>
              <p>Cantidad: {{ item.cantidad }}</p>
              <p>Precio: ${{ item.precio * item.cantidad }}</p>
            </div>
            <button @click="eliminarDelCarrito(index)" class="eliminar-btn">Eliminar</button>
          </li>
        </ul>
        <div class="total">
          <h3>Total: ${{ calcularTotal }}</h3>
          <button @click="finalizarCompra" class="comprar-btn">Finalizar Compra</button>
        </div>
        <button @click="mostrarCarrito = false" class="cerrar-btn">Cerrar</button>
      </div>
    </div>

    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      currentMonth: new Date().getMonth(),
      currentYear: new Date().getFullYear(),
      selectedDay: null,
      selectedDayEvents: [],
      eventos: [
        { id: 1, nombre: 'Torneo de Fútbol', fecha: '2025-03-15', descripcion: 'Un torneo de fútbol local.', boletosDisponibles: 100, precio: 50 },
        { id: 2, nombre: 'Maratón', fecha: '2025-03-25', descripcion: 'Una maratón de 10k.', boletosDisponibles: 200, precio: 30 },
        { id: 3, nombre: 'Conferencia Deportiva', fecha: '2025-03-15', descripcion: 'Una conferencia sobre el futuro del deporte.', boletosDisponibles: 50, precio: 20 },
        { id: 4, nombre: 'Partido de Tenis', fecha: '2025-04-18', descripcion: 'Partido amistoso de tenis entre dos equipos.', boletosDisponibles: 75, precio: 40 },
        { id: 5, nombre: 'Torneo de Baloncesto', fecha: '2025-04-22', descripcion: 'Torneo local de baloncesto con equipos regionales.', boletosDisponibles: 120, precio: 25 },
        { id: 6, nombre: 'Clínica de Natación', fecha: '2025-05-05', descripcion: 'Clínica intensiva para nadadores principiantes.', boletosDisponibles: 60, precio: 35 },
        { id: 7, nombre: 'Competencia de Skateboarding', fecha: '2025-05-10', descripcion: 'Competencia de skateboarding en el parque central.', boletosDisponibles: 80, precio: 15 },
        { id: 8, nombre: 'Curso de Primeros Auxilios Deportivos', fecha: '2025-06-01', descripcion: 'Curso para entrenadores y deportistas sobre primeros auxilios.', boletosDisponibles: 90, precio: 10 },
        { id: 9, nombre: 'Exhibición de Artes Marciales', fecha: '2025-06-20', descripcion: 'Exhibición de varias disciplinas de artes marciales.', boletosDisponibles: 110, precio: 20 },
        { id: 10, nombre: 'Torneo de Volleyball', fecha: '2025-07-05', descripcion: 'Torneo de volleyball en la playa con equipos locales.', boletosDisponibles: 130, precio: 30 },
        { id: 11, nombre: 'Desafío de Ciclismo', fecha: '2025-07-15', descripcion: 'Desafío de ciclismo de montaña con recorrido por rutas difíciles.', boletosDisponibles: 150, precio: 50 }
      ],
      monthNames: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
      daysOfWeek: ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'],
      carrito: [], // Carrito de compras
      mostrarCarrito: false, // Controla la visibilidad del popup del carrito
    };
  },
  computed: {
    daysInMonth() {
      const daysInMonth = new Date(this.currentYear, this.currentMonth + 1, 0).getDate();
      const firstDay = new Date(this.currentYear, this.currentMonth, 1).getDay();
      let days = [];
      for (let i = 0; i < firstDay; i++) {
        days.push(null);
      }
      for (let i = 1; i <= daysInMonth; i++) {
        days.push(i);
      }
      return days;
    },
    eventosOrdenados() {
      return this.eventos.slice().sort((a, b) => new Date(a.fecha) - new Date(b.fecha));
    },
    calcularTotal() {
      return this.carrito.reduce((total, item) => total + item.precio * item.cantidad, 0);
    }
  },
  methods: {
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
    selectDay(day) {
      if (day) {
        this.selectedDay = day;
        this.selectedDayEvents = this.eventos.filter(evento => {
          const eventDate = new Date(evento.fecha);
          return eventDate.getDate() === day && eventDate.getMonth() === this.currentMonth;
        });
      }
    },
    closeModal() {
      this.selectedDay = null;
      this.selectedDayEvents = [];
    },
    hasEvents(day) {
      return this.eventos.some(evento => {
        const eventDate = new Date(evento.fecha);
        return eventDate.getDate() === day && eventDate.getMonth() === this.currentMonth;
      });
    },
    formatDate(dateString) {
      const date = new Date(dateString);
      return date.toLocaleDateString('es-ES', { day: 'numeric', month: 'long', year: 'numeric' });
    },
    comprarBoleto(evento) {
    const itemEnCarrito = this.carrito.find(item => item.id === evento.id);
    if (itemEnCarrito) {
      itemEnCarrito.cantidad++;
    } else {
      this.carrito.push({ ...evento, cantidad: 1 });
    }
    evento.boletosDisponibles--;
    console.log('Carrito:', this.carrito); // Depuración
  },
  eliminarDelCarrito(index) {
    const item = this.carrito[index];
    item.cantidad--;
    if (item.cantidad === 0) {
      this.carrito.splice(index, 1);
    }
    const evento = this.eventos.find(e => e.id === item.id);
    evento.boletosDisponibles++;
    console.log('Carrito después de eliminar:', this.carrito); // Depuración
  },
  finalizarCompra() {
    alert('Compra finalizada. Gracias por su compra.');
    this.carrito = [];
    console.log('Carrito después de finalizar compra:', this.carrito); // Depuración
  }
  }
};
</script>

<style scoped>

/* Estilos Navbar */
body {
  font-family: Arial, sans-serif;
  margin: 0;
  padding: 0;
  background-color: #f9f9f9;
}

/* Navbar */
.navbar {
  background: linear-gradient(135deg, #000000, #15ff54);
  padding: 1rem 2rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.logo {
  width: 50px;
  height: 50px;
}

.logo-container {
    display: flex;
    gap: 1rem;
    flex-direction: row;

    h1 {
    font-size: 2rem;
    font-weight: bold;
    color: rgb(255, 255, 255);
  }
  }

.nav-links {
  display: flex;
  gap: 2rem;
}

.nav-link {
  color: white;
  text-decoration: none;
  font-size: 1.2rem;
  font-weight: bold;
  transition: color 0.3s ease-in-out;
}

.nav-link:hover {
  color: #fbbf24;
}

.auth-buttons {
  display: flex;
  gap: 1rem;
}

.auth-btn {
  background: transparent;
  border: 2px solid white;
  color: white;
  padding: 0.5rem 1.2rem;
  font-size: 1rem;
  font-weight: bold;
  border-radius: 0.5rem;
  cursor: pointer;
  transition: all 0.3s ease-in-out;
}

.auth-btn:hover {
  background-color: white;
  color: #ff3149;
}
  
.page-title {
  font-size: 2.5rem;
  margin-bottom: 20px;
  text-align: center;
  color: #333;
  font-weight: 600;
  padding-top: 30px;
  padding-top: 30px;
}

.calendar-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 15px;
  padding-left: 110px;
  padding-right: 110px;
}

.calendar-btn {
  font-size: 1rem;
  padding: 8px 15px;
  border-radius: 5px;
  background-color: #17A2B8;
  color: white;
  border: none;
  transition: background 0.3s, transform 0.3s;
}

.calendar-btn:hover {
  background-color: #007bff;
  transform: translateY(-2px);
}

.calendar-grid {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  gap: 5px;
  text-align: center;
  padding-left: 100px;
  padding-right: 100px;
}

.calendar-day-name {
  font-weight: bold;
  font-size: 1rem;
  color: #333;
}

.calendar-day {
  padding: 12px;
  border-radius: 8px;
  cursor: pointer;
  background: #f9f9f9;
  transition: background 0.3s, transform 0.3s;
}

.calendar-day:hover {
  background: #d0d0d0;
  transform: translateY(-2px);
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
  background-color: rgba(0, 0, 0, 0.6);
  display: flex;
  justify-content: center;
  align-items: center;
  animation: fadeIn 0.5s ease;
}

.modal-content {
  background-color: white;
  padding: 20px;
  border-radius: 15px;
  width: 80%;
  max-width: 600px;
  box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2);
  animation: slideIn 0.5s ease;
}

button {
  background-color: #007bff;
  color: white;
  padding: 10px 15px;
  border: none;
  border-radius: 5px;
  font-size: 1rem;
  cursor: pointer;
  transition: background 0.3s;
}

button:hover {
  background-color: #0056b3;
}

.close-btn {
  background-color: #d9534f;
}

@keyframes fadeIn {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}

@keyframes slideIn {
  from {
    transform: translateY(-50px);
  }
  to {
    transform: translateY(0);
  }
}

.eventos-ordenados {
  margin-top: 40px;
  padding: 20px;
  background-color: #ffffff;
  border-radius: 15px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.eventos-ordenados h2 {
  font-size: 2rem;
  color: #333;
  margin-bottom: 20px;
  text-align: center;
}

.evento-item {
  display: flex;
  align-items: center;
  padding: 15px;
  margin-bottom: 15px;
  background-color: #f9f9f9;
  border-radius: 10px;
  transition: transform 0.3s, box-shadow 0.3s;
}

.evento-item:hover {
  transform: translateY(-5px);
  box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
}

.evento-fecha {
  flex: 0 0 120px;
  font-size: 1.1rem;
  font-weight: bold;
  color: #007bff;
  margin-right: 20px;
}

.evento-info {
  flex: 1;
}

.evento-info h3 {
  font-size: 1.5rem;
  color: #333;
  margin-bottom: 10px;
}

.evento-info p {
  font-size: 1rem;
  color: #666;
  margin-bottom: 10px;
}

.evento-info button {
  background-color: #007bff;
  color: white;
  padding: 8px 15px;
  border: none;
  border-radius: 5px;
  font-size: 1rem;
  cursor: pointer;
  transition: background 0.3s;
}

.evento-info button:hover {
  background-color: #0056b3;
}

.evento-info button:disabled {
  background-color: #cccccc;
  cursor: not-allowed;
}


.carrito {
  margin-top: 40px;
  padding: 20px;
  background-color: #ffffff;
  border-radius: 15px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  display: block; /* Asegúrate de que no esté oculto */
}

.carrito h2 {
  font-size: 2rem;
  color: #333;
  margin-bottom: 20px;
  text-align: center;
}

.carrito-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 15px;
  margin-bottom: 15px;
  background-color: #f9f9f9;
  border-radius: 10px;
  transition: transform 0.3s, box-shadow 0.3s;
}

.carrito-item:hover {
  transform: translateY(-5px);
  box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
}

.carrito-info {
  flex: 1;
}

.carrito-info h3 {
  font-size: 1.5rem;
  color: #333;
  margin-bottom: 10px;
}

.carrito-info p {
  font-size: 1rem;
  color: #666;
  margin-bottom: 10px;
}

.eliminar-btn {
  background-color: #d9534f;
  color: white;
  padding: 8px 15px;
  border: none;
  border-radius: 5px;
  font-size: 1rem;
  cursor: pointer;
  transition: background 0.3s;
}

.eliminar-btn:hover {
  background-color: #c9302c;
}

.total {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 20px;
}

/* Botón flotante del carrito */
.carrito-btn {
  position: fixed;
  bottom: 20px;
  right: 20px;
  background-color: #007bff;
  color: white;
  padding: 10px 20px;
  border: none;
  border-radius: 50px;
  font-size: 1rem;
  cursor: pointer;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  z-index: 1000;
  transition: background 0.3s, transform 0.3s;
}

.carrito-btn:hover {
  background-color: #0056b3;
  transform: translateY(-2px);
}

/* Popup del carrito */
.carrito-popup {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0, 0, 0, 0.6);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1001;
  animation: fadeIn 0.3s ease;
}

.carrito-contenido {
  background-color: white;
  padding: 20px;
  border-radius: 15px;
  width: 90%;
  max-width: 500px;
  box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2);
  animation: slideIn 0.3s ease;
}

.carrito-contenido h2 {
  font-size: 2rem;
  color: #333;
  margin-bottom: 20px;
  text-align: center;
}

.carrito-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 15px;
  margin-bottom: 15px;
  background-color: #f9f9f9;
  border-radius: 10px;
  transition: transform 0.3s, box-shadow 0.3s;
}

.carrito-item:hover {
  transform: translateY(-5px);
  box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
}

.carrito-info {
  flex: 1;
}

.carrito-info h3 {
  font-size: 1.5rem;
  color: #333;
  margin-bottom: 10px;
}

.carrito-info p {
  font-size: 1rem;
  color: #666;
  margin-bottom: 10px;
}

.eliminar-btn {
  background-color: #d9534f;
  color: white;
  padding: 8px 15px;
  border: none;
  border-radius: 5px;
  font-size: 1rem;
  cursor: pointer;
  transition: background 0.3s;
}

.eliminar-btn:hover {
  background-color: #c9302c;
}

.total {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 20px;
}

.comprar-btn {
  background-color: #28a745;
  color: white;
  padding: 10px 20px;
  border: none;
  border-radius: 5px;
  font-size: 1rem;
  cursor: pointer;
  transition: background 0.3s;
}

.comprar-btn:hover {
  background-color: #218838;
}

.cerrar-btn {
  background-color: #d9534f;
  color: white;
  padding: 10px 20px;
  border: none;
  border-radius: 5px;
  font-size: 1rem;
  cursor: pointer;
  transition: background 0.3s;
  margin-top: 20px;
  width: 100%;
}

.cerrar-btn:hover {
  background-color: #c9302c;
}

@keyframes fadeIn {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}

@keyframes slideIn {
  from {
    transform: translateY(-20px);
  }
  to {
    transform: translateY(0);
  }
}


</style>
