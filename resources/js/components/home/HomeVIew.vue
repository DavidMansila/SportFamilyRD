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
  
      <!-- Banner -->
      <div class="banner">
        <h1 class="banner-title">Bienvenido a SportFamilyRD</h1>
      </div>
  

      <!-- Seccion de Categorias -->
      <section class="section">
        <h2 class="section-title">Categorias</h2>
        <div class="cards">
          <div v-for="category in categories" :key="category.name" class="card">
            <img :src="category.image" :alt="category.name" class="card-img" />
            <p class="card-title">{{ category.name }}</p>
          </div>
        </div>
      </section>
  

      <!-- Seccion de productos -->
      <section class="section">
        <h2 class="section-title">Productos Favoritos</h2>
        <div class="cards">
          <div v-for="product in products" :key="product.name" class="card">
            <img :src="product.image" :alt="product.name" class="card-img" />
            <p class="card-title">{{ product.name }}</p>
          </div>
        </div>
      </section>
  

      <!-- Seccion de temas de conversacion -->
      <section class="section">
        <h2 class="section-title">Temas populares</h2>
        <div class="cards">
          <div v-for="thread in threads" :key="thread.title" class="card">
            <img :src="thread.image" :alt="thread.title" class="card-img" />
            <p class="card-title">{{ thread.title }}</p>
          </div>
        </div>
      </section>
  


      <div class="cta">
        <p class="cta-text">Nuevo en SportFamilyRD hoy</p>
        <p class="cta-subtext">Unete a nuestra comunidad de deportes</p>
      </div>
    </div>
  </template>
  

  <script>
  export default {
    data() {
      return {
        login:true, 

        categories: [
          { name: "Futbol", image: "/imagenes/football.jpg" },
          { name: "Basketball", image: "/imagenes/basketball.jpg" },
          { name: "Baseball", image: "/imagenes/baseball.jpg" },
          { name: "Volleyball", image: "/imagenes/voleyball.jpg" }
        ],
        products: [
          { name: "Consumibles", image: "/imagenes/consumibles.jpg" },
          { name: "Ropa", image: "/imagenes/ropa.jpg" },
          { name: "Artículos de Juego", image: "/imagenes/articulos.jpg" }
        ],
        threads: [
          { title: "Surf", image: "/imagenes/surf_thread.jpg" },
          { title: "Escalada", image: "/imagenes/escalada_thread.jpg" },
          { title: "Levantamiento de pesas", image: "/imagenes/pesas.jpg" }
        ]
};
    }
  };
  </script>
  

  <style scoped>
  @import '../../../scss/Home/home.scss';
  </style>