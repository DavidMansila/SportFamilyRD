<template>
  <div class="noticias-page">
    <!-- NavBar -->
    <nav class="navbar1">
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

    <div class="container">
      <h1 class="page-title">Sports News</h1>

      <div v-for="noticia in noticias" :key="noticia.id" class="noticia-card">
        <div class="noticia-image">
          <img :src="noticia.imagen" alt="Imagen de noticia" class="image" />
        </div>
        <div class="noticia-content">
          <h3 class="noticia-title">{{ noticia.titulo }}</h3>
          <p class="noticia-description">{{ noticia.descripcion }}</p>
          <p class="noticia-source">{{ noticia.fuente }} · {{ noticia.tiempo }} min read</p>
          <a :href="'/noticia/' + noticia.id" class="read-more">Read more</a>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'NoticiasComponent',
  data() {
    return {
      noticias: [
        { 
          id: 1, 
          titulo: 'Olympic athletes are fighting for their rights to protest', 
          descripcion: 'The rules have changed but the fight is far from over.', 
          imagen: 'https://via.placeholder.com/600x300',
          fuente: 'Vox',
          tiempo: '4'
        },
        { 
          id: 2, 
          titulo: 'What we learned from the first week of the NBA playoffs', 
          descripcion: 'The Lakers are out and the league is wide open.', 
          imagen: 'https://via.placeholder.com/600x300',
          fuente: 'ESPN',
          tiempo: '6'
        },
        { 
          id: 3, 
          titulo: 'The rise of women’s skateboarding', 
          descripcion: 'As the sport makes its Olympic debut, female skaters are taking center stage.', 
          imagen: 'https://via.placeholder.com/600x300',
          fuente: 'The Guardian',
          tiempo: '5'
        }
      ]
    };
  }
};
</script>

<<<<<<< HEAD
<style scoped>

body {
    font-family: Arial, sans-serif;
    margin: 0;
    padding: 0;
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
  

=======
<style scoped> 
>>>>>>> 0607206 (Mas cambios en las paginas, mas front)
/* ------------------- ESTILOS DEL NAVBAR ------------------- */
/* Navbar */
.navbar1 {
    background: linear-gradient(to right, #000000, #007BFF);
    padding: 1rem 2rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

<<<<<<< HEAD
  .container {
    margin: 0 auto;
  }
=======
.logo-container {
    display: flex;
    align-items: center;
    gap: 1rem;
}
>>>>>>> 0607206 (Mas cambios en las paginas, mas front)

.logo {
    width: 50px;
    height: 50px;
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
  

/* ------------------- ESTILOS DE NOTICIAS ------------------- */
.noticias-page {
  font-family: 'Inter', sans-serif;
  background-color: #f8f9fa;
  min-height: 100vh;
  padding-bottom: 40px;
}

.container {
  max-width: 1100px;
  margin: 0 auto;
  padding: 20px;
}

.page-title {
  font-size: 2.5rem;
  color: #333;
  margin-bottom: 30px;
  font-weight: bold;
  text-align: left;
}

.noticia-card {
  display: flex;
  background: #fff;
  border-radius: 15px;
  margin: 20px 0;
  padding: 20px;
  box-shadow: 0px 8px 16px rgba(0, 0, 0, 0.1);
  transition: transform 0.3s ease-in-out;
}

.noticia-card:hover {
  transform: translateY(-5px);
  box-shadow: 0px 12px 20px rgba(0, 0, 0, 0.2);
}

.noticia-image {
  flex: 1;
  max-width: 250px;
  margin-right: 20px;
}

.image {
  width: 100%;
  height: auto;
  border-radius: 10px;
  object-fit: cover;
}

.noticia-content {
  flex: 2;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}

.noticia-title {
  font-size: 1.6rem;
  color: #222;
  font-weight: bold;
  margin-bottom: 10px;
}

.noticia-description {
  font-size: 1.1rem;
  color: #555;
  margin-bottom: 15px;
}

.noticia-source {
  font-size: 0.9rem;
  color: #777;
  margin-bottom: 15px;
}

.read-more {
  align-self: flex-start;
  padding: 10px 15px;
  background: #007bff;
  color: white;
  font-weight: bold;
  text-decoration: none;
  border-radius: 8px;
  transition: background 0.3s;
}

.read-more:hover {
  background: #0056b3;
}

/* Responsive Design */
@media (max-width: 768px) {
  .navbar {
    flex-direction: column;
    text-align: center;
  }

  .nav-links {
    flex-direction: column;
    gap: 10px;
  }

  .auth-buttons {
    margin-top: 10px;
  }

  .noticia-card {
    flex-direction: column;
    text-align: center;
  }

  .noticia-image {
    max-width: 100%;
    margin-right: 0;
    margin-bottom: 15px;
  }

  .read-more {
    align-self: center;
  }
}
</style>
