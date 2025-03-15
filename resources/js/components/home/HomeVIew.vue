<template>
    <div>
      <!-- Navbar -->
      <nav class="navbar">
        <div class="logo-container">
        <a href="/" class="logo-container">
          <img src="/imagenes/logo.png" alt="SportFamilyRD Logo" class="logo"/>
        </a>
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
            <a :href=" login ? '/Login' : '/Logout' ">
                <button class="auth-btn">{{ login ? "Login" : "logout" }}</button>
            </a>
        </div>
      </nav>
  
      <!-- Banner -->
      <div class="banner">
        <h1 class="banner-title">Bienvenido a SportFamilyRD</h1>
      </div>
  
      <!-- Categories Section -->
      <section class="section">
        <h2 class="section-title">Categorias</h2>
        <div class="cards">
          <div v-for="category in categories" :key="category.name" class="card">
            <img :src="category.image" :alt="category.name" class="card-img" />
            <p class="card-title">{{ category.name }}</p>
          </div>
        </div>
      </section>
  
      <!-- Favorite Products Section -->
      <section class="section">
        <h2 class="section-title">Productos Favoritos</h2>
        <div class="cards">
          <div v-for="product in products" :key="product.name" class="card">
            <img :src="product.image" :alt="product.name" class="card-img" />
            <p class="card-title">{{ product.name }}</p>
          </div>
        </div>
      </section>
  
      <!-- Featured Threads Section -->
      <section class="section">
        <h2 class="section-title">Temas populares</h2>
        <div class="cards">
          <div v-for="thread in threads" :key="thread.title" class="card">
            <img :src="thread.image" :alt="thread.title" class="card-img" />
            <p class="card-title">{{ thread.title }}</p>
          </div>
        </div>
      </section>
  
      <!-- Call to Action Section -->
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
  /* General styles */
  body {
    font-family: Arial, sans-serif;
    margin: 0;
    padding: 0;
  }


/* Navbar */
.navbar {
  background: linear-gradient(to right, #000000, #ff3149);
  padding: 1rem 2rem;
  display: flex;
  justify-content: space-between; /* Distribuye el espacio entre los elementos */
  align-items: center;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

/* Logo a la izquierda */
.logo-container {
  display: flex;
  align-items: center;
}

.logo {
  width: 200px; /* Tamaño del logo */
  height: 65px;
}

/* Enlaces en el centro */
.nav-links {
  display: flex;
  gap: 2rem;
  flex-grow: 1; /* Ocupa el espacio disponible */
  justify-content: center; /* Centra los enlaces */
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

/* Botones a la derecha */
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


  
  /* Banner */
  .banner {
    background-image: url("/imagenes/estadio.jpg");
    background-size: cover;
    background-position: center;
    height: 25rem;
    display: flex;
    justify-content: center;
    align-items: flex-end;
    padding: 2rem;
  }
  
  .banner-title {
  color: rgb(255, 255, 255); /* Texto blanco para buen contraste */
  font-size: 3rem;
  font-weight: bold;
  text-align: center;
  position: relative; /* Necesario para la capa del fondo */
  z-index: 1; /* Asegura que el texto esté por encima */
  text-shadow: 2px 2px 12px rgba(0, 0, 0, 0.6); /* Sombra suave para mejorar legibilidad */
  padding: 20px; /* Espaciado alrededor del texto */
}

.banner-title::before {
  content: "";
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  backdrop-filter: blur(12px); /* Difumina el fondo */
  z-index: -1; /* Coloca el fondo detrás del texto */
  border-radius: 15px; /* Bordes redondeados para suavizar el fondo */
  clip-path: ellipse(75% 50% at 50% 50%); /* Forma fluida en el fondo */
}

  
  /* Section Styles */
  .section {
    margin-top: 4rem;
    text-align: center;
  }
  
  .section-title {
    font-size: 2rem;
    font-weight: 600;
    color: #2d3748;
  }
  
  .cards {
    display: flex;
    justify-content: center;
    gap: 2rem;
    flex-wrap: wrap;
  }
  
  .card {
    background-color: white;
    border-radius: 0.75rem;
    width: 18rem;
    box-shadow: 0 20px 30px rgba(0, 0, 0, 0.1);
    overflow: hidden;
    text-align: center;
  }
  
  .card-img {
    width: 100%;
    height: 12rem;
    object-fit: cover;
  }
  
  .card-title {
    padding: 1rem;
    font-size: 1.25rem;
    color: #2d3748;
  }
  
  /* Call to Action */
  .cta {
    margin-top: 5rem;
    text-align: center;
  }
  
  .cta-text {
    font-size: 2rem;
    color: #2d3748;
  }
  
  .cta-subtext {
    font-size: 1.125rem;
    color: #718096;
  }

.brand {
    display: flex;
    align-items: center;
    gap: 1rem;
}


  </style>
  