<template>
  <div class="home-container">

    <!-- Navbar -->
    <Navbar/>


    <div class="hero-banner">
      <div class="hero-content">
        <h1 class="hero-title">Bienvenido a SportFamilyRD</h1>
        <p class="hero-subtitle">Tu comunidad deportiva en República Dominicana</p>
        <div class="hero-cta">
          <router-link to="/Directorio" class="cta-button">
            Directorio de Deportes
          </router-link>
        </div>
      </div>
    </div>

    <section class="category-section">
      <div class="section-header">
        <h2 class="section-title">Explora Categorías</h2>
        <p class="section-description">Descubre los deportes más populares</p>
      </div>
      <div class="category-grid">
        <a v-for="category in categories" :key="category.name" class="category-card">
          <div class="card-image">
            <img :src="category.image" :alt="category.name" />
            <div class="card-overlay"></div>
          </div>
          <div class="card-content">
            <h3>{{ category.name }}</h3>
          </div>
        </a>
      </div>
    </section>

    <section class="featured-section">
      <div class="section-header">
        <h2 class="section-title">Productos Destacados</h2>
        <p class="section-description">Lo mejor para tu rendimiento deportivo</p>
      </div>
      <div class="product-carousel">
        <div v-for="product in products" :key="product.name" class="product-card">
          <div class="product-badge" v-if="product.featured">Destacado</div>
          <img :src="product.image" :alt="product.name" class="product-image" />
          <div class="product-info">
            <h3>{{ product.name }}</h3>
            <div class="product-price">${{ product.price }}</div>
            <button class="add-to-cart">Añadir al carrito</button>
          </div>
        </div>
      </div>
    </section>

    <section class="community-section">
      <div class="community-content">
        <h2>Únete a nuestra comunidad</h2>
        <p>Conecta con otros entusiastas del deporte en nuestro foro</p>
        <div class="thread-preview">
          <a v-for="thread in threads" :key="thread.title" class="thread-card">
            <img :src="thread.image" :alt="thread.title" />
            <div class="thread-details">
              <h4>{{ thread.title }}</h4>
              <div class="thread-meta">
                <span>25 comentarios</span>
                <span>150 votos</span>
              </div>
            </div>
          </a>
        </div>
        <a href="/Foro" class="community-button">Ver todos los temas</a>
      </div>
    </section>

    <footer class="main-footer">
      <div class="footer-content">
        <div class="footer-section">
          <h3>SportFamilyRD</h3>
          <p>Tu comunidad deportiva en República Dominicana</p>
          <div class="social-links">
            <a href="#" class="social-icon"><img src="/imagenes/facebook-icon.png" alt="Facebook"></a>
            <a href="#" class="social-icon"><img src="/imagenes/instagram-icon.png" alt="Instagram"></a>
            <a href="#" class="social-icon"><img src="/imagenes/twitter-icon.png" alt="Twitter"></a>
          </div>
        </div>

        <div class="footer-section">
          <h3>Enlaces Rápidos</h3>
          <ul class="footer-links">
            <li><a href="/Noticias">Noticias</a></li>
            <li><a href="/Calendario">Calendario</a></li>
            <li><a href="/Tienda">Tienda</a></li>
            <li><a href="/Entrenadores">Entrenadores</a></li>
            <li><a href="/Foro">Foro</a></li>
          </ul>
        </div>

        <div class="footer-section">
          <h3>Contacto</h3>
          <ul class="contact-info">
            <li><i class="icon">📧</i> sportfamilyrd@gmail.com </li>
            <li><i class="icon">📱</i> (849) - 881 - 4028 </li>
            <!--<li><i class="icon">📍</i> </li>-->
          </ul>
        </div>
      </div>

      <div class="footer-bottom">
        <p>© 2025 SportFamilyRD. Todos los derechos reservados.</p>
        <div class="legal-links">
          <a href="/terminos">Términos de Servicio</a>
          <a href="/privacidad">Política de Privacidad</a>
        </div>
      </div>
    </footer>
  </div>
</template>



<script>
import Navbar from '../navbarComponent.vue';

export default {
  components: {
    Navbar
      },
  data() {
    return {
      categories: [
        { name: "Fútbol", image: "/imagenes/football.jpg" },
        { name: "Baloncesto", image: "/imagenes/basketball.jpg" },
        { name: "Béisbol", image: "/imagenes/DirectorioDeDeportes/baseball.jpg" },
        { name: "Voleibol", image: "/imagenes/voleyball.jpg" }
      ],
      products: [
        { name: "Consumibles", image: "/imagenes/consumibles.jpg", price: "24.99", featured: true },
        { name: "Ropa Deportiva", image: "/imagenes/ropa.jpg", price: "39.99" },
        { name: "Equipamiento", image: "/imagenes/articulos.jpg", price: "89.99", featured: true },
        { name: "Accesorios", image: "/imagenes/articulos.jpg", price: "14.99" }
      ],
      threads: [
        { title: "Surf en Cabarete", image: "/imagenes/surf_thread.jpg" },
        { title: "Escalada en Jarabacoa", image: "/imagenes/escalada_thread.jpg" },
        { title: "Levantamiento de pesas", image: "/imagenes/pesas.jpg" }
      ]
    }
  },

  mounted() {


    document.title = 'SportFamilyRD';

  },
}
</script>





<style scoped>
@import '../../../scss/Home/home.scss';

@import '../../../scss/Home/home_footer.scss';

@import '../../../scss/Home/home_navbar.scss';

@import '../../../scss/Home/home_productos.scss';

@import '../../../scss/Home/home_comunidad.scss';

@import '../../../scss/Home/home_responsive.scss';
</style>