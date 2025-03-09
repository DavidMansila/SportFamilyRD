<template>
  <div class="tienda-page">
    <!-- Nav Bar -->
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

    <!-- Título de la tienda -->
    <h2 class="tienda-title">Bienvenido a nuestra Tienda</h2>

    <!-- Barra de búsqueda y Filtros -->
    <div class="filtros-container">
      <div class="search-bar">
        <input
          type="text"
          v-model="busqueda"
          placeholder="Buscar productos..."
          @input="filtrarPorBusqueda"
          class="search-input"
        />
      </div>
      <div class="filters">
        <select v-model="categoriaSeleccionada" class="filter-select" @change="filtrarProductos">
          <option value="">Todas las categorías</option>
          <optgroup label="Deportes">
            <option value="futbol">Fútbol</option>
            <option value="basketball">Baloncesto</option>
            <option value="tenis">Tenis</option>
          </optgroup>
          <optgroup label="Ropa">
            <option value="ropa-hombre">Ropa Hombre</option>
            <option value="ropa-mujer">Ropa Mujer</option>
            <option value="ropa-ninos">Ropa Niños</option>
          </optgroup>
          <optgroup label="Consumibles">
            <option value="proteinas">Proteínas</option>
            <option value="barras">Barras energéticas</option>
          </optgroup>
          <optgroup label="Accesorios">
            <option value="accesorios">Accesorios</option>
          </optgroup>
        </select>
      </div>
    </div>

    <!-- Productos -->
    <div class="productos-container">
      <div
        v-for="producto in productosFiltrados"
        :key="producto.id"
        class="producto-card"
        @click="abrirPopup(producto)"
      >
        <div class="producto-imagen-container">
          <img :src="producto.imagen" alt="Imagen del producto" class="producto-imagen" />
        </div>
        <div class="producto-info">
          <h3 class="producto-nombre">{{ producto.nombre }}</h3>
          <p class="producto-precio">{{ producto.precio }}</p>
        </div>
      </div>
    </div>

    <!-- Pop-up de detalles del producto -->
    <div v-if="popupVisible" class="popup-overlay" @click="cerrarPopup">
      <div class="popup-content" @click.stop>
        <button class="btn-cerrar" @click="cerrarPopup">×</button>
        <img :src="productoSeleccionado.imagen" alt="Imagen del producto" class="popup-imagen" />
        <div class="popup-info">
          <h3 class="popup-nombre">{{ productoSeleccionado.nombre }}</h3>
          <p class="popup-descripcion">{{ productoSeleccionado.descripcion }}</p>
          <p class="popup-precio">{{ productoSeleccionado.precio }}</p>
          <button @click="agregarAlCarrito(productoSeleccionado)" class="btn-comprar">
            Agregar al carrito
          </button>
        </div>
      </div>
    </div>

    <!-- Carrito de Compras -->
    <div v-if="carrito.length > 0" class="carrito">
      <button @click="abrirCarrito" class="btn-carrito">
        Ver carrito ({{ carrito.length }})
      </button>
    </div>

    <!-- Pop-up del carrito -->
    <div v-if="carritoVisible" class="popup-overlay" @click="cerrarCarrito">
      <div class="popup-content carrito-popup" @click.stop>
        <button class="btn-cerrar" @click="cerrarCarrito">×</button>
        <h2 class="carrito-titulo">Carrito de Compras</h2>
        <div class="carrito-productos">
          <div v-for="(item, index) in carrito" :key="index" class="carrito-item">
            <img :src="item.imagen" alt="Imagen del producto" class="carrito-imagen" />
            <div class="carrito-info">
              <h3 class="carrito-nombre">{{ item.nombre }}</h3>
              <p class="carrito-precio">{{ item.precio }}</p>
            </div>
            <button @click="eliminarDelCarrito(index)" class="btn-eliminar">Eliminar</button>
          </div>
        </div>
        <div class="carrito-total">
          <p>Total: ${{ calcularTotal }}</p>
        </div>
        <button @click="finalizarCompra" class="btn-finalizar">Finalizar Compra</button>
      </div>
    </div>

    <!-- Notificación de agregado al carrito -->
    <div v-if="productoAgregado" class="notificacion">
      <p>{{ productoAgregado.nombre }} agregado al carrito</p>
    </div>
  </div>
</template>




<script>
export default {
  name: 'TiendaComponent',
  data() {
    return {
      productos: [
        {
          id: 1,
          nombre: 'Balón de Fútbol Adidas',
          precio: '30',
          categoria: 'futbol',
          imagen: '/imagenes/balon-futbol.jpg',
          descripcion: 'Balón oficial de la liga profesional, tamaño 5.',
        },
        {
          id: 2,
          nombre: 'Raqueta de Tenis Wilson',
          precio: '120',
          categoria: 'tenis',
          imagen: '/imagenes/raqueta-tenis.jpg',
          descripcion: 'Raqueta profesional para tenistas avanzados.',
        },
        {
          id: 3,
          nombre: 'Zapatillas Nike Running',
          precio: '90',
          categoria: 'ropa-hombre',
          imagen: '/imagenes/zapatillas-running.jpg',
          descripcion: 'Zapatillas cómodas y duraderas para running.',
        },
        {
          id: 4,
          nombre: 'Camiseta Deportiva Mujer',
          precio: '25',
          categoria: 'ropa-mujer',
          imagen: '/imagenes/camiseta-mujer.jpg',
          descripcion: 'Camiseta transpirable para actividades deportivas.',
        },
        {
          id: 5,
          nombre: 'Proteína en Polvo Whey',
          precio: '45',
          categoria: 'proteinas',
          imagen: '/imagenes/proteina-whey.jpg',
          descripcion: 'Proteína de suero para recuperación muscular.',
        },
        {
          id: 6,
          nombre: 'Barras Energéticas Power',
          precio: '15',
          categoria: 'barras',
          imagen: '/imagenes/barras-energeticas.jpg',
          descripcion: 'Barras energéticas para un impulso rápido.',
        },
        {
          id: 7,
          nombre: 'Balón de Basketball Spalding',
          precio: '40',
          categoria: 'basketball',
          imagen: '/imagenes/balon-basketball.jpg',
          descripcion: 'Balón oficial de la NBA, tamaño 7.',
        },
        {
          id: 8,
          nombre: 'Camiseta Deportiva Hombre',
          precio: '35',
          categoria: 'ropa-hombre',
          imagen: '/imagenes/camiseta-hombre.jpg',
          descripcion: 'Camiseta deportiva para hombres.',
        },
        {
          id: 9,
          nombre: 'Camiseta Deportiva Niño',
          precio: '20',
          categoria: 'ropa-ninos',
          imagen: '/imagenes/camiseta-nino.jpg',
          descripcion: 'Camiseta deportiva para niños.',
        },
        {
          id: 10,
          nombre: 'Gorra Deportiva Nike',
          precio: '20',
          categoria: 'accesorios',
          imagen: '/imagenes/gorra-deportiva.jpg',
          descripcion: 'Gorra deportiva ajustable.',
        },
      ],
      carrito: [],
      categoriaSeleccionada: '',
      busqueda: '',
      productoAgregado: null,
      productosFiltrados: [],
      popupVisible: false,
      productoSeleccionado: null,
      carritoVisible: false,
    };
  },
  created() {
    this.productosFiltrados = this.productos;
  },
  computed: {
    calcularTotal() {
      return this.carrito.reduce((total, producto) => {
        return total + parseFloat(producto.precio);
      }, 0).toFixed(2);
    },
  },
  methods: {
    agregarAlCarrito(producto) {
      this.carrito.push(producto);
      this.productoAgregado = producto;
      setTimeout(() => {
        this.productoAgregado = null;
      }, 3000);
    },
    eliminarDelCarrito(index) {
      this.carrito.splice(index, 1);
    },
    abrirCarrito() {
      this.carritoVisible = true;
    },
    cerrarCarrito() {
      this.carritoVisible = false;
    },
    finalizarCompra() {
      alert('Compra finalizada. Total: $' + this.calcularTotal);
      this.carrito = [];
      this.carritoVisible = false;
    },
    abrirPopup(producto) {
      this.productoSeleccionado = producto;
      this.popupVisible = true;
    },
    cerrarPopup() {
      this.popupVisible = false;
      this.productoSeleccionado = null;
    },
    filtrarProductos() {
      let productosFiltrados = this.productos;

      if (this.categoriaSeleccionada) {
        productosFiltrados = productosFiltrados.filter(
          (producto) => producto.categoria === this.categoriaSeleccionada
        );
      }

      if (this.busqueda) {
        productosFiltrados = productosFiltrados.filter((producto) =>
          producto.nombre.toLowerCase().includes(this.busqueda.toLowerCase())
        );
      }

      this.productosFiltrados = productosFiltrados;
    },
    filtrarPorBusqueda() {
      this.filtrarProductos();
    },
  },
};
</script>



<style scoped>
/* Estilos generales */
body {
  font-family: Arial, sans-serif;
  margin: 0;
  padding: 0;
  background-color: #f9f9f9;
}

/* Navbar */
.navbar {
  background: linear-gradient(to right, #000000, #17a2b8);
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
  align-items: center;
  gap: 1rem;
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

/* Título de la tienda */
.tienda-title {
  text-align: center;
  margin-top: 2rem;
  font-size: 2.5rem;
  color: #333;
}

/* Filtros y barra de búsqueda */
.filtros-container {
  display: flex;
  justify-content: center;
  gap: 2rem;
  margin-top: 2rem;
  padding: 1rem;
  background-color: #fff;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  border-radius: 10px;
  max-width: 1200px;
  margin: 2rem auto;
}

.search-bar {
  flex: 1;
}

.search-input {
  width: 100%;
  padding: 0.75rem;
  font-size: 1rem;
  border: 1px solid #ccc;
  border-radius: 5px;
  outline: none;
}

.filters {
  flex: 1;
}

.filter-select {
  width: 100%;
  padding: 0.75rem;
  font-size: 1rem;
  border: 1px solid #ccc;
  border-radius: 5px;
  outline: none;
  cursor: pointer;
}

/* Productos */
.productos-container {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 2rem;
  padding: 2rem;
  max-width: 1200px;
  margin: 0 auto;
}

.producto-card {
  background-color: #fff;
  border-radius: 10px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  overflow: hidden;
  transition: transform 0.3s ease-in-out;
  cursor: pointer;
}

.producto-card:hover {
  transform: translateY(-5px);
}

.producto-imagen-container {
  position: relative;
  overflow: hidden;
}

.producto-imagen {
  width: 100%;
  height: 200px;
  object-fit: cover;
  transition: transform 0.3s ease-in-out;
}

.producto-card:hover .producto-imagen {
  transform: scale(1.1);
}

.producto-info {
  padding: 1rem;
  text-align: center;
}

.producto-nombre {
  font-size: 1.25rem;
  color: #333;
  margin-bottom: 0.5rem;
}

.producto-precio {
  font-size: 1.1rem;
  color: #17a2b8;
  margin-bottom: 1rem;
}

/* Pop-up de detalles del producto */
.popup-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0, 0, 0, 0.7);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.popup-content {
  background-color: #fff;
  border-radius: 10px;
  padding: 2rem;
  max-width: 600px;
  width: 90%;
  position: relative;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.btn-cerrar {
  position: absolute;
  top: 1rem;
  right: 1rem;
  background: none;
  border: none;
  font-size: 1.5rem;
  cursor: pointer;
  color: #333;
}

.popup-imagen {
  width: 100%;
  max-height: 300px;
  object-fit: cover;
  border-radius: 10px;
  margin-bottom: 1rem;
}

.popup-info {
  text-align: center;
}

.popup-nombre {
  font-size: 1.5rem;
  color: #333;
  margin-bottom: 1rem;
}

.popup-descripcion {
  font-size: 1rem;
  color: #666;
  margin-bottom: 1rem;
}

.popup-precio {
  font-size: 1.25rem;
  color: #17a2b8;
  margin-bottom: 1.5rem;
}

.btn-comprar {
  padding: 0.75rem 1.5rem;
  background-color: #17a2b8;
  color: white;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  font-size: 1rem;
  transition: background-color 0.3s ease-in-out;
}

.btn-comprar:hover {
  background-color: #138496;
}

/* Carrito */
.carrito {
  position: fixed;
  bottom: 2rem;
  right: 2rem;
}

.btn-carrito {
  padding: 0.75rem 1.5rem;
  background-color: #17a2b8;
  color: white;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  font-size: 1rem;
  transition: background-color 0.3s ease-in-out;
}

.btn-carrito:hover {
  background-color: #138496;
}

/* Pop-up del carrito */
.carrito-popup {
  max-width: 500px;
}

.carrito-titulo {
  font-size: 1.5rem;
  color: #333;
  margin-bottom: 1.5rem;
  text-align: center;
}

.carrito-productos {
  max-height: 300px;
  overflow-y: auto;
  margin-bottom: 1.5rem;
}

.carrito-item {
  display: flex;
  align-items: center;
  gap: 1rem;
  margin-bottom: 1rem;
}

.carrito-imagen {
  width: 60px;
  height: 60px;
  object-fit: cover;
  border-radius: 10px;
}

.carrito-info {
  flex: 1;
}

.carrito-nombre {
  font-size: 1rem;
  color: #333;
  margin-bottom: 0.25rem;
}

.carrito-precio {
  font-size: 0.9rem;
  color: #17a2b8;
}

.btn-eliminar {
  background-color: #ff4d4d;
  color: white;
  border: none;
  padding: 0.5rem 1rem;
  border-radius: 5px;
  cursor: pointer;
  transition: background-color 0.3s ease-in-out;
}

.btn-eliminar:hover {
  background-color: #cc0000;
}

.carrito-total {
  text-align: right;
  font-size: 1.25rem;
  color: #333;
  margin-bottom: 1.5rem;
}

.btn-finalizar {
  width: 100%;
  padding: 0.75rem;
  background-color: #17a2b8;
  color: white;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  font-size: 1rem;
  transition: background-color 0.3s ease-in-out;
}

.btn-finalizar:hover {
  background-color: #138496;
}

/* Notificación */
.notificacion {
  position: fixed;
  bottom: 2rem;
  left: 50%;
  transform: translateX(-50%);
  background-color: #333;
  color: white;
  padding: 1rem 2rem;
  border-radius: 5px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  animation: fadeInOut 3s ease-in-out;
}

@keyframes fadeInOut {
  0%,
  100% {
    opacity: 0;
  }
  10%,
  90% {
    opacity: 1;
  }
}
</style>