<template>

  <div class="home-container">

    <!-- Navbar -->
    <Navbar />

    <!-- Hero Section -->
    <section class="hero-banner">
      <div class="hero-video-overlay"></div>
      <video autoplay muted loop class="hero-video">
        <source src="/videos/sports-hero.mp4" type="video/mp4">
      </video>

      <div class="hero-content">
        <div class="hero-text">
          <h1 class="hero-title">
            <span class="title-line">Conecta con la</span>
            <span class="title-line highlight">Comunidad Deportiva</span>
            <span class="title-line">Dominicana</span>
          </h1>
          <p class="hero-subtitle">Eventos • Entrenamiento • Comunidad • Tienda</p>
        </div>

        <!-- <div class="hero-search-container">
          <div class="search-box">
            <input type="text" placeholder="Buscar deportes, eventos o productos..." class="search-input">
            <button class="search-button">
              <i class="fas fa-search"></i>
            </button>
          </div>
          <div class="search-tags">
            <span>#Fútbol</span>
            <span>#Baloncesto</span>
            <span>#Béisbol</span>
            <span>#Fitness</span>
          </div>
        </div> -->

        <div class="hero-stats">
          <div class="stat-item">
            <div class="stat-number" data-count="4521">0</div>
            <div class="stat-label">Miembros</div>
          </div>
          <div class="stat-item">
            <div class="stat-number" data-count="287">0</div>
            <div class="stat-label">Eventos</div>
          </div>
          <div class="stat-item">
            <div class="stat-number" data-count="156">0</div>
            <div class="stat-label">Publicaciones</div>
          </div>
        </div>
      </div>
    </section>



    <!-- Directorio de deportes -->
    <section class="category-section">
      <div class="section-header">
        <h2 class="section-title">Descubre tu Deporte</h2>
        <p class="section-description">Explora más de 20 disciplinas deportivas</p>
        <router-link to="/directorio" class="view-all">Ver todos <i class="fas fa-arrow-right"></i></router-link>
      </div>

      <div class="category-grid">
        <div v-for="(category, index) in categories" :key="category.name" class="category-card"
          :style="{ '--delay': index * 0.1 + 's' }">
          <div class="card-inner">
            <div class="card-front">
              <img :src="category.image" :alt="category.name" class="card-image">
              <div class="card-overlay"></div>
              <div class="card-badge" v-if="category.popular">Popular</div>
              <div class="participation-rate">
                <div class="rate-bar" :style="{ width: category.participation + '%' }"></div>
                <span>{{ category.participation }}% participación</span>
              </div>
            </div>
            <div class="card-back">
              <h3>{{ category.name }}</h3>
              <p class="card-description">{{ category.description }}</p>
              <div class="card-stats">
              </div>
              <router-link :to="'/deporte/' + category.slug" class="card-button">
                Explorar <i class="fas fa-arrow-right"></i>
              </router-link>
            </div>
          </div>
        </div>
      </div>
    </section>





    <!-- Noticias deportivas -->
    <section class="news-section">
      <div class="section-header">
        <h2 class="section-title">Últimas Noticias Deportivas</h2>
        <p class="section-description">Mantente al día con lo último del mundo deportivo</p>
        <router-link to="/noticias" class="view-all-news">Ver todas las noticias <i
            class="fas fa-arrow-right"></i></router-link>
      </div>

      <div class="news-tabs">
        <button class="tab-button active" @click="filterNews('all')">Todas</button>
        <button class="tab-button" @click="filterNews('futbol')">Fútbol</button>
        <button class="tab-button" @click="filterNews('beisbol')">Béisbol</button>
        <button class="tab-button" @click="filterNews('baloncesto')">Baloncesto</button>
        <button class="tab-button" @click="filterNews('otros')">Otros Deportes</button>
      </div>

      <div class="news-grid">
        <!-- Noticia Destacada -->
        <div class="featured-news" v-if="filteredNews[0]">
          <div class="featured-image">
            <img :src="filteredNews[0].image" :alt="filteredNews[0].title">
            <div class="news-badge">Destacada</div>
            <div class="category-tag" :class="filteredNews[0].category">{{ filteredNews[0].category }}</div>
          </div>
          <div class="featured-content">
            <div class="news-meta">
              <span class="date"><i class="far fa-calendar-alt"></i> {{ filteredNews[0].date }}</span>
              <span class="author"><i class="far fa-user"></i> Por {{ filteredNews[0].author }}</span>
            </div>
            <h3 class="news-title">{{ filteredNews[0].title }}</h3>
            <p class="news-excerpt">{{ filteredNews[0].excerpt }}</p>
            <div class="news-actions">
              <router-link :to="'/noticia/' + filteredNews[0].id" class="read-more">Leer más <i
                  class="fas fa-arrow-right"></i></router-link>
            </div>
          </div>
        </div>

        <!-- Listado de Noticias -->
        <div class="news-list">
          <div class="news-card" v-for="(news, index) in filteredNews.slice(1, 5)" :key="news.id">
            <div class="news-card-image">
              <img :src="news.image" :alt="news.title">
              <div class="category-tag" :class="news.category">{{ news.category }}</div>
            </div>
            <div class="news-card-content">
              <div class="news-meta">
                <span class="date"><i class="far fa-calendar-alt"></i> {{ news.date }}</span>
              </div>
              <h4 class="news-title">{{ news.title }}</h4>
              <p class="news-excerpt">{{ news.excerpt.substring(0, 100) }}...</p>
              <router-link :to="'/noticia/' + news.id" class="read-more">Leer más <i
                  class="fas fa-arrow-right"></i></router-link>
            </div>
          </div>
        </div>

        <!-- Noticias Secundarias -->
        <!-- <div class="secondary-news">
          <div class="secondary-news-card" v-for="news in filteredNews.slice(5, 8)" :key="news.id">
            <div class="secondary-image">
              <img :src="news.image" :alt="news.title">
            </div>
            <div class="secondary-content">
              <div class="category-tag small" :class="news.category">{{ news.category }}</div>
              <h5 class="news-title">{{ news.title }}</h5>
              <div class="news-meta">
                <span class="date"><i class="far fa-clock"></i> {{ news.time }}</span>
              </div>
            </div>
          </div>
        </div> -->
      </div>

    </section>



    <!-- Eventos -->
    <section class="events-section">
      <div class="section-header">
        <h2 class="section-title">Eventos Destacados</h2>
        <p class="section-description">No te pierdas los próximos encuentros deportivos</p>
      </div>

      <div class="events-container">
        <div class="calendar-widget">
          <div class="calendar-header">
            <button class="nav-button"><i class="fas fa-chevron-left"></i></button>
            <h3 class="month-year">Julio 2025</h3>
            <button class="nav-button"><i class="fas fa-chevron-right"></i></button>
          </div>
          <div class="calendar-grid">
            <div class="day-header" v-for="day in ['L', 'M', 'M', 'J', 'V', 'S', 'D']" :key="day">{{ day }}</div>
            <div class="day" v-for="day in 31" :key="day"
              :class="{ 'has-event': day % 3 === 0, 'current-day': day === new Date().getDate() }">
              {{ day }}
              <div class="event-dot" v-if="day % 3 === 0"></div>
            </div>
          </div>
        </div>

        <div class="featured-events">
          <div class="event-card" v-for="event in featuredEvents" :key="event.id">
            <div class="event-date">
              <div class="date-day">{{ event.date.split('/')[0] }}</div>
              <div class="date-month">{{ event.date.split('/')[1] }}</div>
            </div>
            <div class="event-details">
              <h3 class="event-title">{{ event.title }}</h3>
              <div class="event-meta">
                <span class="event-location"><i class="fas fa-map-marker-alt"></i> {{ event.location }}</span>
                <span class="event-time"><i class="fas fa-clock"></i> {{ event.time }}</span>
              </div>
              <p class="event-description">{{ event.description }}</p>
              <!-- <div class="event-actions">
                <button class="share-button"><i class="fas fa-share-alt"></i></button>
              </div> -->
            </div>
          </div>
        </div>
      </div>

      <div class="map-view">
        <div class="map-container">
          <img src="/imagenes/map1.png" alt="Mapa de eventos" class="map-image">
          <div class="map-pins">
            <div class="map-pin" v-for="pin in mapPins" :key="pin.id" :style="{ left: pin.x + '%', top: pin.y + '%' }">
              <div class="pin-tooltip">{{ pin.event }}</div>
            </div>
          </div>
        </div>
        <button class="view-map-button">
          <i class="fas fa-map-marked-alt"></i> Ver Mapa Completo
        </button>
      </div>
    </section>




    <!-- Productos -->
    <section class="products-section">
      <div class="section-header">
        <h2 class="section-title">Equipamiento Premium</h2>
        <p class="section-description">Los mejores productos para tu rendimiento</p>
        <div class="view-options">
          <button class="view-option active">Destacados</button>
          <button class="view-option">Nuevos</button>
        </div>
      </div>

      <div class="products-carousel">
        <div class="product-card" v-for="product in featuredProducts" :key="product.id">
          <div class="product-badges">
            <div class="badge featured" v-if="product.featured">Destacado</div>
            <div class="badge discount" v-if="product.discount">-{{ product.discount }}%</div>
          </div>
          <div class="product-image-container">
            <img :src="product.image" :alt="product.name" class="product-image">
            <button class="quick-view" @click="showProductModal(product)">
              <i class="fas fa-expand"></i> Vista Rápida
            </button>
          </div>
          <div class="product-info">
            <h3 class="product-name">{{ product.name }}</h3>
            <div class="product-rating">
              <div class="stars">
                <i class="fas fa-star" v-for="n in 5" :key="n" :class="{ 'filled': n <= product.rating }"></i>
              </div>
              <span class="review-count">({{ product.reviews }})</span>
            </div>
            <div class="product-pricing">
              <span class="current-price">${{ product.price }}</span>
              <span class="original-price" v-if="product.originalPrice">${{ product.originalPrice }}</span>
            </div>
            <div class="product-actions">
              <button class="add-to-cart">
                <i class="fas fa-shopping-cart"></i> Añadir
              </button>
              <button class="add-to-wishlist">
                <i class="far fa-heart"></i>
              </button>
            </div>
          </div>
        </div>
      </div>
    </section>





    <!-- Comunidad Interactiva -->
    <section class="community-section">
      <div class="community-header">
        <h2 class="section-title">Únete a la Comunidad</h2>
        <p class="section-description">Conecta con otros apasionados del deporte</p>
      </div>

      <div class="community-grid">
        <div class="forum-highlights">
          <h3 class="sub-section-title">Discusiones Populares</h3>
          <div class="forum-thread" v-for="thread in popularThreads" :key="thread.id">
            <div class="thread-header">
              <img :src="thread.author.avatar" :alt="thread.author.name" class="author-avatar">
              <div class="author-info">
                <span class="author-name">{{ thread.author.name }}</span>
                <span class="thread-date">{{ thread.date }}</span>
              </div>
              <div class="thread-stats">
                <span class="stat"><i class="fas fa-comments"></i> Comentarios - {{ thread.comments }}</span>
                <span class="stat"><i class="fas fa-heart"></i> Likes - {{ thread.likes }}</span>
              </div>
            </div>
            <h4 class="thread-title">{{ thread.title }}</h4>
            <p class="thread-excerpt">{{ thread.excerpt }}</p>
            <div class="thread-tags">
              <span class="tag" v-for="tag in thread.tags" :key="tag">{{ tag }}</span>
            </div>
          </div>

          <router-link to="/foro" class="view-all-threads">
            Ver todas las discusiones <i class="fas fa-arrow-right"></i>
          </router-link>
        </div>

        

        <!-- <div class="live-feed">
          <h3 class="sub-section-title">Actividad Reciente</h3>
          <div class="activity-item" v-for="activity in recentActivity" :key="activity.id">
            <div class="activity-avatar">
              <img :src="activity.user.avatar" :alt="activity.user.name">
            </div>
            <div class="activity-content">
              <p class="activity-text">
                <span class="user-name">{{ activity.user.name }}</span>
                {{ activity.action }}
                <span class="activity-target" v-if="activity.target">{{ activity.target }}</span>
              </p>
              <span class="activity-time">{{ activity.time }}</span>
            </div>
          </div>
        </div> -->


      </div>
    </section>



    <!-- App Download Section -->

    <!-- <section class="app-section">
      <div class="app-content">
        <div class="app-info">
          <h2 class="section-title">Descarga Nuestra App</h2>
          <p class="app-description">Lleva SportFamilyRD contigo a todas partes. Notificaciones instantáneas, acceso exclusivo y más.</p>
          <div class="download-buttons">
            <button class="download-button app-store">
              <i class="fab fa-apple"></i>
              <div class="button-text">
                <span>Descarga en la</span>
                <span>App Store</span>
              </div>
            </button>
            <button class="download-button google-play">
              <i class="fab fa-google-play"></i>
              <div class="button-text">
                <span>Disponible en</span>
                <span>Google Play</span>
              </div>
            </button>
          </div>
        </div>
        <div class="app-preview">
          <img src="/images/app-screens.png" alt="Vista previa de la app" class="app-screens">
        </div>
      </div>
    </section> -->



    <!-- Newsletter -->

    <!-- <section class="newsletter-section">
      <div class="newsletter-container">
        <div class="newsletter-content">
          <h2 class="section-title">Mantente Informado</h2>
          <p class="newsletter-text">Suscríbete para recibir noticias, eventos exclusivos y ofertas especiales.</p>
          <div class="newsletter-form">
            <input type="email" placeholder="Tu correo electrónico" class="email-input">
            <button class="subscribe-button">Suscribirse</button>
          </div>
          <div class="privacy-notice">
            <input type="checkbox" id="privacy-check" checked>
            <label for="privacy-check">Acepto la política de privacidad</label>
          </div>
        </div>
        <div class="newsletter-benefits">
          <div class="benefit-item">
            <i class="fas fa-gift"></i>
            <span>Oferta de bienvenida</span>
          </div>
          <div class="benefit-item">
            <i class="fas fa-calendar-check"></i>
            <span>Eventos exclusivos</span>
          </div>
          <div class="benefit-item">
            <i class="fas fa-percentage"></i>
            <span>Descuentos especiales</span>
          </div>
        </div>
      </div>
    </section> -->



    <!-- Footer -->
    <footer class="main-footer">
      <div class="footer-content">
        <div class="footer-brand">
          <div class="brand-logo">
            <img src="/imagenes/logo2.png" alt="SportFamilyRD Logo">
          </div>
          <p class="brand-slogan">Conectando la comunidad deportiva dominicana</p>
          <div class="social-links">
            <a href="#" class="social-icon"><i class="fab fa-facebook-f"></i></a>
            <a href="#" class="social-icon"><i class="fab fa-instagram"></i></a>
            <a href="#" class="social-icon"><i class="fab fa-twitter"></i></a>
            <a href="#" class="social-icon"><i class="fab fa-youtube"></i></a>
            <a href="#" class="social-icon"><i class="fab fa-tiktok"></i></a>
          </div>
        </div>

        <div class="footer-links">
          <div class="link-column">
            <h3 class="links-title">Explorar</h3>
            <ul>
              <li><a href="#">Deportes</a></li>
              <li><a href="#">Noticias</a></li>
              <li><a href="#">Eventos</a></li>
              <li><a href="#">Tienda</a></li>
              <li><a href="#">Entrenadores</a></li>
              <li><a href="#">Foro Deportivo</a></li>
            </ul>
          </div>
          <div class="link-column">
            <h3 class="links-title">Comunidad</h3>
            <ul>
              <li><a href="#">Foro</a></li>
              <li><a href="#">Grupos</a></li>
              <li><a href="#">Blog</a></li>
              <li><a href="#">Testimonios</a></li>
              <li><a href="#">Patrocinadores</a></li>
            </ul>
          </div>
          <div class="link-column">
            <h3 class="links-title">Empresa</h3>
            <ul>
              <li><a href="#">Nosotros</a></li>
              <li><a href="#">Contacto</a></li>
              <li><a href="#">Trabaja con Nosotros</a></li>
              <li><a href="#">Prensa</a></li>
              <li><a href="#">Partners</a></li>
            </ul>
          </div>
          <div class="link-column">
            <h3 class="links-title">Legal</h3>
            <ul>
              <li><a href="#">Términos</a></li>
              <li><a href="#">Privacidad</a></li>
              <li><a href="#">Cookies</a></li>
              <li><a href="#">DMCA</a></li>
              <li><a href="#">Aviso Legal</a></li>
            </ul>
          </div>
        </div>

        <div class="footer-contact">
          <h3 class="contact-title">Contacto</h3>
          <div class="contact-item">
            <i class="fas fa-envelope"></i>
            <span>info@sportfamilyrd.com</span>
          </div>
          <div class="contact-item">
            <i class="fas fa-phone-alt"></i>
            <span>(849) 881-4028</span>
          </div>
          <div class="contact-item">
            <i class="fas fa-map-marker-alt"></i>
            <span>Santo Domingo, República Dominicana</span>
          </div>
        </div>
      </div>

      <div class="footer-bottom">
        <!-- <div class="payment-methods">
          <i class="fab fa-cc-visa"></i>
          <i class="fab fa-cc-mastercard"></i>
          <i class="fab fa-cc-amex"></i>
          <i class="fab fa-cc-paypal"></i>
          <i class="fab fa-cc-discover"></i>
        </div> -->
        <div class="copyright">
          © 2025 SportFamilyRD. Todos los derechos reservados.
        </div>
      </div>
    </footer>

    <!-- Product Modal -->
    <ProductModal v-if="showModal" :product="selectedProduct" @close="closeModal" />

    <!-- Floating Action Buttons -->
    <!-- <div class="fab-container">
      <button class="fab main-fab">
        <i class="fas fa-comment-dots"></i>
      </button>
      <div class="fab-options">
        <button class="fab option-fab">
          <i class="fas fa-question"></i>
        </button>
        <button class="fab option-fab">
          <i class="fas fa-calendar-alt"></i>
        </button>
        <button class="fab option-fab">
          <i class="fas fa-shopping-cart"></i>
        </button>
      </div>
    </div> -->

    <!-- Back to Top Button -->
    <button class="back-to-top" @click="scrollToTop">
      <i class="fas fa-arrow-up"></i>
    </button>

  </div>
</template>




<script>
import axios from 'axios';
import Navbar from '../navbarComponent.vue';

export default {
  components: {
    Navbar
  },
  data() {
    return {
      showModal: false,
      selectedProduct: null,
      categories: [
        {
          name: "Baseball",
          image: "/imagenes/DirectorioDeDeportes/baseball.jpg",
          description: "La pasión de multitudes en RD",
          participation: 80,
          members: 1250,
          events: 45,
          slug: "futbol",
          popular: true
        },
        {
          name: "Basketball",
          image: "/imagenes/DirectorioDeDeportes/Baloncesto.jpg",
          description: "La pasión de multitudes en RD",
          participation: 64,
          members: 1250,
          events: 45,
          slug: "futbol",
          popular: true
        },
        {
          name: "Domino",
          image: "/imagenes/DirectorioDeDeportes/Domino.jpg",
          description: "La pasión de multitudes en RD",
          participation: 72,
          members: 1250,
          events: 45,
          slug: "futbol",
          popular: true
        },
        // Más categorías...
      ],
      featuredEvents: [
        {
          id: 1,
          title: "Torneo Nacional de Fútbol",
          date: "15/Jul",
          location: "Estadio Olímpico, SD",
          time: "4:00 PM",
          description: "Final del torneo nacional con los mejores equipos del país",
          rsvp: false
        },
        {
          id: 1,
          title: "Torneo Nacional de Fútbol",
          date: "15/Jul",
          location: "Estadio Olímpico, SD",
          time: "4:00 PM",
          description: "Final del torneo nacional con los mejores equipos del país",
          rsvp: false
        },
        // Más eventos...
      ],
      featuredProducts: [
        {
          id: 1,
          name: "Balón Oficial Liga Dominicana",
          image: "/imagenes/football.jpg",
          price: "24.99",
          originalPrice: "34.99",
          discount: 30,
          rating: 4,
          reviews: 128,
          featured: true,
          ar: true
        },
        {
          id: 1,
          name: "Balón Oficial Liga Dominicana",
          image: "/imagenes/football.jpg",
          price: "24.99",
          originalPrice: "34.99",
          discount: 30,
          rating: 4,
          reviews: 128,
          featured: true,
          ar: true
        },
        {
          id: 1,
          name: "Balón Oficial Liga Dominicana",
          image: "/imagenes/football.jpg",
          price: "24.99",
          originalPrice: "34.99",
          discount: 30,
          rating: 4,
          reviews: 128,
          featured: true,
          ar: true
        },
        {
          id: 1,
          name: "Balón Oficial Liga Dominicana",
          image: "/imagenes/football.jpg",
          price: "24.99",
          originalPrice: "34.99",
          discount: 30,
          rating: 4,
          reviews: 128,
          featured: true,
          ar: true
        },

        // Más productos...
      ],
      popularThreads: [
        {
          id: 1,
          title: "¿Dónde practicar surf en República Dominicana?",
          excerpt: "Estoy buscando los mejores spots para surfear este verano...",
          author: {
            name: "Carlos Rodríguez",
            avatar: "/imagenes/surf_thread.jpg"
          },
          date: "Hace 2 horas",
          comments: 24,
          likes: 56,
          tags: ["surf", "playa", "consejos"]
        },
        {
          id: 1,
          title: "¿Dónde practicar surf en República Dominicana?",
          excerpt: "Estoy buscando los mejores spots para surfear este verano...",
          author: {
            name: "Carlos Rodríguez",
            avatar: "/imagenes/surf_thread.jpg"
          },
          date: "Hace 2 horas",
          comments: 24,
          likes: 56,
          tags: ["surf", "playa", "consejos"]
        },
        {
          id: 1,
          title: "¿Dónde practicar surf en República Dominicana?",
          excerpt: "Estoy buscando los mejores spots para surfear este verano...",
          author: {
            name: "Carlos Rodríguez",
            avatar: "/imagenes/surf_thread.jpg"
          },
          date: "Hace 2 horas",
          comments: 24,
          likes: 56,
          tags: ["surf", "playa", "consejos"]
        },
        // Más hilos...
      ],
      recentActivity: [
        {
          id: 1,
          user: {
            name: "Juan Pérez",
            avatar: "/images/avatars/user2.jpg"
          },
          action: "se unió al grupo",
          target: "Baloncesto RD",
          time: "5 min ago"
        },
        // Más actividad...
      ],
      mapPins: [
        {
          id: 1,
          event: "Torneo de Voleibol",
          x: 30,
          y: 45
        },
        {
          id: 2,
          event: "Torneo de Futbol",
          x: 10,
          y: 42
        },
        {
          id: 3,
          event: "Torneo de Domino",
          x: 33,
          y: 10
        },
      ],
      activeFilter: 'all',
      sportsNews: [
        {
          id: 1,
          title: "La selección dominicana de baloncesto gana el torneo clasificatorio",
          excerpt: "El equipo nacional logró una victoria histórica que los coloca en los primeros lugares del ranking FIBA...",
          image: "/images/news/basketball-win.jpg",
          category: "baloncesto",
          date: "15 Julio 2025",
          time: "Hace 2 horas",
          author: "Carlos Martínez"
        },
        // Más noticias...
      ]
    }
  },

  computed: {
    filteredNews() {
      if (this.activeFilter === 'all') {
        return this.sportsNews;
      }
      return this.sportsNews.filter(news => news.category === this.activeFilter);
    }
  },

  methods: {
    showProductModal(product) {
      this.selectedProduct = product;
      this.showModal = true;
      document.body.style.overflow = 'hidden';
    },
    closeModal() {
      this.showModal = false;
      document.body.style.overflow = '';
    },
    scrollToTop() {
      window.scrollTo({
        top: 0,
        behavior: 'smooth'
      });
    },
    animateStats() {
      const counters = document.querySelectorAll('.stat-number');
      const speed = 200;

      counters.forEach(counter => {
        const target = +counter.getAttribute('data-count');
        const count = +counter.innerText;
        const increment = target / speed;

        if (count < target) {
          counter.innerText = Math.ceil(count + increment);
          setTimeout(this.animateStats, 1);
        } else {
          counter.innerText = target;
        }
      });
    },

    filterNews(category) {
      this.activeFilter = category;
      document.querySelectorAll('.tab-button').forEach(btn => {
        btn.classList.remove('active');
      });
      event.target.classList.add('active');
    },

    getCalendarScrap(){
      axios.get('/scrap-calendar')
        .then(response => {
          console.log("Calendar data fetched successfully:", response.data.events);
        })
        .catch(error => {
          console.error("Error fetching calendar data:", error);
        });
    }



  },
  mounted() {
    document.title = 'SportFamilyRD - Comunidad Deportiva Dominicana';
    this.animateStats();
    this.getCalendarScrap();

    // Animación de scroll para elementos
    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add('animate');
        }
      });
    }, {
      threshold: 0.1
    });

    document.querySelectorAll('.category-card, .event-card, .product-card').forEach(card => {
      observer.observe(card);
    });
  }
}
</script>



<style scoped>

@import '../../../scss/Home/home.scss';

@import '../../../scss/Home/home_navbar.scss';


/* Hero Section */
.hero-banner {
  position: relative;
  height: 100vh;
  min-height: 600px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  text-align: center;
}

.hero-video {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  object-fit: cover;
  z-index: -1;
}

.hero-video-overlay {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.4);
  z-index: -1;
}

.hero-content {
  max-width: 1200px;
  padding: 0 20px;
  z-index: 1;
}

.hero-title {
  font-size: 4rem;
  font-weight: 800;
  margin-bottom: 1rem;
  line-height: 1.2;
}

.title-line {
  display: block;
}

.highlight {
  color: #c92e40;
  text-shadow: 0 0 10px rgba(184, 23, 23, 0.5);
}

.hero-subtitle {
  font-size: 1.5rem;
  margin-bottom: 2rem;
  opacity: 0.9;
}


/* Stats */
.hero-stats {
  display: flex;
  justify-content: center;
  gap: 40px;
  margin-top: 50px;
}

.stat-item {
  text-align: center;
}

.stat-number {
  font-size: 3rem;
  font-weight: 700;
  margin-bottom: 5px;
  color: #c92e40;
}

.stat-label {
  font-size: 1rem;
  opacity: 0.8;
  text-transform: uppercase;
  letter-spacing: 1px;
}

/* Scroll Indicator */
.scroll-indicator {
  position: absolute;
  bottom: 30px;
  left: 50%;
  transform: translateX(-50%);
}

.mouse {
  width: 25px;
  height: 40px;
  border: 2px solid white;
  border-radius: 15px;
  position: relative;
}

.wheel {
  width: 4px;
  height: 8px;
  background: white;
  border-radius: 2px;
  position: absolute;
  top: 5px;
  left: 50%;
  transform: translateX(-50%);
  animation: scroll 2s infinite;
}

@keyframes scroll {
  0% {
    top: 5px;
    opacity: 1;
  }

  50% {
    top: 15px;
    opacity: 0.5;
  }

  100% {
    top: 5px;
    opacity: 1;
  }
}

/* Section Styles */
.section-header {
  text-align: center;
  margin-bottom: 50px;
}

.section-title {
  font-size: 2.5rem;
  font-weight: 700;
  margin-bottom: 15px;
  position: relative;
  display: inline-block;
}

.section-title::after {
  content: '';
  position: absolute;
  bottom: -10px;
  left: 50%;
  transform: translateX(-50%);
  width: 80px;
  height: 3px;
  background: #000000;
}

.section-description {
  font-size: 1.2rem;
  color: #666;
  max-width: 700px;
  margin: 0 auto;
}

.view-all {
  display: inline-block;
  margin-top: 15px;
  color: #c92e40;
  font-weight: 600;
  text-decoration: none;
  transition: all 0.3s ease;
}

.view-all:hover {
  color: #000000;
}

.view-all i {
  margin-left: 5px;
  transition: transform 0.3s ease;
}

.view-all:hover i {
  transform: translateX(5px);
}


/* Category Cards */
.category-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 30px;
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 20px;
}

.category-card {
  perspective: 1000px;
  height: 350px;
  opacity: 0;
  transform: translateY(30px);
  transition: all 0.6s cubic-bezier(0.23, 1, 0.32, 1);
}

.category-card.animate {
  opacity: 1;
  transform: translateY(0);
}

.card-inner {
  position: relative;
  width: 100%;
  height: 100%;
  transition: transform 0.8s;
  transform-style: preserve-3d;
}

.category-card:hover .card-inner {
  transform: rotateY(180deg);
}

.card-front,
.card-back {
  position: absolute;
  width: 100%;
  height: 100%;
  backface-visibility: hidden;
  border-radius: 15px;
  overflow: hidden;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
}

.card-front {
  background-color: #fff;
  color: #333;
}

.card-back {
  background-color: #c92e40;
  color: white;
  transform: rotateY(180deg);
  padding: 25px;
  display: flex;
  flex-direction: column;
}

.card-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.card-overlay {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: linear-gradient(to top, rgba(0, 0, 0, 0.7) 0%, rgba(0, 0, 0, 0) 50%);
}

.card-badge {
  position: absolute;
  top: 15px;
  right: 15px;
  background: #ff4757;
  color: white;
  padding: 5px 15px;
  border-radius: 20px;
  font-size: 0.8rem;
  font-weight: 600;
}

.participation-rate {
  position: absolute;
  bottom: 20px;
  left: 20px;
  right: 20px;
  color: white;
}

.rate-bar {
  height: 5px;
  background: white;
  margin-bottom: 5px;
  border-radius: 3px;
}

.card-content {
  position: absolute;
  bottom: 20px;
  left: 20px;
  right: 20px;
  color: white;
}

.card-content h3 {
  font-size: 1.5rem;
  margin-bottom: 5px;
}

.card-description {
  margin-bottom: 20px;
  font-size: 0.95rem;
}

.card-stats {
  display: flex;
  gap: 15px;
  margin-bottom: 20px;
}

.stat {
  display: flex;
  align-items: center;
  gap: 5px;
  font-size: 0.9rem;
}

.card-button {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  background: white;
  color: #c92e40;
  padding: 10px 20px;
  border-radius: 30px;
  text-decoration: none;
  font-weight: 600;
  margin-top: auto;
  transition: all 0.3s ease;
}

.card-button:hover {
  background: #f8f9fa;
  transform: translateX(5px);
}

.card-button i {
  margin-left: 5px;
  transition: transform 0.3s ease;
}

.card-button:hover i {
  transform: translateX(3px);
}


/* News Section */

.view-all-news {
  display: inline-block;
  margin-top: 15px;
  color: #0051a8;
  font-weight: 600;
  text-decoration: none;
  transition: all 0.3s ease;
}

.news-section {
  padding: 90px 0;
  background: #f8f9fa;
}

.news-tabs {
  display: flex;
  justify-content: center;
  gap: 10px;
  margin-bottom: 30px;
  flex-wrap: wrap;
}

.tab-button {
  padding: 15px 25px;
  background: #e9ecef;
  border: none;
  border-radius: 30px;
  font-size: 0.9rem;
  cursor: pointer;
  transition: all 0.3s ease;
}

.tab-button:hover {
  background: #dee2e6;
}

.tab-button.active {
  background: #0051a8;
  color: white;
  padding: 20px;
}

.news-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 30px;
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 20px;
}

.featured-news {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 30px;
  background: white;
  border-radius: 15px;
  overflow: hidden;
  box-shadow: 0 5px 15px rgba(0,0,0,0.05);
}

.featured-image {
  position: relative;
  height: 350px;
}

.featured-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.news-badge {
  position: absolute;
  top: 20px;
  left: 20px;
  background: rgb(39, 43, 158);
  color: white;
  padding: 5px 15px;
  border-radius: 4px;
  font-size: 0.8rem;
  font-weight: 600;
}

.category-tag {
  position: absolute;
  bottom: 20px;
  left: 20px;
  padding: 5px 15px;
  border-radius: 4px;
  font-size: 0.8rem;
  font-weight: 600;
  color: white;
}

.category-tag.futbol {
  background: #28a745;
}

.category-tag.beisbol {
  background: #fd7e14;
}

.category-tag.baloncesto {
  background: #007bff;
}

.category-tag.otros {
  background: #6f42c1;
}

.category-tag.small {
  font-size: 0.7rem;
  padding: 3px 10px;
}

.featured-content {
  padding: 30px;
  display: flex;
  flex-direction: column;
  justify-content: center;
}

.news-meta {
  display: flex;
  gap: 15px;
  margin-bottom: 15px;
  font-size: 0.9rem;
  color: #666;
}

.news-meta i {
  margin-right: 5px;
  color: #0051a8;
}

.news-title {
  font-size: 1.8rem;
  margin-bottom: 15px;
  color: #333;
  line-height: 1.3;
}

.featured-news .news-title {
  font-size: 2rem;
}

.news-excerpt {
  color: #555;
  margin-bottom: 20px;
  line-height: 1.6;
}

.news-actions {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: auto;
}

.read-more {
  color: #0051a8;
  font-weight: 600;
  text-decoration: none;
  transition: all 0.3s ease;
}

.read-more:hover {
  color: #002d5d;
}

.read-more i {
  margin-left: 5px;
  transition: transform 0.3s ease;
}

.read-more:hover i {
  transform: translateX(5px);
}

.social-share {
  display: flex;
  gap: 10px;
}

.share-button {
  width: 35px;
  height: 35px;
  border-radius: 50%;
  border: none;
  background: #f1f1f1;
  color: #555;
  cursor: pointer;
  transition: all 0.3s ease;
}

.share-button:hover {
  transform: translateY(-3px);
}

.share-button:nth-child(1):hover {
  background: #3b5998;
  color: white;
}

.share-button:nth-child(2):hover {
  background: #1da1f2;
  color: white;
}

.share-button:nth-child(3):hover {
  background: #25d366;
  color: white;
}

.news-list {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 20px;
}

.news-card {
  background: white;
  border-radius: 10px;
  overflow: hidden;
  box-shadow: 0 5px 15px rgba(0,0,0,0.05);
  transition: transform 0.3s ease;
}

.news-card:hover {
  transform: translateY(-5px);
}

.news-card-image {
  position: relative;
  height: 180px;
}

.news-card-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.news-card-content {
  padding: 20px;
}

.news-card .news-title {
  font-size: 1.2rem;
  margin-bottom: 10px;
}

.news-card .news-excerpt {
  font-size: 0.9rem;
  margin-bottom: 15px;
}

.secondary-news {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  gap: 15px;
}

.secondary-news-card {
  background: white;
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 3px 10px rgba(0,0,0,0.05);
}

.secondary-image {
  height: 120px;
}

.secondary-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.secondary-content {
  padding: 15px;
}

.secondary-news-card .news-title {
  font-size: 0.95rem;
  margin: 10px 0;
}

.secondary-news-card .news-meta {
  font-size: 0.8rem;
}

/* Responsive */
@media (max-width: 992px) {
  .featured-news {
    grid-template-columns: 1fr;
  }
  
  .featured-image {
    height: 250px;
  }
}

@media (max-width: 768px) {
  .news-tabs {
    justify-content: flex-start;
    overflow-x: auto;
    padding-bottom: 10px;
  }
  
  .featured-content {
    padding: 20px;
  }
  
  .featured-news .news-title {
    font-size: 1.5rem;
  }
}

@media (max-width: 576px) {
  .news-meta {
    flex-direction: column;
    gap: 5px;
  }
  
  .news-actions {
    flex-direction: column;
    align-items: flex-start;
    gap: 15px;
  }
  
  .social-share {
    align-self: flex-end;
  }
}







/* Events Section */
.events-section {
  padding: 40px 0;
  background: #f8f9fa;
}

.events-container {
  display: grid;
  grid-template-columns: 300px 1fr;
  gap: 40px;
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 20px;
}

.calendar-widget {
  background: white;
  border-radius: 15px;
  padding: 20px;
  box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
  align-self: start;
}

.calendar-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.month-year {
  font-size: 1.2rem;
  font-weight: 600;
}

.nav-button {
  background: none;
  border: none;
  color: #009e3d;
  font-size: 1rem;
  cursor: pointer;
  padding: 5px;
}

.calendar-grid {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  gap: 10px;
}

.day-header {
  text-align: center;
  font-weight: 600;
  color: #666;
  font-size: 0.9rem;
}

.day {
  text-align: center;
  padding: 8px 0;
  border-radius: 50%;
  cursor: pointer;
  position: relative;
  transition: all 0.2s ease;
}

.day:hover {
  background: #f0f0f0;
}

.has-event::after {
  content: '';
  position: absolute;
  bottom: 3px;
  left: 50%;
  transform: translateX(-50%);
  width: 5px;
  height: 5px;
  background: #74e25e;
  border-radius: 50%;
}

.current-day {
  background: #009e3d;
  color: white;
  padding: 10px;
}

.featured-events {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.event-card {
  background: white;
  border-radius: 15px;
  overflow: hidden;
  box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
  display: flex;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.event-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
}

.event-date {
  background: #009e3d;
  color: white;
  padding: 20px;
  min-width: 80px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
}

.date-day {
  font-size: 2rem;
  font-weight: 700;
  line-height: 1;
}

.date-month {
  font-size: 1rem;
  text-transform: uppercase;
  letter-spacing: 1px;
}

.event-details {
  padding: 20px;
  flex: 1;
}

.event-title {
  font-size: 1.3rem;
  margin-bottom: 10px;
  color: #333;
}

.event-meta {
  display: flex;
  gap: 15px;
  margin-bottom: 10px;
  font-size: 0.9rem;
  color: #666;
}

.event-meta i {
  margin-right: 5px;
  color: #009e3d;
}

.event-description {
  color: #555;
  margin-bottom: 15px;
  font-size: 0.95rem;
  line-height: 1.5;
}

.event-actions {
  display: flex;
  gap: 10px;
}

.rsvp-button {
  background: #009e3d;
  color: white;
  border: none;
  padding: 8px 20px;
  border-radius: 30px;
  cursor: pointer;
  font-size: 0.9rem;
  transition: all 0.3s ease;
}

.rsvp-button:hover {
  background: #009e3d;
}

.rsvp-button.going {
  background: #009e3d;
}

.share-button {
  background: #f8f9fa;
  color: #333;
  border: none;
  width: 35px;
  height: 35px;
  border-radius: 50%;
  cursor: pointer;
  transition: all 0.3s ease;
}

.share-button:hover {
  background: #e9ecef;
}

.map-view {
  margin-top: 50px;
  grid-column: span 2;
}

.map-container {
  position: relative;
  border-radius: 15px;
  overflow: hidden;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
  height: 400px;
}

.map-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.map-pins {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
}

.map-pin {
  position: absolute;
  width: 20px;
  height: 20px;
  background: #000000;
  border-radius: 50% 50% 50% 0;
  transform: rotate(-45deg);
  cursor: pointer;
}

.map-pin::after {
  content: '';
  position: absolute;
  width: 10px;
  height: 10px;
  background: white;
  border-radius: 50%;
  top: 5px;
  left: 5px;
}

.pin-tooltip {
  position: absolute;
  bottom: 30px;
  left: 50%;
  transform: translateX(-50%);
  background: white;
  padding: 5px 10px;
  border-radius: 5px;
  font-size: 0.8rem;
  white-space: nowrap;
  box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
  opacity: 0;
  transition: opacity 0.3s ease;
  transform: translateX(-50%) rotate(45deg);
}

.map-pin:hover .pin-tooltip {
  opacity: 1;
}

.view-map-button {
  display: block;
  margin: 20px auto 0;
  background: #009e3d;
  color: white;
  border: none;
  padding: 17px 25px;
  border-radius: 30px;
  cursor: pointer;
  font-size: 1rem;
  transition: all 0.3s ease;
}

.view-map-button:hover {
  background: #006326;
}

.view-map-button i {
  margin-right: 8px;
}




/* Products Section */
.products-section {
  padding: 60px 0;
  background: white;
}

.view-options {
  display: flex;
  justify-content: center;
  gap: 15px;
  margin-top: 20px;
}

.view-option {
  background: none;
  border: none;
  color: #666;
  font-size: 0.95rem;
  padding: 5px 15px;
  border-radius: 20px;
  cursor: pointer;
  transition: all 0.3s ease;
}

.view-option:hover {
  color: #008599;
}

.view-option.active {
  background: #008599;
  color: white;
}

.products-carousel {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
  gap: 30px;
  max-width: 1200px;
  margin: 40px auto 0;
  padding: 0 20px;
}

.product-card {
  background: white;
  border-radius: 15px;
  overflow: hidden;
  box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
  transition: transform 0.3s ease, box-shadow 0.3s ease;
  position: relative;
}

.product-card:hover {
  transform: translateY(-10px);
  box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
}

.product-badges {
  position: absolute;
  top: 15px;
  left: 15px;
  display: flex;
  flex-direction: column;
  gap: 10px;
  z-index: 1;
}

.badge {
  padding: 5px 10px;
  border-radius: 4px;
  font-size: 0.7rem;
  font-weight: 600;
  color: white;
}

.badge.featured {
  background: #000000;
}

.badge.discount {
  background: #005663;
}

.badge.ar {
  background: #333;
}

.product-image-container {
  position: relative;
  height: 200px;
  overflow: hidden;
}

.product-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.5s ease;
}

.product-card:hover .product-image {
  transform: scale(1.05);
}

.quick-view {
  position: absolute;
  bottom: -50px;
  left: 0;
  width: 100%;
  background: rgba(0, 0, 0, 0.7);
  color: white;
  border: none;
  padding: 10px;
  font-size: 0.8rem;
  cursor: pointer;
  transition: bottom 0.3s ease;
}

.product-card:hover .quick-view {
  bottom: 0;
}

.product-info {
  padding: 15px;
}

.product-name {
  font-size: 1.1rem;
  margin-bottom: 10px;
  color: #333;
}

.product-rating {
  display: flex;
  align-items: center;
  gap: 5px;
  margin-bottom: 10px;
}

.stars {
  color: #ddd;
}

.stars .filled {
  color: #ffc107;
}

.review-count {
  font-size: 0.8rem;
  color: #666;
}

.product-pricing {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 15px;
}

.current-price {
  font-size: 1.2rem;
  font-weight: 700;
  color: #008599;
}

.original-price {
  font-size: 0.9rem;
  color: #999;
  text-decoration: line-through;
}

.product-actions {
  display: flex;
  gap: 10px;
}

.add-to-cart {
  flex: 1;
  background: #008599;
  color: white;
  border: none;
  padding: 8px;
  border-radius: 4px;
  cursor: pointer;
  font-size: 0.9rem;
  transition: all 0.3s ease;
}

.add-to-cart:hover {
  background: #1492a7;
}

.add-to-wishlist {
  background: #f8f9fa;
  color: #333;
  border: none;
  width: 35px;
  height: 35px;
  border-radius: 4px;
  cursor: pointer;
  transition: all 0.3s ease;
}

.add-to-wishlist:hover {
  background: #e9ecef;
  color: #008599;
}





/* Community Section */
.community-section {
  padding: 100px 0;
  background: #f8f9fa;
}

.community-header {
  text-align: center;
  margin-bottom: 50px;
}

.community-grid {
  display: grid;
  grid-template-columns: 1fr 300px;
  gap: 40px;
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 20px;
}

.forum-highlights {
  grid-column: span 1;
}

.sub-section-title {
  font-size: 1.5rem;
  margin-bottom: 20px;
  color: #333;
  position: relative;
  padding-bottom: 10px;
}

.sub-section-title::after {
  content: '';
  position: absolute;
  bottom: 0;
  left: 0;
  width: 50px;
  height: 3px;
  background: #6a11cb;
}

.forum-thread {
  background: white;
  border-radius: 10px;
  padding: 20px;
  margin-bottom: 20px;
  box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
  transition: transform 0.3s ease;
}

.forum-thread:hover {
  transform: translateY(-5px);
}

.thread-header {
  display: flex;
  align-items: center;
  margin-bottom: 15px;
}

.author-avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  object-fit: cover;
  margin-right: 15px;
}

.author-info {
  flex: 1;
}

.author-name {
  font-weight: 600;
  display: block;
  color: #333;
}

.thread-date {
  font-size: 0.8rem;
  color: #999;
}

.thread-stats {
  display: flex;
  gap: 15px;
}

.thread-stats .stat {
  font-size: 0.8rem;
  color: #666;
}

.thread-stats i {
  margin-right: 5px;
  color: #6a11cb;
}

.thread-title {
  font-size: 1.1rem;
  margin-bottom: 10px;
  color: #333;
}

.thread-excerpt {
  color: #666;
  font-size: 0.95rem;
  line-height: 1.5;
  margin-bottom: 15px;
}

.thread-tags {
  display: flex;
  gap: 10px;
}

.tag {
  background: #f0f0f0;
  color: #666;
  padding: 3px 10px;
  border-radius: 20px;
  font-size: 0.7rem;
  transition: all 0.3s ease;
}

.tag:hover {
  background: #6a11cb;
  color: white;
}

.view-all-threads {
  display: inline-block;
  margin-top: 10px;
  color: #6a11cb;
  font-weight: 600;
  text-decoration: none;
  transition: all 0.3s ease;
}

.view-all-threads:hover {
  color: #44008d;
}

.view-all-threads i {
  margin-left: 5px;
  transition: transform 0.3s ease;
}

.view-all-threads:hover i {
  transform: translateX(5px);
}

.member-spotlight {
  background: white;
  border-radius: 10px;
  padding: 20px;
  box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
  align-self: start;
}

.member-card {
  display: flex;
  align-items: center;
  padding: 15px 0;
  border-bottom: 1px solid #eee;
}

.member-card:last-child {
  border-bottom: none;
}

.member-avatar {
  width: 50px;
  height: 50px;
  border-radius: 50%;
  object-fit: cover;
  margin-right: 15px;
}

.member-info {
  flex: 1;
}

.member-name {
  font-weight: 600;
  color: #333;
  margin-bottom: 3px;
}

.member-sport {
  font-size: 0.8rem;
  color: #666;
  display: block;
  margin-bottom: 5px;
}

.member-stats {
  display: flex;
  gap: 10px;
}

.member-stats .stat {
  font-size: 0.7rem;
  color: #666;
  display: flex;
  align-items: center;
  gap: 3px;
}

.member-stats i {
  color: #6a11cb;
}

.follow-button {
  background: #f8f9fa;
  color: #333;
  border: none;
  width: 30px;
  height: 30px;
  border-radius: 50%;
  cursor: pointer;
  transition: all 0.3s ease;
}

.follow-button:hover {
  background: #6a11cb;
  color: white;
}

.live-feed {
  grid-column: span 2;
  background: white;
  border-radius: 10px;
  padding: 20px;
  margin-top: 40px;
  box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
}

.activity-item {
  display: flex;
  padding: 15px 0;
  border-bottom: 1px solid #eee;
}

.activity-item:last-child {
  border-bottom: none;
}

.activity-avatar img {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  object-fit: cover;
  margin-right: 15px;
}

.activity-content {
  flex: 1;
}

.activity-text {
  color: #666;
  font-size: 0.95rem;
  margin-bottom: 5px;
}

.user-name {
  font-weight: 600;
  color: #333;
}

.activity-target {
  color: #6a11cb;
  font-weight: 600;
}

.activity-time {
  font-size: 0.8rem;
  color: #999;
}




/* App Section */
/* .app-section {
  padding: 100px 0;
  background: linear-gradient(135deg, #17a2b8 0%, #0d6efd 100%);
  color: white;
}

.app-content {
  display: flex;
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 20px;
  align-items: center;
  gap: 50px;
}

.app-info {
  flex: 1;
}

.app-description {
  font-size: 1.2rem;
  margin-bottom: 30px;
  opacity: 0.9;
  line-height: 1.6;
}

.download-buttons {
  display: flex;
  gap: 15px;
}

.download-button {
  display: flex;
  align-items: center;
  padding: 10px 20px;
  border-radius: 8px;
  border: none;
  cursor: pointer;
  transition: all 0.3s ease;
}

.download-button i {
  font-size: 1.8rem;
  margin-right: 10px;
}

.button-text {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  line-height: 1.2;
}

.button-text span:first-child {
  font-size: 0.7rem;
}

.button-text span:last-child {
  font-size: 1.1rem;
  font-weight: 600;
}

.app-store {
  background: black;
  color: white;
}

.google-play {
  background: white;
  color: #333;
}

.app-preview {
  flex: 1;
  text-align: center;
}

.app-screens {
  max-width: 100%;
  height: auto;
  border-radius: 20px;
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
} */


/* Newsletter Section
.newsletter-section {
  padding: 80px 0;
  background: white;
}

.newsletter-container {
  max-width: 1000px;
  margin: 0 auto;
  padding: 0 20px;
  display: flex;
  gap: 50px;
  align-items: center;
}

.newsletter-content {
  flex: 1;
}

.newsletter-text {
  font-size: 1.1rem;
  color: #666;
  margin-bottom: 25px;
  line-height: 1.6;
}

.newsletter-form {
  display: flex;
  margin-bottom: 15px;
}

.email-input {
  flex: 1;
  padding: 12px 20px;
  border: 1px solid #ddd;
  border-radius: 30px 0 0 30px;
  font-size: 1rem;
  outline: none;
}

.subscribe-button {
  background: #17a2b8;
  color: white;
  border: none;
  padding: 0 25px;
  border-radius: 0 30px 30px 0;
  cursor: pointer;
  font-size: 1rem;
  font-weight: 600;
  transition: all 0.3s ease;
}

.subscribe-button:hover {
  background: #1492a7;
}

.privacy-notice {
  display: flex;
  align-items: center;
  gap: 10px;
  font-size: 0.8rem;
  color: #666;
}

.privacy-notice input {
  margin: 0;
}

.newsletter-benefits {
  flex: 1;
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 20px;
}

.benefit-item {
  display: flex;
  align-items: center;
  gap: 10px;
  background: #f8f9fa;
  padding: 15px;
  border-radius: 8px;
  transition: all 0.3s ease;
}

.benefit-item:hover {
  background: #e9ecef;
  transform: translateY(-3px);
}

.benefit-item i {
  font-size: 1.2rem;
  color: #17a2b8;
} */



/* Footer */
.main-footer {
  background: #333;
  color: white;
  padding: 60px 0 0;
}

.footer-content {
  display: grid;
  grid-template-columns: 300px 1fr 250px;
  gap: 50px;
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 20px;
}

.footer-brand {
  margin-bottom: 30px;
}

.brand-logo img {
  max-width: 180px;
  margin-bottom: 20px;
}

.brand-slogan {
  font-size: 0.95rem;
  color: #aaa;
  margin-bottom: 20px;
  line-height: 1.6;
}

.social-links {
  display: flex;
  gap: 15px;
}

.social-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 35px;
  height: 35px;
  background: rgba(255, 255, 255, 0.1);
  border-radius: 50%;
  color: white;
  transition: all 0.3s ease;
}

.social-icon:hover {
  background: #17a2b8;
  transform: translateY(-3px);
}

.footer-links {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 30px;
}

.link-column h3 {
  font-size: 1.1rem;
  margin-bottom: 20px;
  position: relative;
  padding-bottom: 10px;
}

.link-column h3::after {
  content: '';
  position: absolute;
  bottom: 0;
  left: 0;
  width: 40px;
  height: 2px;
  background: #17a2b8;
}

.link-column ul {
  list-style: none;
  padding: 0;
  margin: 0;
}

.link-column li {
  margin-bottom: 12px;
}

.link-column a {
  color: #aaa;
  text-decoration: none;
  font-size: 0.9rem;
  transition: all 0.3s ease;
}

.link-column a:hover {
  color: white;
  padding-left: 5px;
}

.footer-contact h3 {
  font-size: 1.1rem;
  margin-bottom: 20px;
  position: relative;
  padding-bottom: 10px;
}

.footer-contact h3::after {
  content: '';
  position: absolute;
  bottom: 0;
  left: 0;
  width: 40px;
  height: 2px;
  background: #17a2b8;
}

.contact-item {
  display: flex;
  align-items: flex-start;
  gap: 10px;
  margin-bottom: 15px;
  font-size: 0.9rem;
  color: #aaa;
  line-height: 1.5;
}

.contact-item i {
  color: #17a2b8;
  margin-top: 3px;
}

.footer-bottom {
  border-top: 1px solid #444;
  margin-top: 50px;
  padding: 20px 0;
}

.footer-bottom-content {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 20px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.payment-methods {
  display: flex;
  gap: 15px;
  font-size: 1.5rem;
  color: #aaa;
}

.copyright {
  font-size: 0.8rem;
  color: #aaa;
}

.language-selector {
  display: flex;
  align-items: center;
  gap: 10px;
  color: #aaa;
}

.language-selector select {
  background: #444;
  border: none;
  color: white;
  padding: 5px;
  border-radius: 4px;
}

/* Floating Action Buttons */
.fab-container {
  position: fixed;
  bottom: 30px;
  right: 30px;
  z-index: 999;
}

.fab {
  width: 60px;
  height: 60px;
  border-radius: 50%;
  border: none;
  color: white;
  font-size: 1.5rem;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
  transition: all 0.3s ease;
}

.main-fab {
  background: #17a2b8;
  position: relative;
  z-index: 1;
}

.main-fab:hover {
  background: #1492a7;
  transform: scale(1.1);
}

.fab-options {
  position: absolute;
  bottom: 70px;
  right: 0;
  display: flex;
  flex-direction: column;
  gap: 15px;
  opacity: 0;
  pointer-events: none;
  transition: all 0.3s ease;
}

.fab-container:hover .fab-options {
  opacity: 1;
  pointer-events: auto;
  bottom: 80px;
}

.option-fab {
  width: 50px;
  height: 50px;
  background: #333;
  font-size: 1.2rem;
}

.option-fab:hover {
  background: #444;
}

/* Back to Top Button */
.back-to-top {
  position: fixed;
  bottom: 30px;
  right: 30px;
  width: 50px;
  height: 50px;
  background: rgba(23, 162, 184, 0.8);
  color: white;
  border: none;
  border-radius: 50%;
  cursor: pointer;
  font-size: 1.2rem;
  display: flex;
  align-items: center;
  justify-content: center;
  opacity: 0;
  visibility: hidden;
  transition: all 0.3s ease;
  z-index: 998;
}

.back-to-top.visible {
  opacity: 1;
  visibility: visible;
}

.back-to-top:hover {
  background: #17a2b8;
}

/* Responsive Styles */
@media (max-width: 1200px) {
  .hero-title {
    font-size: 3.5rem;
  }

  .community-grid {
    grid-template-columns: 1fr;
  }

  .live-feed {
    grid-column: span 1;
  }
}

@media (max-width: 992px) {
  .events-container {
    grid-template-columns: 1fr;
  }

  .map-view {
    grid-column: span 1;
  }

  .footer-content {
    grid-template-columns: 1fr 1fr;
  }

  .footer-links {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 768px) {
  .hero-title {
    font-size: 2.8rem;
  }

  .hero-subtitle {
    font-size: 1.2rem;
  }

  .app-content {
    flex-direction: column;
    text-align: center;
  }

  .download-buttons {
    justify-content: center;
  }

  .newsletter-container {
    flex-direction: column;
    text-align: center;
  }

  .newsletter-form {
    flex-direction: column;
  }

  .email-input {
    border-radius: 30px;
    margin-bottom: 10px;
  }

  .subscribe-button {
    border-radius: 30px;
  }

  .footer-content {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 576px) {
  .hero-title {
    font-size: 2.2rem;
  }

  .hero-stats {
    flex-direction: column;
    gap: 20px;
  }

  .section-title {
    font-size: 2rem;
  }

  .footer-links {
    grid-template-columns: 1fr;
  }
}

/* Animations */
@keyframes fadeIn {
  from {
    opacity: 0;
  }

  to {
    opacity: 1;
  }
}

@keyframes slideUp {
  from {
    opacity: 0;
    transform: translateY(30px);
  }

  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.animate {
  animation: fadeIn 0.6s ease forwards, slideUp 0.6s ease forwards;
}
</style>