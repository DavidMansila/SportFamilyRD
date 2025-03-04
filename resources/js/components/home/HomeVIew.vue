<template>
    <div>
      <!-- Navbar -->
      <nav class="navbar">
        <div class ="logo-container"> <img src="/imagenes/logo.png" alt="SportFamilyRD Logo" class="logo" />
        <h1>SportFamilyRD</h1></div>
        <div class="nav-links">
          <router-link to="/Noticias" class="nav-link">Noticias</router-link>
          <router-link to="/Calendario" class="nav-link">Calendario</router-link>
          <router-link to="/Tienda" class="nav-link">Tienda</router-link>
          <router-link to="/Entrenadores" class="nav-link">Entrenadores</router-link>
          <router-link to="/Foro" class="nav-link">Foro</router-link>
        </div>
        <div class="auth-buttons">
          <button class="auth-btn">Settings</button>
          <button class="auth-btn">Login</button>
        </div>
      </nav>
  
      <!-- Banner -->
      <div class="banner">
        <h1 class="banner-title">Welcome to SportFamilyRD</h1>
      </div>
  
      <!-- Categories Section -->
      <section class="section">
        <h2 class="section-title">Categories</h2>
        <div class="cards">
          <div v-for="category in categories" :key="category.name" class="card">
            <img :src="category.image" :alt="category.name" class="card-img" />
            <p class="card-title">{{ category.name }}</p>
          </div>
        </div>
      </section>
  
      <!-- Favorite Products Section -->
      <section class="section">
        <h2 class="section-title">Favorite Products</h2>
        <div class="cards">
          <div v-for="product in products" :key="product.name" class="card">
            <img :src="product.image" :alt="product.name" class="card-img" />
            <p class="card-title">{{ product.name }}</p>
          </div>
        </div>
      </section>
  
      <!-- Featured Threads Section -->
      <section class="section">
        <h2 class="section-title">Featured Threads</h2>
        <div class="cards">
          <div v-for="thread in threads" :key="thread.title" class="card">
            <img :src="thread.image" :alt="thread.title" class="card-img" />
            <p class="card-title">{{ thread.title }}</p>
          </div>
        </div>
      </section>
  
      <!-- Call to Action Section -->
      <div class="cta">
        <p class="cta-text">New in SportFamilyRD Today</p>
        <p class="cta-subtext">Join our community of sports enthusiasts.</p>
      </div>
    </div>
  </template>
  
  <script>
  export default {
    data() {
      return {
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
  
  <style>
  /* General styles */
  body {
    font-family: Arial, sans-serif;
    margin: 0;
    padding: 0;
  }

  h1 {
    font-size: 2rem;
    font-weight: bold;
    color: white;
  }

  .logo-container {
    display: flex;
    gap: 1rem;
    flex-direction: row;
  }
  
  .container {
    margin: 0 auto;
  }
  
  /* Navbar */
  .navbar {
    background: linear-gradient(to right, #000000, #ff3149);
    padding: 1rem 2rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }
  
  .brand {
    font-size: 2rem;
    font-weight: bold;
    color: white;
  }
  
  .nav-links {
    display: flex;
    gap: 2rem;
  }
  
  .nav-link {
    color: white;
    text-decoration: none;
    transition: color 0.3s;
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
    padding: 0.5rem 1rem;
    border-radius: 0.375rem;
    cursor: pointer;
    transition: all 0.3s;
  }
  
  .auth-btn:hover {
    background-color: white;
    color: #3b82f6;
  }
  
  /* Banner */
  .banner {
    background-image: url("/imagenes/stadium.jpg");
    background-size: cover;
    background-position: center;
    height: 25rem;
    display: flex;
    justify-content: center;
    align-items: flex-end;
    padding: 2rem;
  }
  
  .banner-title {
    color: rgb(4, 4, 4);
    font-size: 3rem;
    font-weight: bold;
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

  .navbar {
    background: linear-gradient(to right, #000000, #ff3149);
    padding: 1rem 2rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.brand {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.logo {
    width: 50px;
    height: 50px;
}


  </style>
  