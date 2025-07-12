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
          <p class="hero-subtitle">Eventos • Entrenamiento • Tienda • Comunidad</p>

          <div v-if="!user" class="hero-cta">
            <router-link :to="{ path: '/signup', query: { panel: 'signup' } }" class="cta-button">
              Únete a la Comunidad
            </router-link>
          </div>

        </div>

        <div class="hero-stats">
          <div class="stat-item">
            <div class="stat-number">{{ stats.users || 0 }}</div>
            <div class="stat-label">Miembros</div>
          </div>
          <div class="stat-item">
            <div class="stat-number">{{ stats.events || 0 }}</div>
            <div class="stat-label">Eventos</div>
          </div>
          <div class="stat-item">
            <div class="stat-number">{{ stats.posts || 0 }}</div>
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
        <router-link v-if="user" to="/directorio" class="view-all">Ver todos <i
            class="fas fa-arrow-right"></i></router-link>
      </div>

      <div class="category-grid">
        <div v-for="(category, index) in categories" :key="category.name" class="category-card">
          <div class="card-inner">
            <div class="card-front">
              <img :src="category.image" :alt="category.name" class="card-image">
              <div class="card-overlay"></div>
              <div class="card-badge" v-if="category.popular">Popular</div>
              <div class="participation-rate">
                <div class="rate-bar" :style="{ width: category.participation + '%' }"></div>
                <span>{{ category.participation }}% popularidad</span>
              </div>
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
        <router-link v-if="user" to="/noticias" class="view-all-news">
          Ver todas las noticias <i class="fas fa-arrow-right"></i>
        </router-link>
      </div>

      <div class="news-grid">
        <!-- Noticia Destacada -->
        <div v-if="recentNews.length > 0" class="featured-news">
          <div class="featured-image">
            <img :src="recentNews[0].image" :alt="recentNews[0].title">
            <div class="news-badge">Destacada</div>
            <div class="category-tag">{{ recentNews[0].category || 'General' }}</div>
          </div>
          <div class="featured-content">
            <div class="news-meta">
              <span class="date"><i class="far fa-calendar-alt"></i> {{ formatNewsDate(recentNews[0].published_at)
                }}</span>
              <span class="author"><i class="far fa-user"></i> Por {{ recentNews[0].author }}</span>
            </div>
            <h3 class="news-title">{{ recentNews[0].title }}</h3>
            <p class="news-excerpt">{{ recentNews[0].description.substring(0, 150) }}...</p>
            <div class="news-actions">
            </div>
          </div>
        </div>

        <!-- Listado de Noticias -->
        <div class="news-list" v-if="recentNews.length > 1">
          <div class="news-card" v-for="(news, index) in recentNews.slice(1, 7)" :key="index">
            <div class="news-card-image">
              <img :src="news.image" :alt="news.title">
              <div class="category-tag">{{ news.category || 'General' }}</div>
            </div>
            <div class="news-card-content">
              <div class="news-meta">
                <span class="date"><i class="far fa-calendar-alt"></i> {{ formatNewsDate(news.published_at) }}</span>
              </div>
              <h4 class="news-title">{{ news.title }}</h4>
              <p class="news-excerpt">{{ news.description.substring(0, 100) }}...</p>
            </div>
          </div>
        </div>

        <div v-if="recentNews.length === 0" class="loading-message">
          Cargando noticias...
        </div>
      </div>
    </section>



    <!-- Eventos -->
    <section class="events-section">
      <div class="section-header">
        <h2 class="section-title">Eventos Destacados</h2>
        <p class="section-description">No te pierdas los próximos eventos deportivos</p>
        <router-link v-if="user" to="/calendario" class="view-all-calendar">
          Ver todos los eventos <i class="fas fa-arrow-right"></i>
        </router-link>
      </div>

      <div class="events-container">

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
            </div>
          </div>
        </div>

      </div>
    </section>



    <!-- Productos -->
    <section class="products-section">
      <div class="section-header">
        <h2 class="section-title">Equipamiento Premium</h2>
        <p class="section-description">Los mejores productos para tu rendimiento</p>
        <router-link v-if="user" to="/tienda" class="view-all-products">
          Ver todos los productos <i class="fas fa-arrow-right"></i>
        </router-link>
      </div>

      <div class="products-carousel">

        <div v-if="recentProducts.length === 0" class="no-products">
          <i class="fas fa-box-open"></i>
          <p>No hay productos disponibles en este momento</p>
        </div>

        <div v-else class="product-card" v-for="product in recentProducts" :key="product.id">
          <div class="product-badges">
            <div class="badge featured" v-if="product.featured">Destacado</div>
          </div>
          <div class="product-image-container">
            <img :src="product.image" :alt="product.name" class="product-image">
          </div>
          <div class="product-info">
            <h3 class="product-name">{{ product.name }}</h3>
            <div class="product-rating">
              <div class="stars">
                <i class="fas fa-star" v-for="n in 5" :key="n" :class="{ 'filled': n <= product.rating }"></i>
              </div>
            </div>
            <div class="product-pricing">
              <span class="current-price">${{ product.price }}</span>
              <span class="original-price" v-if="product.originalPrice">${{ product.originalPrice }}</span>
            </div>
            <!-- <div class="product-actions">
              <button v-if="user" class="add-to-cart">
                <i class="fas fa-shopping-cart"></i> Añadir
              </button>
            </div> -->
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

          <div class="forum-thread" v-for="post in popularPosts" :key="post.id">
            <div class="thread-header">
              <img :src="post.user?.image_url || '/storage/users/Perfil-Icon.png'" :alt="post.user?.name || 'Usuario'"
                class="author-avatar">

              <div class="author-info">
                <span class="author-name">
                  {{ post.user?.name || 'Usuario Anónimo' }}
                </span>

                <span class="thread-date">
                  {{ formatDate(post.created_at) }}
                </span>
              </div>

              <div class="thread-stats">
                <span class="stat">
                  <i class="fas fa-comments"> Comentarios </i> {{ post.comments_count || 0 }}
                </span>
                <span class="stat">
                  <i class="fas fa-heart"> Likes </i> {{ post.likes_count || 0 }}
                </span>
              </div>
            </div>

            <h4 class="thread-title">{{ post.titulo }}</h4>

            <p class="thread-excerpt">
              {{ (post.contenido || '').substring(0, 100) }}...
            </p>
          </div>

          <router-link v-if="user" to="/foro" class="view-all-threads">
            Ver todas las discusiones <i class="fas fa-arrow-right"></i>
          </router-link>
        </div>
      </div>
    </section>


    <!-- Footer -->
    <footer class="main-footer">
      <div class="footer-content">
        <div class="footer-brand">
          <div class="brand-logo">
            <img src="/imagenes/Logo2.png" alt="SportFamilyRD Logo">
          </div>
          <p class="brand-slogan">Conectando la comunidad deportiva dominicana</p>
          <!-- <div class="social-links">
            <a href="#" class="social-icon"><i class="fab fa-facebook-f"></i></a>
            <a href="#" class="social-icon"><i class="fab fa-instagram"></i></a>
            <a href="#" class="social-icon"><i class="fab fa-twitter"></i></a>
            <a href="#" class="social-icon"><i class="fab fa-youtube"></i></a>
            <a href="#" class="social-icon"><i class="fab fa-tiktok"></i></a>
          </div> -->
        </div>

        <div class="footer-links">
          <!-- <div class="link-column">
            <h3 class="links-title">Explorar</h3>
            <ul>
              <li><a href="#">Deportes</a></li>
              <li><a href="#">Noticias</a></li>
              <li><a href="#">Eventos</a></li>
              <li><a href="#">Tienda</a></li>
              <li><a href="#">Entrenadores</a></li>
              <li><a href="#">Foro Deportivo</a></li>
            </ul>
          </div> -->
          <!-- <div class="link-column">
            <h3 class="links-title">Comunidad</h3>
            <ul>
              <li><a href="#">Foro</a></li>
              <li><a href="#">Grupos</a></li>
              <li><a href="#">Blog</a></li>
              <li><a href="#">Testimonios</a></li>
              <li><a href="#">Patrocinadores</a></li>
            </ul>
          </div> -->
          <!-- <div class="link-column">
            <h3 class="links-title">Empresa</h3>
            <ul>
              <li><a href="#">Nosotros</a></li>
              <li><a href="#">Contacto</a></li>
              <li><a href="#">Trabaja con Nosotros</a></li>
              <li><a href="#">Prensa</a></li>
              <li><a href="#">Partners</a></li>
            </ul>
          </div> -->
          <!-- <div class="link-column">
            <h3 class="links-title">Legal</h3>
            <ul>
              <li><a href="#">Términos</a></li>
              <li><a href="#">Privacidad</a></li>
              <li><a href="#">Cookies</a></li>
              <li><a href="#">DMCA</a></li>
              <li><a href="#">Aviso Legal</a></li>
            </ul>
          </div> -->
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


    <!-- Back to Top Button -->
    <button class="back-to-top" @click="scrollToTop">
      <i class="fas fa-arrow-up"></i>
    </button>
  </div>

  <!-- Burbuja de Mensajes Flotante -->
  <ChatBubbleComponent v-if="user" :user="user" />

</template>




<script>
import axios from 'axios';
import Navbar from '../navbarComponent.vue';
import ChatBubbleComponent from '../ChatBubbleComponent.vue';

function throttle(func, wait) {
  let timeout = null;
  let lastCall = 0;

  return function executedFunction(...args) {
    const context = this;
    const now = Date.now();
    const timeSinceLastCall = now - lastCall;

    const later = () => {
      if (func) {
        func.apply(context, args);
      }
      lastCall = Date.now();
    };

    if (timeSinceLastCall >= wait) {
      later();
    } else {
      clearTimeout(timeout);
      timeout = setTimeout(later, wait - timeSinceLastCall);
    }
  };
}

export default {
  components: {
    Navbar,
    ChatBubbleComponent
  },
  data() {
    return {
      user: null,
      showModal: false,
      selectedProduct: null,
      recentNews: [],
      recentProducts: [],
      popularPosts: [],
      stats: {
        users: 0,
        events: 0,
        posts: 0,
      },
      isLoading: false,
      featuredEvents: [], // Ahora se inicializa vacío, se llenará dinámicamente
      categories: [
        {
          name: "Baseball",
          image: "/imagenes/DirectorioDeDeportes/baseball.jpg",
          slug: "futbol",
          popular: true,
          participation: 90,
        },
        {
          name: "Basketball",
          image: "/imagenes/DirectorioDeDeportes/Baloncesto.jpg",
          slug: "futbol",
          popular: true,
          participation: 50,
        },
        {
          name: "Domino",
          image: "/imagenes/DirectorioDeDeportes/Domino.jpg",
          slug: "futbol",
          popular: true,
          participation: 60,
        },
      ],
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


    async fetchInitialData() {
      this.isLoading = true;
      try {
        const [stats, news, products, posts] = await Promise.all([
          axios.get('/home-stats'),
          axios.get('/recent-news'),
          axios.get('/recent-products'),
          axios.get('/popular-posts')
        ]);

        // Debuggear respuestas
        console.log("Stats:", stats.data);
        console.log("News:", news.data);
        console.log("Products:", products.data);
        console.log("Posts:", posts.data);

        this.stats = stats.data || {};
        this.recentNews = news.data || [];
        this.recentProducts = products.data.products || [];

        // Extraer posts de la respuesta
        this.popularPosts = this.extractPopularPosts(posts);
        console.log("Popular posts:", this.popularPosts);

      } catch (error) {
        console.error('Error fetching data:', error);
        this.popularPosts = this.getSamplePosts();
      } finally {
        this.isLoading = false;
      }
    },


    animateElements() {
      const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
          if (entry.isIntersecting) {
            entry.target.classList.add('animate');
            observer.unobserve(entry.target);
          }
        });
      }, { threshold: 0.1 });

      document.querySelectorAll('.category-card, .event-card, .product-card').forEach(card => {
        observer.observe(card);
      });
    },


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

    getCalendarScrap() {
      axios.get('/scrap-calendar')
        .then(response => {
          console.log("Calendar data fetched successfully:", response.data.events);
        })
        .catch(error => {
          console.error("Error fetching calendar data:", error);
        });
    },

    fetchRecentNews() {
      axios.get('/recent-news')
        .then(response => {
          // Formatear las fechas para mostrarlas correctamente
          this.recentNews = response.data.map(news => ({
            ...news,
            date: new Date(news.published_at).toLocaleDateString('es-ES', {
              day: 'numeric',
              month: 'long',
              year: 'numeric'
            })
          }));
        })
        .catch(error => {
          console.error('Error fetching recent news:', error);
        });
    },

    fetchRecentProducts() {
      axios.get('/recent-products')
        .then(response => {
          this.recentProducts = response.data;
        })
        .catch(error => {
          console.error('Error fetching recent products:', error);
        });
    },

    fetchPopularPosts() {
      axios.get('/popular-posts')
        .then(response => {
          this.popularPosts = response.data;
        })
        .catch(error => {
          console.error('Error fetching popular posts:', error);
        });
    },

    async fetchFeaturedEvents() {
      try {
        const response = await axios.get('/featured-events');
        this.featuredEvents = response.data.events || [];
      } catch (error) {
        this.featuredEvents = [
          {
            id: 1,
            title: "Torneo Nacional de Baseball",
            date: "15/Jul",
            time: "4:00 PM",
            location: "Estadio Quisqueya, Santo Domingo",
            description: "La gran final del torneo nacional con los mejores equipos"
          },
        ];
        console.error('Error fetching featured events:', error);
      }
    },

    formatDate(dateString) {
      if (!dateString) return '';
      const date = new Date(dateString);
      return date.toLocaleDateString('es-ES', {
        day: 'numeric',
        month: 'long',
        year: 'numeric'
      });
    },

    formatNewsDate(dateString) {
      return this.formatDate(dateString);
    },


    extractPopularPosts(response) {
      try {
        const data = response.data;

        // Verificar si la respuesta tiene posts
        if (data && data.posts && Array.isArray(data.posts)) {
          return data.posts.slice(0, 4);
        }

        // Si no hay posts, devolver un array vacío
        return [];

      } catch (e) {
        console.error('Error procesando posts populares:', e);
        return [];
      }
    },


    getSamplePosts() {
      return [
        {
          id: 1,
          titulo: "Mejores lugares para jugar baloncesto",
          contenido: "Descubre los mejores courts de baloncesto en Santo Domingo...",
          created_at: new Date().toISOString(),
          comments_count: 15,
          likes_count: 42,
          user: {
            id: 1,
            name: "Usuario Ejemplo",
            image_url: "/default-avatar.png"
          }
        },
        {
          id: 2,
          titulo: "Consejos para mejorar tu bateo",
          contenido: "Comparto algunos tips que me ayudaron a mejorar mi promedio de bateo...",
          created_at: new Date().toISOString(),
          comments_count: 8,
          likes_count: 24,
          user: {
            id: 2,
            name: "Bateador Pro",
            image_url: "/default-avatar.png"
          }
        }
      ];
    }



  },

  mounted() {
    document.title = 'SportFamilyRD - Comunidad Deportiva Dominicana';
    this.fetchInitialData();
    this.animateElements();
    this.fetchFeaturedEvents(); // Llama a la función para cargar los eventos reales

    const userData = sessionStorage.getItem('user');
    this.user = userData ? JSON.parse(userData) : null;

    // Optimizar scroll
    this.throttledScroll = throttle(this.handleScroll, 100);
    window.addEventListener('scroll', this.throttledScroll);
  },

  beforeUnmount() {
    window.removeEventListener('scroll', this.throttledScroll);
  }
}
</script>



<style scoped>
@import '../../../scss/Home/home.scss';

@import '../../../scss/Home/home_navbar.scss';


img {
  content-visibility: auto;
}

.category-card,
.event-card,
.product-card {
  will-change: transform, opacity;
}

.loading-message {
  min-height: 300px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.community-section {
  .author-avatar {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    object-fit: cover;
    margin-right: 15px;
    border: 2px solid #3498db;
  }

  .thread-header {
    display: flex;
    align-items: center;
    margin-bottom: 15px;
  }

  .author-info {
    flex-grow: 1;
  }

  .thread-stats {
    display: flex;
    gap: 15px;

    .stat {
      display: flex;
      align-items: center;
      gap: 5px;
    }
  }
}

.hero-cta {
  margin-top: 2rem;
  animation: fadeInUp 0.8s ease-out 0.5s both;
}


/* BOTON EN NARANJA */

/* .cta-button {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 1rem 2rem;
  background: linear-gradient(135deg, #e28b4c, #ff3c00);
  color: white;
  font-weight: bold;
  font-size: 1.2rem;
  border-radius: 50px;
  text-decoration: none;
  transition: all 0.3s ease;
  box-shadow: 0 8px 20px rgba(255, 107, 0, 0.4);
  border: 2px solid rgba(255, 255, 255, 0.2);
  position: relative;
  overflow: hidden;
}

.cta-button:hover {
  transform: translateY(-5px);
  box-shadow: 0 12px 25px rgba(255, 107, 0, 0.6);
  background: linear-gradient(135deg, #ff7a1a, #ff4d00);
}

.cta-button:active {
  transform: translateY(2px);
  box-shadow: 0 4px 15px rgba(255, 107, 0, 0.4);
}

.cta-button i {
  margin-left: 10px;
  transition: transform 0.3s ease;
}

.cta-button:hover i {
  transform: translateX(5px);
}

@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(30px);
  }

  to {
    opacity: 1;
    transform: translateY(0);
  }
} */


/* BOTON EN ROJO  */

.cta-button {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 1rem 2rem;
  background: #830d1b;
  color: white;
  font-weight: bold;
  font-size: 1.2rem;
  border-radius: 50px;
  text-decoration: none;
  transition: all 0.3s ease;
  position: relative;
  overflow: hidden;
}

.cta-button:hover {
  transform: translateY(-5px);
}

.cta-button:active {
  transform: translateY(2px);
  box-shadow: 0 4px 15px rgba(255, 0, 0, 0.4);
}

.cta-button i {
  margin-left: 10px;
  transition: transform 0.3s ease;
}

.cta-button:hover i {
  transform: translateX(5px);
}

@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(30px);
  }

  to {
    opacity: 1;
    transform: translateY(0);
  }
}


.no-products {
  text-align: center;
  padding: 40px;
  color: #666;
  font-size: 1.2rem;
  width: 100%;
  justify-content: center;
}

.no-products i {
  font-size: 3rem;
  margin-bottom: 15px;
  display: block;
  color: #ccc;
  justify-content: center;
}
</style>
