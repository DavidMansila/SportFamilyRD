<template>
  <div class="tienda-page">
    
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

    
    <!-- Filtros -->
    <div class="filtros-container">
  <div class="categorias-horizontal">
    <!-- Opción "Ver todos" -->
    <div class="categoria-item">
      <div
        class="categoria-header"
        @click="seleccionarSubcategoria('')"
      >
        <h4>Ver todos</h4>
      </div>
    </div>

    <!-- Categorías principales -->
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
          <img :src="producto.image" alt="Imagen del producto" class="producto-imagen" />
        </div>
        <div class="producto-info">
          <h3 class="producto-nombre">{{ producto.name }}</h3>
          <p class="producto-precio">{{ producto.price }} RD$ </p>
        </div>
      </div>
    </div>

    <!-- Pop-up de detalles del producto -->
    <div v-if="popupVisible" class="popup-overlay" @click="cerrarPopup">
      <div class="popup-content" @click.stop>
        <button class="btn-cerrar" @click="cerrarPopup">×</button>
        <img :src="productoSeleccionado.image" alt="Imagen del producto" class="popup-imagen" />
        <div class="popup-info">
          <h3 class="popup-nombre">{{ productoSeleccionado.name }}</h3>
          <p class="popup-descripcion">{{ productoSeleccionado.description }}</p>
          <p class="popup-precio"> {{ productoSeleccionado.price}} RD$ </p>
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
            <img :src="item.image" alt="Imagen del producto" class="carrito-imagen" />
            <div class="carrito-info">
              <h3 class="carrito-nombre">{{ item.name }}</h3>
              <p class="carrito-precio">{{ item.price }}</p>
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

    computed: {

      calcularTotal() {
        return this.carrito.reduce((total, producto) => {
          return total + parseFloat(producto.price);
        }, 0).toFixed(2);
      },
    },

    methods: {
      getProducts() {
        axios.get('/products')
        .then(response => {
          this.productos = response.data.products;
          this.productosFiltrados = this.productos; 
        })
        .catch(error => {
          console.error('Error al cargar los productos:', error);
        });
      },

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
      // Si la categoría ya está activa, se cierra
      if (this.categoriaActiva === index) {
        this.categoriaActiva = null;
      } else {
        // Si no, se abre la categoría seleccionada
        this.categoriaActiva = index;
      }
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
    
    mounted() {
      this.productosFiltrados = this.productos; 
      this.getProducts();
    },

  };
</script>



<style scoped>
  @import '../../../scss/Tienda/tienda.scss';
</style>
