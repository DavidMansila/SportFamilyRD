<template>
  <div>
          <!-- Navbar -->
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

            <a class="Carrito">
                <img src="/imagenes/Carrito-Icon.png" alt="Carrito" class="carrito-icon"/>
            </a>

            <a href= "/Ajustes" class="Ajustes">
                <img src="/imagenes/Ajustes-Icon.png" alt="Ajustes" class="ajustes-icon"/>
            </a>

            <a class="Perfil">
                <img src="/imagenes/Perfil-Icon.png" alt="Perfil" class="perfil-icon"/>
            </a>

            <a :href=" login ? '/Login' : '/Logout' " class="Logout">
                <img src="/imagenes/Logout-Icon.png" alt="Logout" class="logout-icon"/>
            </a>

        </div>
      </nav>

<!-- Calendario de eventos -->
    <div class="calendario-page">
      <!-- Calendario -->
      <div class="calendario-container">
        <div class="calendar-header">
          <button @click="changeMonth('prev')" class="calendar-btn">◄</button>
          <h1>{{ monthNames[currentMonth] }} {{ currentYear }}</h1>
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
  @import '../../../scss/Calendario/calendario.scss';
</style>