<template>
  <div class="tienda-page">
    <h1 class="tienda-title">Bienvenido a la Tienda</h1>

    <!-- Barra de búsqueda -->
    <div class="search-bar">
      <input
        type="text"
        v-model="busqueda"
        placeholder="Buscar productos..."
        @input="filtrarPorBusqueda"
        class="search-input"
      />
    </div>

    <!-- Filtros -->
    <div class="filters">
      <select v-model="categoriaSeleccionada" class="filter-select">
        <option value="">Filtrar por categoría</option>
        <option value="futbol">Fútbol</option>
        <option value="basketball">Baloncesto</option>
        <option value="tenis">Tenis</option>
        <option value="ropa-hombre">Ropa Hombre</option>
        <option value="ropa-mujer">Ropa Mujer</option>
        <option value="ropa-ninos">Ropa Niños</option>
        <option value="proteinas">Proteínas</option>
        <option value="barras">Barras energéticas</option>
        <option value="accesorios">Accesorios</option>
      </select>
      <button class="btn-filtro" @click="filtrarProductos">Aplicar filtro</button>
    </div>

    <!-- Productos -->
    <div class="productos-container">
      <div
        v-for="producto in productosFiltrados"
        :key="producto.id"
        class="producto-card"
      >
        <img :src="producto.imagen" alt="Imagen del producto" class="producto-imagen" />
        <h3 class="producto-nombre">{{ producto.nombre }}</h3>
        <p class="producto-precio">{{ producto.precio }}</p>
        <button @click="agregarAlCarrito(producto)" class="btn btn-agregar">Agregar al carrito</button>
      </div>
    </div>

    <!-- Carrito de Compras -->
    <div v-if="carrito.length > 0" class="carrito">
      <button @click="mostrarCarrito" class="btn-carrito">
        Ver carrito ({{ carrito.length }})
      </button>
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
        { id: 1, nombre: 'Balón de Fútbol', precio: '$30', categoria: 'futbol', imagen: '/imagenes/ball.jpg' },
        { id: 2, nombre: 'Raqueta de Tenis', precio: '$50', categoria: 'tenis', imagen: '/imagenes/racket.jpg' },
        { id: 3, nombre: 'Zapatillas de Running', precio: '$70', categoria: 'ropa-hombre', imagen: '/imagenes/sneakers.jpg' },
        { id: 4, nombre: 'Camiseta Deportiva Mujer', precio: '$25', categoria: 'ropa-mujer', imagen: '/imagenes/tshirt-woman.jpg' },
        { id: 5, nombre: 'Proteína en Polvo', precio: '$45', categoria: 'proteinas', imagen: '/imagenes/protein.jpg' },
        { id: 6, nombre: 'Barras Energéticas', precio: '$15', categoria: 'barras', imagen: '/imagenes/protein-bars.jpg' },
        { id: 7, nombre: 'Balón de Basketball', precio: '$40', categoria: 'basketball', imagen: '/imagenes/basketball.jpg' },
        { id: 8, nombre: 'Camiseta Deportiva Hombre', precio: '$35', categoria: 'ropa-hombre', imagen: '/imagenes/soccer-shirt.jpg' },
        { id: 9, nombre: 'Camiseta Deportiva Niño', precio: '$20', categoria: 'ropa-ninos', imagen: '/imagenes/tshirt-kids.jpg' },
        { id: 10, nombre: 'Gorra Deportiva', precio: '$20', categoria: 'accesorios', imagen: '/imagenes/cap.jpg' },
      ],
      carrito: [],
      categoriaSeleccionada: '',
      busqueda: '',
      productoAgregado: null,
      productosFiltrados: [],
    };
  },
  created() {
    this.productosFiltrados = this.productos;
  },
  methods: {
    agregarAlCarrito(producto) {
      this.carrito.push(producto);
      this.productoAgregado = producto;
      setTimeout(() => {
        this.productoAgregado = null;
      }, 3000);
    },
    mostrarCarrito() {
      console.log('Carrito:', this.carrito);
    },
    filtrarProductos() {
      let productosFiltrados = this.productos;
      
      if (this.categoriaSeleccionada) {
        productosFiltrados = productosFiltrados.filter(producto => producto.categoria === this.categoriaSeleccionada);
      }
      
      if (this.busqueda) {
        productosFiltrados = productosFiltrados.filter(producto =>
          producto.nombre.toLowerCase().includes(this.busqueda.toLowerCase())
        );
      }
      
      this.productosFiltrados = productosFiltrados;
    },
    filtrarPorBusqueda() {
      this.filtrarProductos();
    }
  },
  watch: {
    categoriaSeleccionada() {
      this.filtrarProductos();
    }
  }
};
</script>

<style scoped>
/* Página de la tienda */
.tienda-page {
  font-family: 'Arial', sans-serif;
  background-color: #f8f9fa;
  padding: 40px 20px;
  display: flex;
  flex-direction: column;
  align-items: center;
}

.tienda-title {
  font-size: 2.8rem;
  color: #333;
  margin-bottom: 40px;
  font-weight: 600;
}

/* Barra de búsqueda */
.search-bar {
  margin-bottom: 25px;
  display: flex;
  justify-content: center;
}

.search-input {
  width: 350px;
  padding: 12px 20px;
  font-size: 1.1rem;
  border-radius: 30px;
  border: 2px solid #007bff;
  outline: none;
  transition: border-color 0.3s ease;
}

.search-input:focus {
  border-color: #0056b3;
}

/* Filtros */
.filters {
  display: flex;
  gap: 20px;
  margin-bottom: 30px;
  align-items: center;
}

.filter-select {
  padding: 12px 20px;
  font-size: 1.1rem;
  border-radius: 30px;
  border: 2px solid #007bff;
  background-color: #fff;
}

.btn-filtro {
  padding: 12px 25px;
  background-color: #007bff;
  color: white;
  font-size: 1.1rem;
  border: none;
  border-radius: 30px;
  cursor: pointer;
  transition: background-color 0.3s ease;
}

.btn-filtro:hover {
  background-color: #0056b3;
}

/* Contenedor de productos */
.productos-container {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
  gap: 25px;
  max-width: 1200px;
  width: 100%;
  margin-top: 20px;
}

/* Tarjeta de producto */
.producto-card {
  background-color: #fff;
  border-radius: 15px;
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
  overflow: hidden;
  transition: transform 0.3s ease-in-out, box-shadow 0.3s ease-in-out;
  text-align: center;
  padding: 25px;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  align-items: center;
}

.producto-card:hover {
  transform: translateY(-10px);
  box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
}

/* Imagen del producto */
.producto-imagen {
  width: 100%;
  height: 200px;
  object-fit: cover;
  border-radius: 10px;
  margin-bottom: 20px;
  transition: transform 0.3s ease;
}

.producto-card:hover .producto-imagen {
  transform: scale(1.1);
}

/* Nombre y precio del producto */
.producto-nombre {
  font-size: 1.3rem;
  color: #333;
  margin-bottom: 15px;
  font-weight: 500;
}

.producto-precio {
  font-size: 1.2rem;
  color: #28a745;
  margin-bottom: 20px;
  font-weight: 600;
}

/* Botón de agregar al carrito */
.btn {
  padding: 12px 25px;
  font-size: 1.1rem;
  border-radius: 25px;
  border: none;
  cursor: pointer;
  transition: background-color 0.3s ease, transform 0.3s ease;
}

.btn-agregar {
  background-color: #28a745;
  color: white;
}

.btn-agregar:hover {
  background-color: #218838;
  transform: scale(1.05);
}

/* Notificación */
.notificacion {
  position: fixed;
  bottom: 20px;
  right: 20px;
  background-color: #28a745;
  color: white;
  padding: 15px;
  border-radius: 10px;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
  font-size: 1rem;
  display: flex;
  align-items: center;
  justify-content: center;
}

.carrito {
  margin-top: 30px;
}

.btn-carrito {
  padding: 15px 30px;
  background-color: #ffc107;
  color: white;
  font-size: 1.2rem;
  border: none;
  border-radius: 30px;
  cursor: pointer;
  transition: background-color 0.3s ease;
}

.btn-carrito:hover {
  background-color: #e0a800;
}
</style>
