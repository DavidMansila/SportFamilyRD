<template>
  <div class="tienda-page">

<<<<<<< HEAD
<<<<<<< HEAD
    <!-- Nav Bar -->
      <nav class="navbar">
=======
    <!-- Barra de navegación -->
      <nav class="navbar3">
>>>>>>> 0607206 (Mas cambios en las paginas, mas front)
=======
    <!-- Nav Bar -->
      <nav class="navbar">
>>>>>>> 0d3bd1c (Arreglo de las vistas y doble password en registro)
        <div class="logo-container">
        <a href="/" class="logo-container">
          <img src="/imagenes/logo.png" alt="SportFamilyRD Logo" class="logo"/>
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
            <a href="/Settings">
                  <button class="auth-btn">Ajustes</button>
            </a>
            <a href="/Login">
                 <button class="auth-btn">Login</button>
            </a>
        </div>
      </nav>

    <!-- Título de la tienda -->
<<<<<<< HEAD
<<<<<<< HEAD
    <h2 class="tienda-title">Bienvenido a nuestra Tienda</h2>
=======
    <h1 class="tienda-title">Bienvenido a nuestra Tienda</h1>
>>>>>>> 0607206 (Mas cambios en las paginas, mas front)
=======
    <h2 class="tienda-title">Bienvenido a nuestra Tienda</h2>
>>>>>>> 0d3bd1c (Arreglo de las vistas y doble password en registro)

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
        { id: 11, nombre: 'Balón de Fútbol Profesional', precio: '$60', categoria: 'futbol', imagen: '/imagenes/professional-ball.jpg' },
        { id: 12, nombre: 'Zapatillas de Baloncesto', precio: '$90', categoria: 'basketball', imagen: '/imagenes/basket-shoes.jpg' },
        { id: 13, nombre: 'Short Deportivo', precio: '$25', categoria: 'ropa-hombre', imagen: '/imagenes/sport-shorts.jpg' },
        { id: 14, nombre: 'Camiseta Deportiva Hombre', precio: '$30', categoria: 'ropa-hombre', imagen: '/imagenes/sport-shirt-man.jpg' },
        { id: 15, nombre: 'Raqueta Profesional de Tenis', precio: '$150', categoria: 'tenis', imagen: '/imagenes/professional-racket.jpg' },
        { id: 16, nombre: 'Camiseta de Baloncesto', precio: '$45', categoria: 'basketball', imagen: '/imagenes/basketball-shirt.jpg' },
        { id: 17, nombre: 'Pantalones de Yoga', precio: '$28', categoria: 'ropa-mujer', imagen: '/imagenes/yoga-pants.jpg' },
        { id: 18, nombre: 'Gorra Deportiva Estilo', precio: '$18', categoria: 'accesorios', imagen: '/imagenes/stylish-cap.jpg' },
        { id: 19, nombre: 'Pelota de Tenis', precio: '$12', categoria: 'tenis', imagen: '/imagenes/tennis-ball.jpg' },
        { id: 20, nombre: 'Guantes de Fútbol', precio: '$40', categoria: 'futbol', imagen: '/imagenes/football-gloves.jpg' },
        { id: 21, nombre: 'Proteínas Vegetales', precio: '$60', categoria: 'proteinas', imagen: '/imagenes/vegan-protein.jpg' },
        { id: 22, nombre: 'Barras de Proteína', precio: '$18', categoria: 'barras', imagen: '/imagenes/protein-bars-2.jpg' },
        { id: 23, nombre: 'Balón de Fútbol de Entrenamiento', precio: '$50', categoria: 'futbol', imagen: '/imagenes/training-ball.jpg' },
        { id: 24, nombre: 'Camiseta Deportiva para Niño', precio: '$15', categoria: 'ropa-ninos', imagen: '/imagenes/kid-shirt.jpg' },
        { id: 25, nombre: 'Zapatillas de Tenis para Mujer', precio: '$80', categoria: 'ropa-mujer', imagen: '/imagenes/tennis-women.jpg' },
        { id: 26, nombre: 'Chaqueta Deportiva', precio: '$55', categoria: 'ropa-hombre', imagen: '/imagenes/sport-jacket.jpg' },
        { id: 27, nombre: 'Bolsa Deportiva', precio: '$30', categoria: 'accesorios', imagen: '/imagenes/sport-bag.jpg' },
        { id: 28, nombre: 'Botellas de Agua', precio: '$20', categoria: 'accesorios', imagen: '/imagenes/water-bottle.jpg' },
        { id: 29, nombre: 'Mochila Deportiva', precio: '$50', categoria: 'accesorios', imagen: '/imagenes/sport-backpack.jpg' },
        { id: 30, nombre: 'Banda de Resistencia', precio: '$10', categoria: 'accesorios', imagen: '/imagenes/resistance-band.jpg' }
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
  }
};
</script>



<style scoped>

<<<<<<< HEAD
<<<<<<< HEAD
=======
>>>>>>> 0d3bd1c (Arreglo de las vistas y doble password en registro)
body {
    font-family: Arial, sans-serif;
    margin: 0;
    padding: 0;
  }
<<<<<<< HEAD

  
  /* Navbar */
  .navbar {
    background: linear-gradient(to right, #000000, #17A2B8);
    padding: 1rem 2rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
}


.logo {
    width: 50px;
    height: 50px;
}

.container {
    margin: 0 auto;
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
=======
.tienda-page {
  font-family: Arial, sans-serif;
>>>>>>> 0607206 (Mas cambios en las paginas, mas front)
}
=======
>>>>>>> 0d3bd1c (Arreglo de las vistas y doble password en registro)

  
  /* Navbar */
  .navbar {
    background: linear-gradient(to right, #000000, #17A2B8);
    padding: 1rem 2rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
}


.logo {
    width: 50px;
    height: 50px;
}

.container {
    margin: 0 auto;
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

.tienda-title {
  text-align: center;
  margin-top: 20px;
  font-size: 24px;
}

.search-bar {
  text-align: center;
  margin-top: 20px;
}

.search-input {
  width: 80%;
  padding: 10px;
  font-size: 16px;
}

.filters {
  display: flex;
  justify-content: center;
  margin-top: 20px;
}

.filter-select {
  padding: 10px;
  margin-right: 10px;
  font-size: 16px;
}

.btn-filtro {
  padding: 10px;
  background-color: #333;
  color: white;
  border: none;
  cursor: pointer;
}

.productos-container {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 20px;
  margin-top: 30px;
}

.producto-card {
  background-color: #f5f5f5;
  padding: 20px;
  text-align: center;
  border-radius: 10px;
}

.producto-imagen {
  width: 100%;
  max-width: 150px;
  margin-bottom: 10px;
}

.producto-nombre {
  font-size: 18px;
  margin-bottom: 10px;
}

.producto-precio {
  font-size: 16px;
  margin-bottom: 10px;
}

.btn-agregar {
  padding: 10px;
  background-color: #333;
  color: white;
  border: none;
  cursor: pointer;
}

.carrito {
  text-align: center;
  margin-top: 20px;
}

.notificacion {
  position: fixed;
  bottom: 20px;
  left: 50%;
  transform: translateX(-50%);
  background-color: #333;
  color: white;
  padding: 10px;
  border-radius: 5px;
  display: none;
}
</style>
