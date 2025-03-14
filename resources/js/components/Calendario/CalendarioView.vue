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
      <!-- Calendario -->
      <div class="calendario-container">
        <div class="calendar-header">
          <button @click="changeMonth('prev')" class="calendar-btn">◄</button>
          <h2>{{ monthNames[currentMonth] }} {{ currentYear }}</h2>
          <button @click="changeMonth('next')" class="calendar-btn">►</button>
        </div>
        <div class="calendar-grid">
          <div v-for="(day, index) in daysOfWeek" :key="index" class="calendar-day-name">
            {{ day }}
          </div>
          <div 
            v-for="day in daysInMonth" 
            :key="day" 
            :class="['calendar-day', { 'event-day': hasEvents(day), 'selected-day': selectedDay === day }]" 
            @click="selectDay(day)"
          >
            {{ day }}
            <div v-if="hasEvents(day)" class="event-indicator"></div>
          </div>
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

/* Estilos generales */
body {
  font-family: 'Poppins', sans-serif;
  margin: 0;
  padding: 0;
  background-color: #f8f9fa;
  color: #000000;
}

/* Navbar */
.navbar {
  background: linear-gradient(135deg, #000000, #00b746);
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
  align-items: center;
}

.logo-container h1 {
  font-size: 2rem;
  font-weight: bold;
  color: white;
  margin: 0;
}

.nav-links {
  display: flex;
  gap: 2rem;
}

.nav-link {
  color: white;
  text-decoration: none;
  font-size: 1.1rem;
  font-weight: 500;
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
  color: #00b746; /* Color oscuro al pasar el mouse */
}

/* Contenedor del calendario */
.calendario-container {
  background-color: #ffffff; /* Fondo blanco */
  border-radius: 15px;
  box-shadow: 0 12px 24px rgba(0, 0, 0, 0.1); /* Sombra suave */
  padding: 20px;
  margin: 20px auto;
  max-width: 800px;
}

/* Encabezado del calendario */
.calendar-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
  background-color: #3498db; /* Azul claro */
  padding: 10px 20px;
  border-radius: 10px;
  color: white; /* Texto blanco */
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); /* Sombra sutil */
}

.calendar-header h2 {
  font-size: 1.8rem;
  font-weight: 600;
  margin: 0;
}

.calendar-btn {
  background-color: transparent;
  border: none;
  color: white; /* Texto blanco */
  font-size: 1.8rem;
  cursor: pointer;
  transition: transform 0.3s, color 0.3s;
}

.calendar-btn:hover {
  transform: scale(1.2);
  color: #2c3e50; /* Color oscuro al pasar el mouse */
}

/* Cuadrícula de días */
.calendar-grid {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  gap: 10px;
  text-align: center;
}

.calendar-day-name {
  font-weight: bold;
  font-size: 1rem;
  color: #3498db; /* Azul claro */
  padding: 10px;
  background-color: #f0f0f0; /* Fondo gris claro */
  border-radius: 8px;
  text-transform: uppercase; /* Mayúsculas para los nombres de los días */
}

.calendar-day {
  padding: 15px;
  border-radius: 8px;
  cursor: pointer;
  background: #ffffff; /* Fondo blanco */
  border: 1px solid #e0e0e0; /* Borde gris claro */
  transition: background 0.3s, transform 0.3s, box-shadow 0.3s;
  font-size: 1rem;
  color: #333; /* Texto oscuro */
  position: relative;
}

.calendar-day:hover {
  background: #3498db; /* Azul claro al pasar el mouse */
  color: white; /* Texto blanco al pasar el mouse */
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); /* Sombra al pasar el mouse */
}

.event-day {
  background-color: #3498db; /* Azul claro */
  color: white; /* Texto blanco */
  border: none;
}

.event-day:hover {
  background-color: #2980b9; /* Azul más oscuro al pasar el mouse */
}

.selected-day {
  background-color: #2c3e50; /* Fondo oscuro */
  color: white; /* Texto blanco */
  border: 2px solid #3498db; /* Borde azul claro */
  box-shadow: 0 4px 8px rgba(52, 152, 219, 0.3); /* Sombra azul */
}

.event-indicator {
  position: absolute;
  bottom: 5px;
  left: 50%;
  transform: translateX(-50%);
  width: 6px;
  height: 6px;
  background-color: white; /* Punto blanco */
  border-radius: 50%;
}

/* Estilos para días vacíos */
.calendar-day:empty {
  background: transparent;
  border: none;
  cursor: default;
}

/* Sección de eventos ordenados */
.eventos-ordenados {
  margin-top: 40px;
  padding: 20px;
  background-color: #ffffff;
  border-radius: 15px;
  box-shadow: 0 12px 24px rgba(0, 0, 0, 0.1); /* Sombra suave */
}

.eventos-ordenados h2 {
  font-size: 2rem;
  color: #2c3e50; /* Texto oscuro */
  margin-bottom: 20px;
  text-align: center;
  font-weight: 700; /* Texto más grueso */
}

.evento-item {
  display: flex;
  align-items: center;
  padding: 15px;
  margin-bottom: 15px;
  background-color: #ffffff;
  border: 1px solid #e0e0e0; /* Borde gris claro */
  border-radius: 10px;
  transition: transform 0.3s, box-shadow 0.3s;
}

.evento-item:hover {
  transform: translateY(-5px);
  box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1); /* Sombra al pasar el mouse */
}

.evento-fecha {
  flex: 0 0 120px;
  font-size: 1.1rem;
  font-weight: bold;
  color: #3498db; /* Azul claro */
  margin-right: 20px;
}

.evento-info {
  flex: 1;
}

.evento-info h3 {
  font-size: 1.5rem;
  color: #2c3e50; /* Texto oscuro */
  margin-bottom: 10px;
  font-weight: 600; /* Texto más grueso */
}

.evento-info p {
  font-size: 1rem;
  color: #666;
  margin-bottom: 10px;
}

.evento-info button {
  background-color: #3498db; /* Azul claro */
  color: white; /* Texto blanco */
  padding: 8px 15px;
  border: none;
  border-radius: 5px;
  font-size: 1rem;
  cursor: pointer;
  transition: background 0.3s, transform 0.3s;
}

.evento-info button:hover {
  background-color: #2980b9; /* Azul más oscuro al pasar el mouse */
  transform: translateY(-2px);
}

.evento-info button:disabled {
  background-color: #cccccc;
  cursor: not-allowed;
}

/* Botón flotante del carrito */
.carrito-btn {
  position: fixed;
  bottom: 20px;
  right: 20px;
  background-color: #3498db; /* Azul claro */
  color: white; /* Texto blanco */
  padding: 12px 24px;
  border: none;
  border-radius: 50px;
  font-size: 1rem;
  cursor: pointer;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  z-index: 1000;
  transition: background 0.3s, transform 0.3s;
}

.carrito-btn:hover {
  background-color: #2980b9; /* Azul más oscuro al pasar el mouse */
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
  box-shadow: 0 12px 24px rgba(0, 0, 0, 0.2); /* Sombra suave */
  animation: slideIn 0.3s ease;
}

.carrito-contenido h2 {
  font-size: 2rem;
  color: #2c3e50; /* Texto oscuro */
  margin-bottom: 20px;
  text-align: center;
  font-weight: 700; /* Texto más grueso */
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

/* Animaciones */
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