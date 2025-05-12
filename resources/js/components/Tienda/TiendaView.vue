<template>
  <div class="tienda-page">



    <!-- Navbar -->
    <nav class="navbar">
      <div class="logo-container">
        <a href="/" class="logo-container">
          <img src="/imagenes/logo2.png" alt="SportFamilyRD Logo" class="logo" />
        </a>
      </div>

      <div class="nav-links">

        <!-- Secciones para lo usuarios y no usuarios -->
        <a href="/Noticias" class="nav-link">Noticias</a>
        <a href="/Calendario" class="nav-link">Calendario</a>
        <a href="/Tienda" class="nav-link">Tienda</a>
        <a href="/Entrenadores" class="nav-link">Entrenadores</a>
        <a href="/Foro" class="nav-link">Foro</a>

        <!-- Secciones para entrenadores -->
        <a v-if="userType == 'entrenador'" href="/SolicitudesUsuarios" class="nav-link">Solicitudes</a>

        <!-- Secciones para entrenadores -->
        <a v-if="userType == 'admin'" href="/SolicitudesEntrenadores" class="nav-link">Solicitudes</a>


      </div>

      <div class="Imagenes">

        <a href="#" class="Carrito">
          <img src="/imagenes/Carrito-Icon.png" alt="Carrito" class="carrito-icon" />
        </a>

        <a href="/Ajustes" class="Ajustes">
          <img src="/imagenes/Ajustes-Icon.png" alt="Ajustes" class="ajustes-icon" />
        </a>

        <a href="/Perfil" class="Perfil">
          <img src="/imagenes/Perfil-Icon.png" alt="Perfil" class="perfil-icon" />
        </a>

        <a :href="login ? '/Login' : '/Logout'" class="Logout">
          <img src="/imagenes/Logout-Icon.png" alt="Logout" class="logout-icon" />
        </a>

      </div>
    </nav>



    <!-- Barra de búsqueda -->
    <div class="search-container">
      <div class="search-wrapper">
        <input type="text" v-model="busqueda" placeholder="Buscar productos..." @input="filtrarPorBusqueda"
          class="search-input" />
        <i class="fas fa-search search-icon"></i>
      </div>
    </div>



    <!-- Filtros modernos -->
    <div class="filters-section">
      <div class="filter-tabs">
        <button class="filter-tab" :class="{ active: subcategoriaSeleccionada === '' }"
          @click="seleccionarSubcategoria('')">
          Todos
        </button>

        <div v-for="(categoria, index) in categorias" :key="index" class="filter-dropdown">
          <button class="filter-tab" :class="{ active: categoriaActiva === index }" @click="toggleAcordeon(index)">
            {{ categoria.nombre }}
            <i class="fas fa-chevron-down dropdown-icon"></i>
          </button>

          <div class="dropdown-content" :class="{ show: categoriaActiva === index }">
            <button v-for="(opcion, i) in categoria.opciones" :key="i" class="dropdown-item"
              @click="seleccionarSubcategoria(opcion.valor)">
              {{ opcion.texto }}
            </button>
          </div>
        </div>
      </div>
    </div>



    <!-- Productos -->
    <div class="products-grid">
      <div v-for="producto in productosFiltrados" :key="producto.id" class="product-card" @click="abrirPopup(producto)">
        <div class="product-badge" v-if="producto.oferta">OFERTA</div>
        <div class="product-image-container">
          <img :src="producto.image" :alt="producto.name" class="product-image" />
          <button class="quick-view-btn" @click.stop="abrirPopup(producto)">
            Ver Detalles
          </button>
        </div>
        <div class="product-info">
          <span class="product-category">{{ getCategoryName(producto.categoria) }}</span>
          <h3 class="product-name">{{ producto.name }}</h3>
          <div class="product-price-container">
            <span class="product-price">{{ producto.price }} RD$</span>
            <span class="product-old-price" v-if="producto.oldPrice">{{ producto.oldPrice }} RD$</span>
          </div>
          <button class="add-to-cart-btn" @click.stop="agregarAlCarrito(producto)">
            <i class="fas fa-shopping-cart"></i> Añadir
          </button>
        </div>
      </div>
    </div>



    <!-- Popup de producto -->
    <div v-if="popupVisible" class="product-modal" @click="cerrarPopup">
      <div class="modal-content" @click.stop>
        <button class="close-modal" @click="cerrarPopup">
          <i class="fas fa-times"></i>
        </button>

        <div class="modal-grid">
          <div class="modal-images">
            <img :src="productoSeleccionado.image" :alt="productoSeleccionado.name" class="main-image" />
            <div class="thumbnail-container">
              <img v-for="(img, index) in productoSeleccionado.images" :key="index" :src="img" class="thumbnail"
                @click="changeMainImage(img)" />
            </div>
          </div>

          <div class="modal-details">
            <div class="product-header">
              <span class="product-category">{{ getCategoryName(productoSeleccionado.categoria) }}</span>
              <h2 class="product-title">{{ productoSeleccionado.name }}</h2>
              <div class="product-rating">
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <i class="fas fa-star-half-alt"></i>
                <span class="rating-count">(24 reviews)</span>
              </div>
            </div>

            <div class="price-container">
              <span class="current-price">{{ productoSeleccionado.price }} RD$</span>
              <span class="old-price" v-if="productoSeleccionado.oldPrice">{{ productoSeleccionado.oldPrice }}
                RD$</span>
              <span class="discount" v-if="productoSeleccionado.oldPrice">
                {{ calculateDiscount(productoSeleccionado.price, productoSeleccionado.oldPrice) }}% OFF
              </span>
            </div>

            <p class="product-description">{{ productoSeleccionado.description }}</p>

            <div class="product-actions">
              <div class="quantity-selector">
                <button @click="decrementQuantity">-</button>
                <span>{{ quantity }}</span>
                <button @click="incrementQuantity">+</button>
              </div>

              <button class="add-to-cart" @click="addToCartFromModal">
                <i class="fas fa-shopping-cart"></i> Añadir al carrito
              </button>
            </div>

            <div class="product-meta">
              <div class="meta-item">
                <i class="fas fa-shield-alt"></i>
                <span>Garantía de 1 año</span>
              </div>
              <div class="meta-item">
                <i class="fas fa-truck"></i>
                <span>Envío gratis en órdenes > 2000 RD$</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>








  </div>
</template>



<script>
export default {
  name: 'TiendaComponent',
  data() {
    return {
      productos: [],
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
          nombre: 'Suplementos',
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
      categoriaActiva: null,
      subcategoriaSeleccionada: '',
      carrito: [],
      busqueda: '',
      productoAgregado: null,
      productosFiltrados: [],
      popupVisible: false,
      productoSeleccionado: null,
      cartVisible: false,
      quantity: 1,
      currentImage: ''
    };
  },
  computed: {
    calcularSubtotal() {
      return this.carrito.reduce((total, item) => {
        return total + (parseFloat(item.price) * (item.quantity || 1));
      }, 0).toFixed(2);
    },
    calcularEnvio() {
      return this.calcularSubtotal > 2000 ? '0.00' : '150.00';
    },
    calcularTotal() {
      return (parseFloat(this.calcularSubtotal) + parseFloat(this.calcularEnvio)).toFixed(2);
    }
  },
  methods: {
    getProducts() {
      axios.get('/products')
        .then(response => {
          this.productos = response.data.products.map(product => {
            return {
              ...product,
              images: product.images || [product.image],
              oldPrice: product.oldPrice || null,
              oferta: product.oldPrice ? true : false
            };
          });
          this.productosFiltrados = this.productos;
        })
        .catch(error => {
          console.error('Error al cargar los productos:', error);
        });
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

      if (this.subcategoriaSeleccionada) {
        productosFiltrados = productosFiltrados.filter(
          producto => producto.categoria === this.subcategoriaSeleccionada
        );
      }

      if (this.busqueda) {
        const searchTerm = this.busqueda.toLowerCase();
        productosFiltrados = productosFiltrados.filter(producto =>
          producto.name.toLowerCase().includes(searchTerm) ||
          (producto.description && producto.description.toLowerCase().includes(searchTerm))
        );
      }

      this.productosFiltrados = productosFiltrados;
    },
    filtrarPorBusqueda() {
      this.filtrarProductos();
    },
    abrirPopup(producto) {
      this.productoSeleccionado = {
        ...producto,
        images: producto.images || [producto.image]
      };
      this.currentImage = this.productoSeleccionado.image;
      this.quantity = 1;
      this.popupVisible = true;
    },
    cerrarPopup() {
      this.popupVisible = false;
    },
    changeMainImage(img) {
      this.currentImage = img;
    },
    agregarAlCarrito(producto) {
      const existingItem = this.carrito.find(item => item.id === producto.id);

      if (existingItem) {
        existingItem.quantity = (existingItem.quantity || 1) + 1;
      } else {
        this.carrito.push({
          ...producto,
          quantity: 1
        });
      }

      this.showNotification(producto);
    },
    addToCartFromModal() {
      const productToAdd = {
        ...this.productoSeleccionado,
        quantity: this.quantity
      };

      const existingItem = this.carrito.find(item => item.id === productToAdd.id);

      if (existingItem) {
        existingItem.quantity += this.quantity;
      } else {
        this.carrito.push(productToAdd);
      }

      this.showNotification(productToAdd);
      this.cerrarPopup();
    },
    showNotification(producto) {
      this.productoAgregado = producto;
      setTimeout(() => {
        this.productoAgregado = null;
      }, 3000);
    },
    eliminarDelCarrito(index) {
      this.carrito.splice(index, 1);
    },
    toggleCart() {
      this.cartVisible = !this.cartVisible;
    },
    closeAllModals() {
      this.popupVisible = false;
      this.cartVisible = false;
    },
    finalizarCompra() {
      alert(`Compra finalizada. Total: ${this.calcularTotal} RD$`);
      this.carrito = [];
      this.cartVisible = false;
    },
    incrementQuantity() {
      this.quantity++;
    },
    decrementQuantity() {
      if (this.quantity > 1) this.quantity--;
    },
    increaseQuantity(index) {
      this.carrito[index].quantity = (this.carrito[index].quantity || 1) + 1;
    },
    decreaseQuantity(index) {
      if (this.carrito[index].quantity > 1) {
        this.carrito[index].quantity--;
      } else {
        this.eliminarDelCarrito(index);
      }
    },
    calculateDiscount(price, oldPrice) {
      return Math.round(((oldPrice - price) / oldPrice) * 100);
    },
    getCategoryName(categoryValue) {
      for (const category of this.categorias) {
        const found = category.opciones.find(opt => opt.valor === categoryValue);
        if (found) return category.nombre;
      }
      return 'General';
    }





    // FUNCIONES PARA ADMINISTRADOR

    // EDITAR PRODUCTO
    // ELIMINAR PRODUCTO 
    // AGREGAR PRODUCTO


  },
  mounted() {
    this.getProducts();
    document.title = 'Tienda';
  }
};
</script>



<style scoped>
@import '../../../scss/Tienda/tienda.scss';

@import '../../../scss/Tienda/tienda_filtros_y_busqueda.scss';

@import '../../../scss/Tienda/tienda_grid.scss';

@import '../../../scss/Tienda/tienda_modal.scss';

@import '../../../scss/Tienda/tienda_navbar.scss';

@import '../../../scss/Tienda/tienda_responsive.scss';
</style>
