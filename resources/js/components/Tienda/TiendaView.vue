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

    <!-- Barra de búsqueda -->
    <div class="search-container">
      <input
        type="text"
        v-model="busqueda"
        placeholder="Buscar productos..."
        @input="filtrarPorBusqueda"
        class="search-input"
      />
    </div>

    <!-- Filtros de categorías -->
    <div class="filtros-container">
      <div class="categorias-horizontal">
        <div
          v-for="(categoria, index) in categorias"
          :key="index"
          class="categoria-item"
        >
          <div
            class="categoria-header"
            @click="toggleAcordeon(index)"
            :class="{ active: categoriaActiva === index }"
          >
            <h4>{{ categoria.nombre }}</h4>
            <i class="fas fa-chevron-down"></i>
          </div>
          <div
            class="categoria-opciones"
            :class="{ active: categoriaActiva === index }"
          >
            <div
              v-for="(opcion, i) in categoria.opciones"
              :key="i"
              class="categoria-opcion"
              @click="seleccionarSubcategoria(opcion.valor)"
            >
              <label>{{ opcion.texto }}</label>
            </div>
          </div>
        </div>
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
      categorias: [
        {
          nombre: 'Deportes',
          opciones: [
            { valor: 'futbol', texto: 'Fútbol' },
            { valor: 'basketball', texto: 'Baloncesto' },
            { valor: 'tenis', texto: 'Tenis' },
          ],
        },
        {
          nombre: 'Ropa',
          opciones: [
            { valor: 'ropa-hombre', texto: 'Ropa Hombre' },
            { valor: 'ropa-mujer', texto: 'Ropa Mujer' },
            { valor: 'ropa-ninos', texto: 'Ropa Niños' },
          ],
        },
        {
          nombre: 'Consumibles',
          opciones: [
            { valor: 'proteinas', texto: 'Proteínas' },
            { valor: 'barras', texto: 'Barras energéticas' },
          ],
        },
        {
          nombre: 'Accesorios',
          opciones: [
            { valor: 'accesorios', texto: 'Accesorios' },
          ],
        },
      ],
      categoriaActiva: null, // Índice de la categoría activa
      subcategoriaSeleccionada: '', // Subcategoría seleccionada
      carrito: [],
      busqueda: '',
      productoAgregado: null,
      productosFiltrados: [],
      popupVisible: false,
      productoSeleccionado: null,
      carritoVisible: false,
    };
  },
  created() {
    this.productosFiltrados = this.productos; // Mostrar todos los productos al inicio
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
    toggleAcordeon(index) {
      this.categoriaActiva = this.categoriaActiva === index ? null : index;
    },
    seleccionarSubcategoria(subcategoria) {
      this.subcategoriaSeleccionada = subcategoria;
      this.filtrarProductos();
    },
    filtrarProductos() {
      let productosFiltrados = this.productos;

      // Filtrar por subcategoría
      if (this.subcategoriaSeleccionada) {
        productosFiltrados = productosFiltrados.filter(
          (producto) => producto.categoria === this.subcategoriaSeleccionada
        );
      }

      // Filtrar por búsqueda
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
  font-family: 'Poppins', sans-serif; /* Fuente moderna */
  margin: 0;
  padding: 0;
  background-color: #f8f9fa; /* Fondo claro */
  color: #333; /* Color de texto principal */
}

/* ------------------- ESTILOS DEL NAVBAR ------------------- */
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
  color: #ff3149;
}

/* ------------------- ESTILOS DE LA TIENDA ------------------- */

/* Título de la tienda */
.tienda-title {
  text-align: center;
  margin-top: 2rem;
  font-size: 2.5rem;
  color: #2c3e50; /* Texto oscuro */
  font-weight: 700; /* Texto más grueso */
}



/* Filtros y barra de búsqueda */


.search-bar {
  flex: 1;
}

.search-input {
  width: 100%;
  padding: 0.75rem;
  font-size: 1rem;
  border: 1px solid #e0e0e0; /* Borde gris claro */
  border-radius: 5px;
  outline: none;
  transition: border-color 0.3s ease-in-out;
}

.search-input:focus {
  border-color: #17a2b8; /* Borde azul al enfocar */
}



/* ------------------- ESTILOS DE LA TIENDA ------------------- */

/* Título de la tienda */
.tienda-title {
  text-align: center;
  margin-top: 2rem;
  font-size: 2.5rem;
  color: #2c3e50;
  font-weight: 700;
}

/* Barra de búsqueda */
.search-container {
  display: flex;
  justify-content: center;
  margin: 2rem auto;
  width: 70%; /* Ancho del 70% */
}

.search-input {
  width: 100%;
  padding: 0.75rem;
  font-size: 1rem;
  border: 1px solid #e0e0e0;
  border-radius: 5px;
  outline: none;
  transition: border-color 0.3s ease-in-out;
}

.search-input:focus {
  border-color: #17a2b8;
}

/* ------------------- ESTILOS DE LAS CATEGORÍAS HORIZONTALES ------------------- */
.filtros-container {
  display: flex;
  justify-content: center;
  margin: 1rem auto;
  width: 70%; /* Ancho del 70% */
}

.categorias-horizontal {
  display: flex;
  gap: 1rem;
  flex-wrap: wrap;
  justify-content: center;
}

.categoria-item {
  background-color: #ffffff;
  border-radius: 10px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  overflow: hidden;
  transition: transform 0.3s ease-in-out;
  cursor: pointer;
  flex: 1 1 200px; /* Flex para que las categorías se ajusten */
  max-width: 250px; /* Ancho máximo */
}

.categoria-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem;
  background-color: #17a2b8;
  color: white;
  border-radius: 10px 10px 0 0;
}

.categoria-header h4 {
  font-size: 1.1rem;
  margin: 0;
  font-weight: 600;
}

.categoria-header i {
  font-size: 1.2rem;
  transition: transform 0.3s ease-in-out;
}

.categoria-header.active i {
  transform: rotate(180deg);
}

.categoria-opciones {
  padding: 1rem;
  display: none;
}

.categoria-opciones.active {
  display: block;
}

.categoria-opcion {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem;
  cursor: pointer;
  transition: background-color 0.3s ease-in-out;
}

.categoria-opcion:hover {
  background-color: #f8f9fa;
}

.categoria-opcion label {
  font-size: 1rem;
  color: #2c3e50;
  cursor: pointer;
}




/* ------------------- ESTILOS DE LOS PRODUCTOS ------------------- */

.productos-container {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 2rem;
  padding: 2rem;
  max-width: 1200px;
  margin: 0 auto;
}

.producto-card {
  background-color: #ffffff; /* Fondo blanco */
  border-radius: 10px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1); /* Sombra suave */
  overflow: hidden;
  transition: transform 0.3s ease-in-out, box-shadow 0.3s ease-in-out;
  cursor: pointer;
}

.producto-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 6px 16px rgba(0, 0, 0, 0.15); /* Sombra más pronunciada al pasar el mouse */
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
  padding: 1.5rem;
  text-align: center;
}

.producto-nombre {
  font-size: 1.25rem;
  color: #2c3e50; /* Texto oscuro */
  margin-bottom: 0.5rem;
  font-weight: 600; /* Texto más grueso */
}

.producto-precio {
  font-size: 1.1rem;
  color: #17a2b8; /* Azul claro */
  margin-bottom: 1rem;
  font-weight: 500; /* Texto semi-grueso */
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
  background-color: #ffffff; /* Fondo blanco */
  border-radius: 10px;
  padding: 2rem;
  max-width: 600px;
  width: 90%;
  position: relative;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1); /* Sombra suave */
}

.btn-cerrar {
  position: absolute;
  top: 1rem;
  right: 1rem;
  background: none;
  border: none;
  font-size: 1.5rem;
  cursor: pointer;
  color: #2c3e50; /* Texto oscuro */
  transition: color 0.3s ease-in-out;
}

.btn-cerrar:hover {
  color: #17a2b8; /* Azul claro al pasar el mouse */
}

.popup-imagen {
  width: 100%;
  max-height: 300px;
  object-fit: cover;
  border-radius: 10px;
  margin-bottom: 1.5rem;
}

.popup-info {
  text-align: center;
}

.popup-nombre {
  font-size: 1.75rem;
  color: #2c3e50; /* Texto oscuro */
  margin-bottom: 1rem;
  font-weight: 700; /* Texto más grueso */
}

.popup-descripcion {
  font-size: 1rem;
  color: #666; /* Texto gris */
  margin-bottom: 1.5rem;
}

.popup-precio {
  font-size: 1.5rem;
  color: #17a2b8; /* Azul claro */
  margin-bottom: 1.5rem;
  font-weight: 600; /* Texto más grueso */
}

.btn-comprar {
  padding: 0.75rem 1.5rem;
  background-color: #17a2b8; /* Azul claro */
  color: white;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  font-size: 1rem;
  transition: background-color 0.3s ease-in-out;
}

.btn-comprar:hover {
  background-color: #138496; /* Azul más oscuro al pasar el mouse */
}

/* Carrito */
.carrito {
  position: fixed;
  bottom: 2rem;
  right: 2rem;
}

.btn-carrito {
  padding: 0.75rem 1.5rem;
  background-color: #17a2b8; /* Azul claro */
  color: white;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  font-size: 1rem;
  transition: background-color 0.3s ease-in-out;
}

.btn-carrito:hover {
  background-color: #138496; /* Azul más oscuro al pasar el mouse */
}

/* Pop-up del carrito */
.carrito-popup {
  max-width: 500px;
}

.carrito-titulo {
  font-size: 1.75rem;
  color: #2c3e50; /* Texto oscuro */
  margin-bottom: 1.5rem;
  text-align: center;
  font-weight: 700; /* Texto más grueso */
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
  color: #2c3e50; /* Texto oscuro */
  margin-bottom: 0.25rem;
  font-weight: 600; /* Texto más grueso */
}

.carrito-precio {
  font-size: 0.9rem;
  color: #17a2b8; /* Azul claro */
}

.btn-eliminar {
  background-color: #ff4d4d; /* Rojo */
  color: white;
  border: none;
  padding: 0.5rem 1rem;
  border-radius: 5px;
  cursor: pointer;
  transition: background-color 0.3s ease-in-out;
}

.btn-eliminar:hover {
  background-color: #cc0000; /* Rojo más oscuro al pasar el mouse */
}

.carrito-total {
  text-align: right;
  font-size: 1.25rem;
  color: #2c3e50; /* Texto oscuro */
  margin-bottom: 1.5rem;
  font-weight: 600; /* Texto más grueso */
}

.btn-finalizar {
  width: 100%;
  padding: 0.75rem;
  background-color: #17a2b8; /* Azul claro */
  color: white;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  font-size: 1rem;
  transition: background-color 0.3s ease-in-out;
}

.btn-finalizar:hover {
  background-color: #138496; /* Azul más oscuro al pasar el mouse */
}

/* Notificación */
.notificacion {
  position: fixed;
  bottom: 2rem;
  left: 50%;
  transform: translateX(-50%);
  background-color: #2c3e50; /* Fondo oscuro */
  color: white;
  padding: 1rem 2rem;
  border-radius: 5px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1); /* Sombra suave */
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