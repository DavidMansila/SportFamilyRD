<template>
  <div class="tienda-page">

    <!-- Navbar -->
    <Navbar />

    <!-- Barra de búsqueda -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <div class="search-container">
      <div class="search-wrapper">
        <input type="text" v-model="busqueda" placeholder="Buscar productos..." @input="filtrarProductos"
          class="search-input" />
        <i class="fas fa-search search-icon"></i>
      </div>
    </div>

    <!-- Filtros -->
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

          <button v-if="user.user_type === 'admin'" @click.stop="abrirFormularioProducto(producto)" class="btn-editar">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
              stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
              <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
            </svg>
          </button>

          <button v-if="user.user_type === 'admin'" @click.stop="eliminarProducto(producto.id)" class="btn-eliminar">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
              stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <polyline points="3 6 5 6 21 6"></polyline>
              <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
            </svg>
          </button>

        </div>
      </div>
    </div>


    <!-- Modal de producto -->
    <div class="product-modal" :class="{ active: popupVisible }" @click.self="cerrarPopup">
      <div class="modal-content" @click.stop>


        <button class="close-modal" @click="cerrarPopup" aria-label="Cerrar ventana">
          <svg class="close-icon" viewBox="0 0 24 24">
            <path fill="currentColor"
              d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12 19 6.41z" />

          </svg>
        </button>



        <div class="modal-grid" v-if="productoSeleccionado">
          <div class="modal-images">
            <img :src="productoSeleccionado.image" :alt="productoSeleccionado.name" class="main-image" />
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
          </div>
        </div>
      </div>
    </div>



    <!-- Botón flotante admin -->
    <button v-if="user.user_type === 'admin'" @click="abrirFormularioProducto" class="floating-admin-btn">
      <i class="fas fa-plus"></i>
    </button>

    <!-- Modal formulario (agregar al final del template) -->
    <div class="admin-modal" v-if="showAdminForm" @click.self="cerrarAdminForm">
      <div class="admin-modal-content">
        <h2>{{ editingProduct ? 'Editar Producto' : 'Nuevo Producto' }}</h2>

        <form @submit.prevent="guardarProducto">
          <div class="form-group">
            <input v-model="formProducto.name" placeholder="Nombre del producto" required>
          </div>

          <div class="form-group">
            <textarea v-model="formProducto.description" placeholder="Descripción" required></textarea>
          </div>

          <div class="form-group">
            <input type="number" v-model="formProducto.price" placeholder="Precio (RD$)" step="0.01" required>
          </div>

          <div class="form-group">
            <select v-model="formProducto.category" required>
              <option value="">Seleccionar categoría</option>
              <option v-for="cat in categoriasFlat" :value="cat.valor">{{ cat.texto }}</option>
            </select>
          </div>

          <div class="form-group">
            <input v-model="formProducto.image" placeholder="URL de la imagen" required>
          </div>

          <div class="form-group">
            <input type="number" v-model="formProducto.stock" placeholder="Stock disponible" required>
          </div>

          <div class="form-actions">
            <button type="button" @click="cerrarAdminForm" class="btn-cancelar">Cancelar</button>
            <button type="submit" class="btn-guardar">{{ editingProduct ? 'Actualizar' : 'Crear' }}</button>
          </div>
        </form>
      </div>
    </div>




  </div>
</template>

<script>
import Navbar from '../navbarComponent.vue';
import axios from 'axios';

export default {
  name: 'TiendaComponent',
  components: {
    Navbar
  },
  data() {
    return {
      productos: [],
      categorias: [
        {
          nombre: 'Deportes',
          opciones: [
            { valor: 'Futbol', texto: 'Fútbol' },
            { valor: 'Baloncesto', texto: 'Baloncesto' },
            { valor: 'Tenis', texto: 'Tenis' },
            { valor: 'Ciclismo', texto: 'Ciclismo' },
            { valor: 'Natacion', texto: 'Natación' },
            { valor: 'Boxeo', texto: 'Boxeo' }
          ]
        },
        {
          nombre: 'Ropa Deportiva',
          opciones: [
            { valor: 'Hombres', texto: 'Ropa Hombre' },
            { valor: 'Mujeres', texto: 'Ropa Mujer' },
            { valor: 'Ninos', texto: 'Ropa Niños' },
            { valor: 'Calzado', texto: 'Calzado' },
            { valor: 'Activewear', texto: 'Activewear' },
            { valor: 'Accesorios', texto: 'Accesorios' }
          ]
        },
        {
          nombre: 'Equipamiento',
          opciones: [
            { valor: 'Pelotas', texto: 'Pelotas' },
            { valor: 'Raquetas', texto: 'Raquetas' },
            { valor: 'Bicicletas', texto: 'Bicicletas' },
            { valor: 'Pesas', texto: 'Pesas' },
            { valor: 'Protecciones', texto: 'Protecciones' }
          ]
        },
        {
          nombre: 'Suplementos',
          opciones: [
            { valor: 'Proteinas', texto: 'Proteínas' },
            { valor: 'Vitaminas', texto: 'Vitaminas' },
            { valor: 'Quemadores', texto: 'Quemadores' },
            { valor: 'Energizantes', texto: 'Energizantes' },
            { valor: 'Barras', texto: 'Barras' }
          ]
        },
        {
          nombre: 'Accesorios',
          opciones: [
            { valor: 'Electrónicos', texto: 'Electrónicos' },
            { valor: 'Hidratación', texto: 'Hidratación' },
            { valor: 'Mochilas', texto: 'Mochilas' },
            { valor: 'Relojes', texto: 'Relojes' },
            { valor: 'Toallas', texto: 'Toallas' }
          ]
        }
      ],
      categoriaActiva: null,
      subcategoriaSeleccionada: '',
      busqueda: '',
      productosFiltrados: [],
      popupVisible: false,
      productoSeleccionado: null,
      quantity: 1,
      showAdminForm: false,
      editingProduct: null,
      formProducto: this.resetForm(),
      categoriasFlat: [],
      user: [],

    };
  },

  watch: {
    subcategoriaSeleccionada() {
      this.filtrarProductos();
    }
  },

  methods: {


    getProducts() {
      axios.get('/products')
        .then(response => {
          this.productos = response.data.products.map(product => ({
            ...product,
            categoria: product.category.toLowerCase(), // Normalizar categorías
            name: product.name.toLowerCase(),
            description: product.description?.toLowerCase() || '',
            images: product.images || [product.image],
            oferta: !!product.oldPrice
          }));
          this.productosFiltrados = this.productos;
        })
        .catch(error => {
          console.error('Error al cargar productos:', error);
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
      const searchTerm = this.busqueda.toLowerCase().trim();
      const categoryFilter = this.subcategoriaSeleccionada.toLowerCase();

      this.productosFiltrados = this.productos.filter(producto => {
        const matchCategory = categoryFilter ?
          producto.categoria.toLowerCase() === categoryFilter :
          true;

        const matchSearch = producto.name.toLowerCase().includes(searchTerm) ||
          (producto.description && producto.description.toLowerCase().includes(searchTerm));

        return matchCategory && matchSearch;
      });
    },

    abrirPopup(producto) {
      this.productoSeleccionado = { ...producto };
      this.popupVisible = true;
      document.body.style.overflow = 'hidden';
    },

    cerrarPopup() {
      this.popupVisible = false;
      document.body.style.overflow = 'auto';
    },

    incrementQuantity() {
      this.quantity++;
    },

    decrementQuantity() {
      if (this.quantity > 1) this.quantity--;
    },

    calculateDiscount(price, oldPrice) {
      return Math.round(((oldPrice - price) / oldPrice) * 100);
    },

    getCategoryName(categoryValue) {
      for (const category of this.categorias) {
        const found = category.opciones.find(opt =>
          opt.valor.toLowerCase() === categoryValue.toLowerCase()
        );
        if (found) return category.nombre;
      }
      return 'General';
    },

    handleKeyup(e) {
      if (e.key === 'Escape' && this.popupVisible) {
        this.cerrarPopup();
      }
    },



    // FUNCIONES DE ADMINISTRADOR

    resetForm() {
      return {
        name: '',
        description: '',
        price: 0,
        category: '',
        image: '',
        stock: 0
      }
    },

    abrirFormularioProducto(producto = null) {
      if (producto) {
        this.editingProduct = producto.id
        this.formProducto = { ...producto }
      } else {
        this.editingProduct = null
        this.formProducto = this.resetForm()
      }
      this.showAdminForm = true
    },


    guardarProducto() {
      const requestData = {
        name: this.formProducto.name,
        description: this.formProducto.description,
        price: parseFloat(this.formProducto.price),
        category: this.formProducto.category,
        image: this.formProducto.image,
        stock: parseInt(this.formProducto.stock)
      };

      const config = {
        headers: {
          'Content-Type': 'application/json',
          Authorization: `Bearer ${localStorage.getItem('token')}`
        }
      };

      if (this.editingProduct) {
        axios.put(`/products/${this.editingProduct}`, requestData, config)
          .then(() => {
            this.getProducts();
            this.cerrarAdminForm();
            alert('Producto actualizado correctamente');
          })
          .catch(error => {
            console.error('Error:', error.response?.data);
            alert(error.response?.data?.message || 'Error al actualizar');
          });
      } else {
        axios.post('/products', requestData, config)
          .then(() => {
            this.getProducts();
            this.cerrarAdminForm();
            alert('Producto creado correctamente');
          })
          .catch(error => {
            console.error('Error:', error.response?.data);
            alert(error.response?.data?.message || 'Error al crear');
          });
      }
    },

    async eliminarProducto(id) {
      if (confirm('¿Eliminar este producto permanentemente?')) {
        try {
          await axios.delete(`/products/${id}`, {
            headers: {
              Authorization: `Bearer ${localStorage.getItem('token')}`
            }
          })
          this.getProducts()
        } catch (error) {
          console.error('Error eliminando producto:', error)
        }
      }
    },


    cerrarAdminForm() {
      this.showAdminForm = false;
      // Añadir timeout para esperar a que termine la transición
      setTimeout(() => {
        this.editingProduct = null;
        this.formProducto = this.resetForm();
      }, 300); // Debe coincidir con el tiempo de transición del modal
    },

    generarCategoriasFlat() {
      this.categoriasFlat = this.categorias.flatMap(c =>
        c.opciones.map(o => ({
          valor: o.valor,
          texto: `${c.nombre} - ${o.texto}`
        }))
      )
    }

  },
  mounted() {
    this.getProducts();
    window.addEventListener('keyup', this.handleKeyup);
    this.user = JSON.parse(sessionStorage.getItem('user'));
    this.generarCategoriasFlat()
    document.title = 'Tienda';
  },
  beforeDestroy() {
    window.removeEventListener('keyup', this.handleKeyup);
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

@import '../../../scss/Admin/Admin_tienda.scss';
</style>