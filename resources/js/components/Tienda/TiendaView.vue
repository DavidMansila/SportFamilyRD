<template>
  <div class="tienda-page">

    <!-- Navbar -->
    <Navbar />

    <div class="store-header">
      <div class="header-overlay">
        <div class="header-content">
          <h1 class="store-title">Bienvenido a SportShop</h1>
          <p class="store-subtitle">Encuentra todo para tu rendimiento deportivo</p>

          <!-- Barra de búsqueda -->
          <div class="search-wrapper animated-search">
            <input type="text" v-model="busqueda" placeholder="Buscar productos..." @input="filtrarProductos"
              class="search-input" />
            <button class="search-btn">
              <i class="fas fa-search search-icon"></i>
            </button>
          </div>

          <div class="header-deco-shapes">
            <div class="deco-circle"></div>
            <div class="deco-triangle"></div>
            <div class="deco-wave"></div>
          </div>
        </div>
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


    <!-- Botón de filtros para móviles -->
    <button class="mobile-filter-btn" @click="showMobileFilters = true">
      <i class="fas fa-filter"></i> Filtros
    </button>

    <!-- Menú de filtros móviles -->
    <div class="mobile-filters-menu" :class="{ active: showMobileFilters }">
      <div class="mobile-filters-header">
        <h3>Filtrar Productos</h3>
        <button class="close-mobile-filters" @click="showMobileFilters = false">
          <i class="fas fa-times"></i>X
        </button>
      </div>

      <div class="mobile-filters-content">
        <button class="mobile-filter-option" @click="seleccionarSubcategoria(''); showMobileFilters = false;">
          Todos los productos
        </button>

        <div v-for="(categoria, index) in categorias" :key="index" class="mobile-category">
          <div class="mobile-category-header" @click="toggleMobileCategory(index)">
            {{ categoria.nombre }}
            <i class="fas fa-chevron-down" :class="{ 'fa-rotate-180': mobileCategoryOpen === index }"></i>
          </div>

          <div class="mobile-subcategories" v-show="mobileCategoryOpen === index">
            <button v-for="(opcion, i) in categoria.opciones" :key="i"
              @click="seleccionarSubcategoria(opcion.valor); showMobileFilters = false;">
              {{ opcion.texto }}
            </button>
          </div>
        </div>
      </div>
    </div>


    <!-- Productos -->
    <div class="products-grid">
      <div v-for="producto in paginatedProducts" :key="producto.id" class="product-card" @click="abrirPopup(producto)">
        <!-- <div class="product-badge" v-if="producto.oferta">OFERTA</div> -->
        <div class="product-image-container">
          <img :src="producto.image" :alt="producto.name" class="product-image" loading="lazy" />
          <button class="quick-view-btn" @click.stop="abrirPopup(producto)">
            Ver Detalles
          </button>
        </div>
        <div class="product-info">
          <span class="product-category">{{ getCategoryName(producto.categoria) }}</span>
          <h3 class="product-name">{{ producto.name }}</h3>
          <div class="product-price-container">
            <span class="product-price"> RD$ {{ producto.price }}</span>
            <!-- <span class="product-old-price" v-if="producto.oldPrice">{{ producto.oldPrice }} RD$</span> -->
          </div>
          <button v-if="user" class="add-to-cart-btn" @click.stop="agregarAlCarrito(producto)">
            <i class="fas fa-shopping-cart">Agregar</i>
          </button>

        </div>

        <!-- Acciones de admin agrupadas arriba a la derecha de la tarjeta.
             Antes iban sueltas dentro de .product-info, cada una posicionada
             por su cuenta con `right: 50px` / `right: 10px`. -->
        <div v-if="user?.user_type === 'admin'" class="admin-actions">
          <button type="button" class="btn-editar" :aria-label="`Editar ${producto.name}`" title="Editar producto"
            @click.stop="abrirFormularioProducto(producto)">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
              stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
              <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
              <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
            </svg>
          </button>

          <button type="button" class="btn-eliminar" :aria-label="`Eliminar ${producto.name}`" title="Eliminar producto"
            @click.stop="pedirConfirmacionBorrado(producto)">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
              stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
              <polyline points="3 6 5 6 21 6"></polyline>
              <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
            </svg>
          </button>
        </div>
      </div>
    </div>

    <!-- NO PRODUCTOS -->
    <div v-if="productosFiltrados.length === 0 && !isLoading" class="no-products">
      <div class="center-wrapper">
        <img src="/imagenes/no-news.png" class="empty-image" alt="No hay imagen" />
      </div>
      <h3>No hay productos disponibles</h3>
      <p>Actualmente no hay productos para mostrar.</p>
    </div>


    <!-- Paginación -->
    <div v-if="productosFiltrados.length > itemsPerPage">
      <paginatorComponent v-model="currentPage" :total-items="productosFiltrados.length" :items-per-page="itemsPerPage"
        :max-pages-shown="5" />
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
              <span class="current-price"> RD$ {{ productoSeleccionado.price }} </span>
              <!-- <span class="old-price" v-if="productoSeleccionado.oldPrice">{{ productoSeleccionado.oldPrice }}
                RD$</span>
              <span class="discount" v-if="productoSeleccionado.oldPrice">
                {{ calculateDiscount(productoSeleccionado.price, productoSeleccionado.oldPrice) }}% OFF
              </span> -->
            </div>

            <p class="product-description">{{ productoSeleccionado.description }}</p>

            <div v-if="user" class="product-actions">
              <div class="quantity-selector">
                <button @click="decrementQuantity">-</button>
                <span>{{ quantity }}</span>
                <button @click="incrementQuantity">+</button>
              </div>

              <button v-if="user" class="add-to-cart" @click="addToCartFromModal">
                <i class="fas fa-shopping-cart"></i> Agregar al carrito
              </button>

            </div>
          </div>
        </div>
      </div>
    </div>

    <transition name="fade">
      <div v-if="showSuccess" class="success-notification">
        <div class="notification-content">
          <i class="fas fa-check-circle"></i>
          {{ successMessage }}
        </div>
      </div>
    </transition>

    <!-- Botón flotante admin -->
    <button v-if="user?.user_type === 'admin'" @click="abrirFormularioProducto" class="floating-admin-btn"
      data-admin-label="Nuevo producto" aria-label="Crear un producto nuevo">
      <i class="fas fa-plus" aria-hidden="true"></i>
    </button>

    <!-- Modal formulario de producto -->
    <div class="admin-modal" v-if="showAdminForm" @click.self="cerrarAdminForm" @keydown.esc="cerrarAdminForm">
      <div class="admin-modal-content" role="dialog" aria-modal="true" aria-labelledby="titulo-form-producto">
        <div class="admin-modal__header">
          <div>
            <h2 class="admin-modal__title" id="titulo-form-producto">
              {{ editingProduct ? 'Editar producto' : 'Nuevo producto' }}
            </h2>
            <p class="admin-modal__subtitle">
              {{ editingProduct ? 'Los cambios se aplican de inmediato en la tienda.' : 'El producto aparecerá en la tienda al guardarlo.' }}
            </p>
          </div>
          <button type="button" class="admin-modal__close" @click="cerrarAdminForm" aria-label="Cerrar formulario">
            <svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true">
              <path fill="currentColor"
                d="M19 6.41 17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12 19 6.41Z" />
            </svg>
          </button>
        </div>

        <form @submit.prevent="guardarProducto">
          <!-- Los campos llevan <label> de verdad: antes solo tenian
               placeholder, que desaparece al escribir (y los lectores de
               pantalla no lo anuncian como nombre del campo). -->
          <div class="admin-form-body">
            <div class="form-group form-group--full">
              <label for="prod-nombre">Nombre del producto <span class="required">*</span></label>
              <input id="prod-nombre" v-model="formProducto.name" placeholder="Ej. Guante de béisbol Wilson A2000"
                required>
            </div>

            <div class="form-group form-group--full">
              <label for="prod-desc">Descripción <span class="required">*</span></label>
              <textarea id="prod-desc" v-model="formProducto.description"
                placeholder="Material, tallas, para qué sirve..." required></textarea>
            </div>

            <div class="form-group">
              <label for="prod-precio">Precio (RD$) <span class="required">*</span></label>
              <input id="prod-precio" type="number" v-model="formProducto.price" placeholder="0.00" step="0.01" min="0"
                required>
            </div>

            <div class="form-group">
              <label for="prod-stock">Stock disponible <span class="required">*</span></label>
              <input id="prod-stock" type="number" v-model="formProducto.stock" placeholder="0" min="0" required>
            </div>

            <div class="form-group form-group--full">
              <label for="prod-cat">Categoría <span class="required">*</span></label>
              <select id="prod-cat" v-model="formProducto.category" required>
                <option value="">Seleccionar categoría</option>
                <option v-for="cat in categoriasFlat" :key="cat.valor" :value="cat.valor">{{ cat.texto }}</option>
              </select>
            </div>

            <div class="form-group form-group--full">
              <label for="prod-img">URL de la imagen <span class="required">*</span></label>
              <input id="prod-img" v-model="formProducto.image" placeholder="https://..." required>
              <span class="form-hint">Debe ser un enlace directo a la imagen (termina en .jpg, .png o .webp).</span>
            </div>

            <!-- Vista previa: evita guardar un producto con la URL mal escrita
                 y descubrirlo despues en la rejilla de la tienda. -->
            <div class="form-group form-group--full" v-if="formProducto.image">
              <label>Vista previa</label>
              <img :src="formProducto.image" alt="" class="admin-image-preview" @error="imagenPreviewFallo = true"
                @load="imagenPreviewFallo = false">
              <span class="form-hint" v-if="imagenPreviewFallo">
                No se pudo cargar esa imagen. Revisa el enlace antes de guardar.
              </span>
            </div>
          </div>

          <div class="form-actions">
            <button type="button" @click="cerrarAdminForm" class="btn-cancelar">Cancelar</button>
            <button type="submit" class="btn-guardar" :disabled="guardandoProducto">
              {{ guardandoProducto ? 'Guardando...' : (editingProduct ? 'Guardar cambios' : 'Crear producto') }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <ConfirmDialog :open="!!productoAEliminar" :busy="eliminandoProducto" title="Eliminar producto"
      confirm-label="Sí, eliminar" @cancel="productoAEliminar = null" @confirm="eliminarProducto">
      Se va a eliminar <strong>{{ productoAEliminar?.name }}</strong> de la tienda de forma permanente.
      Esta acción no se puede deshacer.
    </ConfirmDialog>

  </div>

  <!-- Burbuja de Mensajes Flotante -->
  <ChatBubbleComponent v-if="user" :user="user" />

  <Alert 
    v-if="openModal" 
    :key="alertKey"
    :type="alertType" 
    :message="alertMessage" 
    @close="openModal = false" 
  />  
</template>

<script>
import axios from 'axios';
import Navbar from '../navbarComponent.vue';
import ChatBubbleComponent from '../ChatBubbleComponent.vue';
import paginatorComponent from '@/components/paginatorComponent.vue';
import Alert from '../Alert.vue';
import ConfirmDialog from '../ui/ConfirmDialog.vue';

export default {
  name: 'TiendaComponent',
  components: {
    Navbar,
    ChatBubbleComponent,
    paginatorComponent,
    Alert,
    ConfirmDialog
  },
  data() {
    return {
      openModal: false,
      alertType: 'success', // 'success', 'error', 'alert'
      alertMessage: '',
      alertKey: 0,

      productos: [],
      showMobileFilters: false,
      mobileCategoryOpen: null,
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
      guardandoProducto: false,
      imagenPreviewFallo: false,
      // Producto que el admin pidio borrar; mientras no sea null, el dialogo
      // de confirmacion esta abierto.
      productoAEliminar: null,
      eliminandoProducto: false,
      formProducto: this.resetForm(),
      categoriasFlat: [],
      user: null,
      isLoading: true,
      user: null,
      showSuccess: false,
      successMessage: '',
      successTimer: null,
      currentPage: 1,
      itemsPerPage: 12,
    };
  },

  watch: {
    subcategoriaSeleccionada() {
      this.filtrarProductos();
    }
  },

  computed: {

    paginatedProducts() {
      const start = (this.currentPage - 1) * this.itemsPerPage;
      const end = start + this.itemsPerPage;
      return this.productosFiltrados.slice(start, end);
    },
  },
  methods: {

    toggleMobileCategory(index) {
      this.mobileCategoryOpen = this.mobileCategoryOpen === index ? null : index;
    },

    // Mantenemos el método existente para seleccionar subcategoría
    seleccionarSubcategoria(subcategoria) {
      this.subcategoriaSeleccionada = subcategoria;
      this.filtrarProductos();
    },


    getProducts() {
      this.isLoading = true;
      axios.get('/products')
        .then(response => {
          // Mapear productos como antes
          let products = response.data.products.map(product => ({
            ...product,
            categoria: product.category.toLowerCase(),
            name: product.name.toLowerCase(),
            description: product.description?.toLowerCase() || '',
            images: product.images || [product.image],
            oferta: !!product.oldPrice
          }));

          // Mezclar los productos aleatoriamente
          this.productos = this.shuffleArray(products);
          this.productosFiltrados = this.productos;
          this.$store.dispatch('cacheSection', { key: 'productos', data: this.productos });
        })
        .catch(error => {
          console.error('Error:', error);
          this.$notify({
            title: 'Error',
            text: 'No se pudieron cargar los productos',
            type: 'error'
          });
        })
        .finally(() => {
          this.isLoading = false;
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

        this.currentPage = 1;

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
      this.imagenPreviewFallo = false;
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

      // El token lo pone el interceptor de bootstrap.js; aqui solo hace falta
      // declarar el tipo de contenido.
      const config = { headers: { 'Content-Type': 'application/json' } };

      const esEdicion = !!this.editingProduct;
      const peticion = esEdicion
        ? axios.put(`/products/${this.editingProduct}`, requestData, config)
        : axios.post('/products', requestData, config);

      this.guardandoProducto = true;

      peticion
        .then(() => {
          this.getProducts();
          this.cerrarAdminForm();

          this.alertType = 'success';
          this.alertMessage = esEdicion
            ? 'Producto actualizado correctamente'
            : 'Producto creado correctamente';
          this.alertKey++;
          this.openModal = true;
        })
        .catch(error => {
          console.error('Error:', error.response?.data);

          this.alertType = 'error';
          this.alertMessage = error.response?.data?.message
            || (esEdicion ? 'Error al actualizar el producto' : 'Error al crear el producto');
          this.alertKey++;
          this.openModal = true;
        })
        .finally(() => {
          this.guardandoProducto = false;
        });
    },

    pedirConfirmacionBorrado(producto) {
      this.productoAEliminar = producto;
    },

    async eliminarProducto() {
      if (!this.productoAEliminar || this.eliminandoProducto) return;

      this.eliminandoProducto = true;
      const nombre = this.productoAEliminar.name;

      try {
        // Sin cabecera Authorization a mano: el interceptor de bootstrap.js ya
        // adjunta el token. La que habia aqui leia de localStorage, donde el
        // token nunca se guarda (vive en sessionStorage), asi que mandaba
        // "Bearer null" y solo funcionaba porque el interceptor la pisaba.
        await axios.delete(`/products/${this.productoAEliminar.id}`);

        this.productoAEliminar = null;
        this.getProducts();

        this.alertType = 'success';
        this.alertMessage = `"${nombre}" se eliminó de la tienda`;
        this.alertKey++;
        this.openModal = true;
      } catch (error) {
        console.error('Error eliminando producto:', error);
        this.alertType = 'error';
        this.alertMessage = 'No se pudo eliminar el producto. Inténtalo de nuevo.';
        this.alertKey++;
        this.openModal = true;
      } finally {
        this.eliminandoProducto = false;
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
    },

    async agregarAlCarrito(producto) {
      try {
        if (!this.user) {
        
          this.alertType = 'alert';
          this.alertMessage = 'Debes iniciar sesión para agregar productos al carrito';
          this.alertKey++;
          this.openModal = true;
          return;
        }

        const response = await axios.post('/cart/items', {
          item_type: 'product',
          item_id: producto.id,
          quantity: 1,
          user_id: this.user.id
        });

        // Mostrar mensaje de éxito
        this.successMessage = `¡${producto.name} agregado al carrito!`;
        this.showSuccess = true;

        // Ocultar después de 3 segundos
        clearTimeout(this.successTimer);
        this.successTimer = setTimeout(() => {
          this.showSuccess = false;
        }, 3000);

        window.dispatchEvent(new CustomEvent('cart-updated'));
      } catch (error) {
        console.error('Error al agregar al carrito:', error);
        
        this.alertType = 'error';
        // this.alertMessage = 'No se pudo agregar el producto al carrito';
        this.alertMessage = error.response?.data?.message == "No query results for model [App\\Models\\User]." ? 'Inicia sesión para añadir al carrito' :  'Error al agregar al carrito';
        this.alertKey++;
        this.openModal = true;
      }
    },

    addToCartFromModal() {
      if (this.productoSeleccionado) {
        this.agregarAlCarrito(this.productoSeleccionado);
        this.cerrarPopup();
      }
    },


    shuffleArray(array) {
      const newArray = [...array];
      for (let i = newArray.length - 1; i > 0; i--) {
        const j = Math.floor(Math.random() * (i + 1));
        [newArray[i], newArray[j]] = [newArray[j], newArray[i]];
      }
      return newArray;
    }

  },
  mounted() {
    const cachedProductos = this.$store.getters.sectionCache('productos');
    if (cachedProductos) {
      this.productos = cachedProductos;
      this.productosFiltrados = this.productos;
    } else {
      this.getProducts();
    }
    window.addEventListener('keyup', this.handleKeyup);
    this.user = JSON.parse(sessionStorage.getItem('user')) || {};
    this.generarCategoriasFlat()
    document.title = 'Tienda';
  },
  beforeUnmount() {
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

.no-products {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  text-align: center;
}

.center-wrapper {
  display: flex;
  justify-content: center;
  margin-bottom: 20px;
}

.empty-image {
  max-width: 150px;
  /* Ajusta el tamaño según necesites */
  height: auto;
  opacity: 0.7;
}

.no-products h3 {
  font-size: 1.5rem;
  color: #555;
  margin-bottom: 10px;
}

.no-products p {
  color: #777;
  font-size: 1rem;
  max-width: 400px;
  margin: 0 auto;
}


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



/* ==================== FILTROS MÓVILES ==================== */
.mobile-filter-btn {
  display: none;
  background: #2a4d69;
  color: white;
  padding: 14px 20px;
  border-radius: 10px;
  font-weight: 600;
  margin: 15px auto;
  width: 90%;
  text-align: center;
  cursor: pointer;
  box-shadow: 0 5px 15px rgba(0, 0, 0, 0.15);
  font-size: 1.1rem;
  border: none;
  transition: all 0.3s ease;
}

.mobile-filter-btn:hover {
  background: #3a6d99;
  transform: translateY(-2px);
}

.mobile-filter-btn i {
  margin-right: 10px;
  font-size: 1.2rem;
}

.mobile-filters-menu {
  display: none;
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: white;
  z-index: 1000;
  padding: 25px 20px;
  overflow-y: auto;
  box-shadow: 0 0 30px rgba(0, 0, 0, 0.2);
  transform: translateX(100%);
  transition: transform 0.4s ease;
}

.mobile-filters-menu.active {
  display: block;
  transform: translateX(0);
}

.mobile-filters-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 25px;
  padding-bottom: 15px;
  border-bottom: 2px solid #f0f0f0;
}

.mobile-filters-header h3 {
  font-size: 1.6rem;
  color: #2a4d69;
  font-weight: 700;
}

.close-mobile-filters {
  background: #e74c3c;
  color: white;
  border: none;
  width: 40px;
  height: 40px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  box-shadow: 0 3px 8px rgba(0, 0, 0, 0.15);
  transition: all 0.3s ease;
}

.close-mobile-filters:hover {
  background: #c0392b;
  transform: scale(1.1);
}

.close-mobile-filters i {
  font-size: 1.3rem;
}

.mobile-filters-content {
  padding: 10px 5px;
}

.mobile-filter-option {
  display: block;
  width: 100%;
  text-align: left;
  background: #f8f9fa;
  border: none;
  border-radius: 8px;
  padding: 14px 20px;
  margin-bottom: 12px;
  font-size: 1.1rem;
  font-weight: 500;
  color: #2a4d69;
  cursor: pointer;
  transition: all 0.2s ease;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
}

.mobile-filter-option:hover {
  background: #e3f2fd;
  transform: translateX(5px);
}

.mobile-category {
  margin-bottom: 15px;
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08);
}

.mobile-category-header {
  padding: 16px 20px;
  font-weight: 600;
  background: #2a4d69;
  color: white;
  display: flex;
  justify-content: space-between;
  align-items: center;
  cursor: pointer;
  transition: all 0.3s ease;
}

.mobile-category-header:hover {
  background: #3a6d99;
}

.mobile-category-header i {
  transition: transform 0.3s ease;
}

.mobile-subcategories {
  background: white;
  padding: 10px 0;
}

.mobile-subcategories button {
  display: block;
  width: 100%;
  text-align: left;
  padding: 14px 25px;
  border: none;
  background: none;
  cursor: pointer;
  font-size: 1rem;
  color: #333;
  transition: all 0.2s ease;
  position: relative;
}

.mobile-subcategories button:hover {
  background: #f0f7ff;
  color: #2a4d69;
}

.mobile-subcategories button:before {
  content: "";
  position: absolute;
  left: 15px;
  top: 50%;
  transform: translateY(-50%);
  width: 6px;
  height: 6px;
  background: #2a4d69;
  border-radius: 50%;
  opacity: 0;
  transition: opacity 0.2s ease;
}

.mobile-subcategories button:hover:before {
  opacity: 1;
}

/* Animación para el icono de flecha */
.fa-rotate-180 {
  transform: rotate(180deg);
}

/* Responsive para filtros */
@media (max-width: 768px) {
  .filter-tabs {
    display: none;
  }

  .mobile-filter-btn {
    display: block;
  }
}

/* Ajustes para diferentes resoluciones */
@media (max-width: 480px) {
  .mobile-filters-menu {
    padding: 20px 15px;
  }

  .mobile-filters-header h3 {
    font-size: 1.4rem;
  }

  .mobile-filter-option {
    padding: 12px 15px;
    font-size: 1rem;
  }

  .mobile-category-header {
    padding: 14px 15px;
    font-size: 1.1rem;
  }

  .mobile-subcategories button {
    padding: 12px 20px;
    font-size: 0.95rem;
  }
}

@media (min-width: 481px) and (max-width: 720px) {
  .mobile-filters-menu {
    padding: 25px;
  }
}


@media (max-width: 768px) {
  .filters-section {
    display: none;
  }

  .mobile-filter-btn {
    display: block;
  }
}


.product-card:hover .quick-view-btn {
  transform: translateX(-50%) translateY(0);
}

@media (max-width: 720px) {
  .modal-grid {
    flex-direction: column;
  }

  .quick-view-btn {
    position: absolute;
    bottom: 20px;
    left: 5%;
    background: rgba(42, 77, 105, 0.9);
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 30px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s ease;
    z-index: 2;
    white-space: nowrap;
  }

  .modal-images {
    width: 100%;
    margin-right: 0;
    margin-bottom: 20px;
  }

  .modal-details {
    width: 100%;
  }

  .product-title {
    font-size: 1.5rem;
  }

  .current-price {
    font-size: 1.4rem;
  }

  .product-description {
    font-size: 0.95rem;
  }

  .quantity-selector {
    margin-bottom: 15px;
  }

  .add-to-cart {
    width: 100%;
  }
}

@media (max-width: 480px) {
  .store-title {
    font-size: 1.6rem;
  }

  .store-subtitle {
    font-size: 0.95rem;
  }

  .product-card {
    margin-bottom: 20px;
  }

  .product-name {
    font-size: 1.1rem;
    height: auto;
  }

  .product-price {
    font-size: 1.2rem;
  }

  .add-to-cart-btn {
    padding: 8px 12px;
    font-size: 0.9rem;
  }

  .modal-content {
    width: 95%;
    padding: 15px;
  }

  .main-image {
    height: 200px;
  }

  .quantity-selector button {
    width: 36px;
    height: 36px;
    font-size: 16px;
  }

  .quantity-selector span {
    font-size: 16px;
    padding: 0 12px;
  }
}

@media (min-width: 481px) and (max-width: 720px) {
  .products-grid {
    grid-template-columns: repeat(2, 1fr);
  }


  .product-image-container {
    height: 200px;
  }

  .modal-content {
    width: 90%;
  }

}

button,
input,
select,
textarea {
  font-size: 16px !important;
}
</style>