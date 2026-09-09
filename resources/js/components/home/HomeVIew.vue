<template>

  <div class="home-container">

    <!-- Navbar -->
    <Navbar />

    <!-- Hero Section -->
    <section class="hero-banner">
      <div class="hero-content">
        <div class="hero-text">
          <h1 class="hero-title">
            <span class="title-line">Conecta con la</span>
            <span class="title-line highlight">Comunidad Deportiva</span>
            <span class="title-line">Dominicana</span>
          </h1>
          <!-- <p class="hero-subtitle">Eventos • Entrenamiento • Tienda • Comunidad</p> -->

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
          <!-- <div class="stat-item">
            <div class="stat-number">{{ stats.news || 0 }}</div>
            <div class="stat-label">Eventos</div>
          </div> -->
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
        <router-link to="/directorio" class="view-all"> Ver todos <i
            class="fas fa-arrow-right"></i></router-link>
      </div>

      <div class="category-grid">
        <!-- 'ensureSports' en mouseenter/focus: el directorio ya se esta pidiendo
             mientras el usuario mueve el raton hacia la tarjeta, asi que al hacer
             click la ficha normalmente ya esta lista. -->
        <div v-for="category in categories" :key="category.name" class="category-card is-interactive" role="button"
          tabindex="0" :aria-label="`Ver detalles de ${category.name}`" @click="openSport(category)"
          @mouseenter="ensureSports()" @focus="ensureSports()"
          @keydown.enter.prevent="openSport(category)" @keydown.space.prevent="openSport(category)">
          <div class="card-inner">
            <div class="card-front">
              <img :src="category.image" :alt="category.name" class="card-image" loading="lazy">
              <div class="card-overlay"></div>
              <div class="card-badge" v-if="category.popular">Popular</div>
              <span class="hover-hint">{{ category.name }} · Ver detalles</span>
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
        <router-link to="/noticias" class="view-all-news">
          Ver todas las noticias <i class="fas fa-arrow-right"></i>
        </router-link>
      </div>

      <div class="news-grid">
        <!-- Noticia Destacada -->
        <div v-if="recentNews.length > 0" class="featured-news is-interactive" role="button" tabindex="0"
          :aria-label="`Leer noticia: ${recentNews[0].title}`" @click="openNews(recentNews[0])"
          @keydown.enter.prevent="openNews(recentNews[0])" @keydown.space.prevent="openNews(recentNews[0])">
          <div class="featured-image">
            <img :src="recentNews[0].image" :alt="recentNews[0].title" loading="lazy">
            <div class="news-badge">Destacada</div>
            <div class="category-tag">{{ recentNews[0].category || 'General' }}</div>
            <span class="hover-hint">Leer noticia</span>
          </div>
          <div class="featured-content">
            <div class="news-meta">
              <span class="date"><i class="far fa-calendar-alt"></i> {{ formatNewsDate(recentNews[0].published_at)
              }}</span>
              <span class="author"><i class="far fa-user"></i> Por {{ recentNews[0].author }}</span>
            </div>
            <h3 class="news-title">{{ recentNews[0].title }}</h3>
            <p class="news-excerpt">{{ truncate(recentNews[0].description, 150) }}</p>
            <div class="news-actions">
              <button type="button" class="read-more" @click.stop="openNews(recentNews[0])">
                Leer más <i class="fas fa-arrow-right"></i>
              </button>
            </div>
          </div>
        </div>

        <!-- Listado de Noticias -->
        <div class="news-list" v-if="recentNews.length > 1">
          <div class="news-card is-interactive" v-for="news in recentNews.slice(1, 7)" :key="news.id" role="button"
            tabindex="0" :aria-label="`Leer noticia: ${news.title}`" @click="openNews(news)"
            @keydown.enter.prevent="openNews(news)" @keydown.space.prevent="openNews(news)">
            <div class="news-card-image">
              <img :src="news.image" :alt="news.title" loading="lazy">
              <div class="category-tag">{{ news.category || 'General' }}</div>
            </div>
            <div class="news-card-content">
              <div class="news-meta">
                <span class="date"><i class="far fa-calendar-alt"></i> {{ formatNewsDate(news.published_at) }}</span>
              </div>
              <h4 class="news-title">{{ news.title }}</h4>
              <p class="news-excerpt">{{ truncate(news.description, 100) }}</p>
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
        <router-link to="/calendario" class="view-all-calendar">
          Ver todos los eventos <i class="fas fa-arrow-right"></i>
        </router-link>
      </div>

      <div class="events-container">

        <div class="featured-events">
          <div class="event-card is-interactive" v-for="event in featuredEvents" :key="event.event_id || event.id"
            role="button" tabindex="0" :aria-label="`Ver detalles del evento: ${event.Title}`"
            @click="openEvent(event)" @keydown.enter.prevent="openEvent(event)"
            @keydown.space.prevent="openEvent(event)">
            <div class="event-date">
              <div class="date-day">{{ event.date.split('/')[0] }}</div>
              <div class="date-month">{{ event.date.split('/')[1] }}</div>
            </div>
            <div class="event-details">
              <h3 class="event-title">{{ event.Title }}</h3>
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
        <router-link to="/tienda" class="view-all-products">
          Ver todos los productos <i class="fas fa-arrow-right"></i>
        </router-link>
      </div>

      <div class="products-carousel">

        <div v-if="recentProducts.length === 0" class="no-products">
          <i class="fas fa-box-open"></i>
          <p>No hay productos disponibles en este momento</p>
        </div>

        <div v-else class="product-card is-interactive" v-for="product in recentProducts" :key="product.id"
          role="button" tabindex="0" :aria-label="`Ver detalles de ${product.name}`" @click="openProduct(product)"
          @keydown.enter.prevent="openProduct(product)" @keydown.space.prevent="openProduct(product)">
          <div class="product-badges">
            <div class="badge featured" v-if="product.featured">Destacado</div>
          </div>
          <div class="product-image-container">
            <img :src="product.image" :alt="product.name" class="product-image" loading="lazy">
            <span class="hover-hint">Ver detalles</span>
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

          <div class="forum-thread is-interactive" v-for="post in popularPosts" :key="post.id" role="button"
            tabindex="0" :aria-label="`Ver discusión: ${post.titulo}`" @click="openPost(post)"
            @keydown.enter.prevent="openPost(post)" @keydown.space.prevent="openPost(post)">
            <div class="thread-header">
              <img :src="getUserImage(post.user)" :alt="post.user?.name || 'Usuario'" class="author-avatar"
                loading="lazy">

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
              {{ truncate(post.contenido, 100) }}
            </p>
          </div>

          <router-link to="/foro" class="view-all-threads">
            Ver todas las discusiones <i class="fas fa-arrow-right"></i>
          </router-link>
        </div>
      </div>
    </section>


    <!-- =====================================================================
         Footer

         La rejilla era 300px / 1fr / 250px con la columna del medio vacia
         (todos los bloques de enlaces estaban comentados), asi que el pie
         quedaba con el logo pegado a la izquierda, un hueco enorme y el
         contacto perdido a la derecha. Ahora la columna del medio tiene
         enlaces reales del router y el pie cierra la pagina en vez de
         cortarla.
         ===================================================================== -->
    <footer class="main-footer">
      <div class="footer-content">

        <div class="footer-brand">
          <div class="brand-logo">
            <img src="/imagenes/Logo2.png" alt="SportFamilyRD" width="170" height="60">
          </div>
          <p class="brand-slogan">
            Conectando la comunidad deportiva dominicana: eventos, entrenadores,
            noticias y tienda en un solo lugar.
          </p>
          <router-link v-if="!user" :to="{ path: '/signup', query: { panel: 'signup' } }" class="footer-cta">
            Únete a la comunidad <svg class="footer-ico" viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h13M13 6l6 6-6 6"/></svg>
          </router-link>
        </div>

        <nav class="footer-links" aria-label="Enlaces del pie de página">
          <div class="link-column">
            <h3>Explorar</h3>
            <ul>
              <li><router-link to="/directorio">Deportes</router-link></li>
              <li><router-link to="/noticias">Noticias</router-link></li>
              <li><router-link to="/calendario">Eventos</router-link></li>
              <li><router-link to="/tienda">Tienda</router-link></li>
            </ul>
          </div>

          <div class="link-column">
            <h3>Comunidad</h3>
            <ul>
              <li><router-link to="/foro">Foro deportivo</router-link></li>
              <li><router-link to="/entrenadores">Entrenadores</router-link></li>
              <li v-if="user"><router-link to="/perfil">Mi perfil</router-link></li>
              <li v-else>
                <router-link :to="{ path: '/signup', query: { panel: 'login' } }">Iniciar sesión</router-link>
              </li>
            </ul>
          </div>
        </nav>

        <div class="footer-contact">
          <h3>Contacto</h3>
          <a class="contact-item" href="mailto:info@sportfamilyrd.com">
            <span class="contact-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="2.5" y="5" width="19" height="14" rx="2"/><path d="M3 7l9 6 9-6"/></svg></span>
            <span>info@sportfamilyrd.com</span>
          </a>
          <a class="contact-item" href="tel:+18498814028">
            <span class="contact-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6.6 4h-2A1.6 1.6 0 003 5.7C3 13 11 21 18.3 21a1.6 1.6 0 001.7-1.6v-2l-4-1.6-2 2a13 13 0 01-6.8-6.8l2-2L6.6 4z"/></svg></span>
            <span>(849) 881-4028</span>
          </a>
          <div class="contact-item contact-item--static">
            <span class="contact-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 21s7-6.3 7-11a7 7 0 10-14 0c0 4.7 7 11 7 11z"/><circle cx="12" cy="10" r="2.6"/></svg></span>
            <span>Santo Domingo, República Dominicana</span>
          </div>
        </div>

      </div>

      <div class="footer-bottom">
        <div class="footer-bottom-content">
          <p class="copyright">© {{ currentYear }} SportFamilyRD. Todos los derechos reservados.</p>
          <p class="footer-made">Hecho en República Dominicana</p>
          <button type="button" class="footer-top-link" @click="scrollToTop">
            Volver arriba <svg class="footer-ico" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 19V5M6 11l6-6 6 6"/></svg>
          </button>
        </div>
      </div>
    </footer>

    <!-- =====================================================================
         Pop-outs del Home

         Una sola instancia de HomeModal para todo: 'modal.type' decide que
         contenido se pinta dentro. Ninguna variante usa v-html; el texto que
         viene de la base de datos (noticias scrapeadas, posts de usuarios,
         descripciones de productos) se pinta como texto plano y los saltos de
         linea los respeta el CSS, asi no hay forma de inyectar HTML/JS por ahi.
         ===================================================================== -->

    <HomeModal :open="modal.open" :variant="modal.type" @close="closeModal" v-slot="{ titleId }">

      <!-- Noticia -->
      <template v-if="modal.type === 'news' && modal.data">
        <div class="hm-media">
          <img :src="modal.data.image" :alt="modal.data.title">
          <span class="hm-media__chip">{{ modal.data.category || 'General' }}</span>
        </div>
        <div class="hm-body">
          <h2 class="hm-title" :id="titleId">{{ modal.data.title }}</h2>
          <div class="hm-meta">
            <span><i class="far fa-calendar-alt"></i> {{ formatNewsDate(modal.data.published_at) }}</span>
            <span v-if="modal.data.author"><i class="far fa-user"></i> Por {{ modal.data.author }}</span>
          </div>
          <p class="hm-text">{{ modal.data.description }}</p>
          <hr class="hm-divider">
          <div class="hm-actions">
            <button v-if="user" type="button" class="hm-btn"
              :class="isNewsSaved(modal.data.id) ? 'hm-btn--saved' : 'hm-btn--primary'" :disabled="isSavingNews"
              @click="toggleSaveNews(modal.data)">
              <i class="fas fa-bookmark"></i>
              {{ isNewsSaved(modal.data.id) ? 'Guardada' : 'Guardar noticia' }}
            </button>
            <router-link v-else :to="{ path: '/signup', query: { panel: 'login' } }" class="hm-btn hm-btn--primary">
              Inicia sesión para guardarla
            </router-link>
            <router-link to="/noticias" class="hm-btn hm-btn--ghost">
              Ver todas las noticias <i class="fas fa-arrow-right"></i>
            </router-link>
          </div>
        </div>
      </template>

      <!-- Producto -->
      <template v-else-if="modal.type === 'product' && modal.data">
        <div class="hm-media">
          <img :src="modal.data.image" :alt="modal.data.name">
        </div>
        <div class="hm-body">
          <span class="hm-eyebrow">{{ modal.data.category || 'Tienda' }}</span>
          <h2 class="hm-title" :id="titleId">{{ modal.data.name }}</h2>
          <div class="hm-price">
            <span class="hm-price__current">RD$ {{ modal.data.price }}</span>
            <span v-if="modal.data.originalPrice" class="hm-price__old">RD$ {{ modal.data.originalPrice }}</span>
          </div>
          <p class="hm-text">{{ modal.data.description || 'Este producto todavía no tiene descripción.' }}</p>
          <p class="hm-note" v-if="hasStockInfo(modal.data)">
            {{ maxQuantity > 0 ? `${maxQuantity} unidades disponibles` : 'Agotado por el momento' }}
          </p>
          <hr class="hm-divider">
          <div class="hm-actions" v-if="user">
            <div class="hm-qty">
              <button type="button" @click="decrementQuantity" :disabled="quantity <= 1"
                aria-label="Quitar una unidad">−</button>
              <span aria-live="polite">{{ quantity }}</span>
              <button type="button" @click="incrementQuantity" :disabled="quantity >= maxQuantity"
                aria-label="Agregar una unidad">+</button>
            </div>
            <button type="button" class="hm-btn hm-btn--primary" :disabled="isAddingToCart || maxQuantity < 1"
              @click="addProductToCart(modal.data)">
              <i class="fas fa-shopping-cart"></i>
              {{ isAddingToCart ? 'Agregando...' : 'Agregar al carrito' }}
            </button>
            <router-link to="/tienda" class="hm-btn hm-btn--ghost">Ver la tienda</router-link>
          </div>
          <div class="hm-actions" v-else>
            <router-link :to="{ path: '/signup', query: { panel: 'login' } }" class="hm-btn hm-btn--primary">
              Inicia sesión para comprar
            </router-link>
            <router-link to="/tienda" class="hm-btn hm-btn--ghost">Ver la tienda</router-link>
          </div>
        </div>
      </template>

      <!-- Evento -->
      <template v-else-if="modal.type === 'event' && modal.data">
        <div class="hm-media" v-if="modal.data.image">
          <img :src="modal.data.image" :alt="modal.data.Title">
        </div>
        <div class="hm-body">
          <div class="hm-event-head">
            <div class="hm-date-badge">
              <div class="hm-date-badge__day">{{ eventDay(modal.data) }}</div>
              <div class="hm-date-badge__month">{{ eventMonth(modal.data) }}</div>
            </div>
            <h2 class="hm-title" :id="titleId">{{ modal.data.Title }}</h2>
          </div>
          <div class="hm-meta">
            <span><i class="fas fa-clock"></i> {{ modal.data.time || 'Hora por confirmar' }}</span>
            <span><i class="fas fa-map-marker-alt"></i> {{ modal.data.location || 'Ubicación por confirmar' }}</span>
          </div>
          <p class="hm-text">{{ modal.data.description || 'Este evento todavía no tiene descripción.' }}</p>
          <hr class="hm-divider">
          <div class="hm-price" v-if="modal.data.price">
            <span class="hm-price__current">RD$ {{ modal.data.price }}</span>
            <span class="hm-note">por boleta</span>
          </div>
          <p class="hm-note" v-if="hasStockInfo(modal.data)">
            {{ maxQuantity > 0 ? `${maxQuantity} boletas disponibles` : 'Boletas agotadas' }}
          </p>
          <div class="hm-actions" v-if="user && canBuyEvent(modal.data)">
            <div class="hm-qty">
              <button type="button" @click="decrementQuantity" :disabled="quantity <= 1"
                aria-label="Quitar una boleta">−</button>
              <span aria-live="polite">{{ quantity }}</span>
              <button type="button" @click="incrementQuantity" :disabled="quantity >= maxQuantity"
                aria-label="Agregar una boleta">+</button>
            </div>
            <button type="button" class="hm-btn hm-btn--primary" :disabled="isAddingToCart"
              @click="addEventToCart(modal.data)">
              <i class="fas fa-ticket-alt"></i>
              {{ isAddingToCart ? 'Agregando...' : `Añadir al carrito - RD$ ${modal.data.price * quantity}` }}
            </button>
            <router-link to="/calendario" class="hm-btn hm-btn--ghost">Ver el calendario</router-link>
          </div>
          <div class="hm-actions" v-else>
            <router-link v-if="!user" :to="{ path: '/signup', query: { panel: 'login' } }"
              class="hm-btn hm-btn--primary">
              Inicia sesión para comprar boletas
            </router-link>
            <router-link to="/calendario" class="hm-btn hm-btn--ghost">Ver el calendario</router-link>
          </div>
        </div>
      </template>

      <!-- Publicación del foro -->
      <template v-else-if="modal.type === 'post' && modal.data">
        <div class="hm-media" v-if="postTieneImagen(modal.data)">
          <img :src="modal.data.imagen" :alt="`Imagen de: ${modal.data.titulo}`">
        </div>
        <div class="hm-body">
          <span class="hm-eyebrow" v-if="modal.data.categoria">{{ modal.data.categoria }}</span>
          <div class="hm-author">
            <img :src="getUserImage(modal.data.user)" alt="">
            <div>
              <span class="hm-author__name">{{ modal.data.user?.name || 'Usuario Anónimo' }}</span>
              <span class="hm-author__date">{{ formatDate(modal.data.created_at) }}</span>
            </div>
          </div>
          <h2 class="hm-title" :id="titleId">{{ modal.data.titulo }}</h2>
          <p class="hm-text">{{ modal.data.contenido }}</p>
          <div class="hm-stats">
            <span><i class="fas fa-heart"></i> {{ modal.data.likes_count || 0 }} likes</span>
            <span><i class="fas fa-comments"></i> {{ modal.data.comments_count || 0 }} comentarios</span>
          </div>
          <hr class="hm-divider">
          <div class="hm-actions">
            <router-link to="/foro" class="hm-btn hm-btn--primary">
              {{ user ? 'Comentar en el foro' : 'Ir al foro' }} <i class="fas fa-arrow-right"></i>
            </router-link>
          </div>
        </div>
      </template>

      <!-- Deporte del directorio -->
      <template v-else-if="modal.type === 'sport' && modal.data">
        <!-- Portada: el titulo va SOBRE la foto (no debajo) para que el
             deporte se lea de una y la ficha no arranque con un bloque de
             texto suelto. -->
        <div class="hm-hero">
          <img :src="modal.data.image" :alt="modal.data.name">
          <div class="hm-hero__veil"></div>
          <div class="hm-hero__content">
            <h2 class="hm-hero__title" :id="titleId">{{ modal.data.name }}</h2>
            <div class="hm-hero__tags" v-if="modal.data.region || modal.data.type || modal.data.popularity">
              <span class="hm-chip" v-if="modal.data.region">
                <i class="fas fa-map-marked-alt"></i> {{ modal.data.region }}
              </span>
              <span class="hm-chip" v-if="modal.data.type">
                <i class="fas fa-users"></i> {{ modal.data.type }}
              </span>
              <span class="hm-chip hm-chip--accent" v-if="modal.data.popularity">
                <i class="fas fa-fire"></i> {{ modal.data.popularity }}
              </span>
            </div>
          </div>
        </div>

        <div class="hm-body">
          <!-- Mientras el directorio viaja, la ficha ya esta abierta con la foto
               y el nombre del deporte; aqui solo se avisa que falta el detalle. -->
          <p class="hm-text hm-text--lead hm-text--loading" v-if="modal.data.isLoading">
            Cargando la ficha del deporte…
          </p>
          <p class="hm-text hm-text--lead" v-else>{{ modal.data.description }}</p>

          <section class="hm-section" v-if="modal.data.requirements && modal.data.requirements.length">
            <h3 class="hm-section__title">
              <i class="fas fa-clipboard-check"></i> Qué necesitas
            </h3>
            <ul class="hm-gear">
              <li v-for="(item, index) in modal.data.requirements" :key="index">
                <i class="fas fa-check" aria-hidden="true"></i>
                <span>{{ item }}</span>
              </li>
            </ul>
          </section>

          <section class="hm-section" v-if="modal.data.places && modal.data.places.length">
            <h3 class="hm-section__title">
              <i class="fas fa-map-marked-alt"></i> Dónde practicarlo
              <span class="hm-section__count">{{ modal.data.places.length }}</span>
            </h3>
            <div class="hm-places">
              <article class="hm-place" v-for="place in modal.data.places" :key="place.name">
                <h5 class="hm-place__name">{{ place.name }}</h5>
                <p class="hm-place__row" v-if="place.location">
                  <i class="fas fa-location-dot" aria-hidden="true"></i> {{ place.location }}
                </p>
                <p class="hm-place__cost" v-if="place.cost">{{ place.cost }}</p>
                <!-- Los enlaces vienen de la base de datos: solo se pintan si son
                     http(s) reales, y siempre con rel="noopener noreferrer" para
                     que la pestaña destino no pueda tocar la nuestra. -->
                <a v-if="safeUrl(place.website)" class="hm-place__link" :href="safeUrl(place.website)" target="_blank"
                  rel="noopener noreferrer">
                  Visitar sitio web <i class="fas fa-arrow-up-right-from-square" aria-hidden="true"></i>
                </a>
              </article>
            </div>
          </section>

          <div class="hm-actions">
            <router-link to="/directorio" class="hm-btn hm-btn--primary">
              Ver todo el directorio <i class="fas fa-arrow-right"></i>
            </router-link>
          </div>
        </div>
      </template>

    </HomeModal>


    <!-- Back to Top Button -->
    <button class="back-to-top" @click="scrollToTop">
      <i class="fas fa-arrow-up"></i>
    </button>
  </div>

  <!-- Burbuja de Mensajes Flotante: se esconde mientras hay un pop-out
       abierto, para que no quede flotando encima del contenido del modal. -->
  <ChatBubbleComponent v-if="user && !modal.open" :user="user" />

  <Alert v-if="openAlert" :key="alertKey" :type="alertType" :message="alertMessage" @close="openAlert = false" />

</template>




<script>
import axios from 'axios';
import Navbar from '../navbarComponent.vue';
import ChatBubbleComponent from '../ChatBubbleComponent.vue';
import HomeModal from './HomeModal.vue';
import Alert from '../Alert.vue';

// Supabase NO se importa aqui a proposito. El cliente pesa ~220 KB (57 KB
// gzip) y con el import estatico entraba dentro del chunk del Home, asi que
// el navegador tenia que descargarlo entero antes de poder pintar la pagina,
// aunque solo se usa para refrescar tres contadores por realtime. Ahora se
// pide con import() dentro de subscribeRealtime(), despues del primer render.

// Quita acentos y pasa a minusculas, para poder cruzar los nombres de las
// tarjetas del Home ("Béisbol") con los del directorio sin depender de como
// esten tildados en la base de datos.
function normalize(text) {
  return String(text || '')
    .normalize('NFD')
    .replace(/\p{Diacritic}/gu, '')
    .toLowerCase()
    .trim();
}

// Cuanto vale el contenido del Home guardado de la visita anterior. Un minuto:
// lo justo para que ir a Noticias y volver sea instantaneo, sin que los
// contadores ni las noticias se queden notablemente viejos.
const CACHE_HOME_MS = 60 * 1000;

export default {
  components: {
    Navbar,
    ChatBubbleComponent,
    HomeModal,
    Alert
  },
  data() {
    return {
      user: null,

      // Pop-out activo. 'open' controla la animacion del modal, 'type' dice
      // que plantilla se pinta ('news', 'product', 'event', 'post' o 'sport')
      // y 'data' es el elemento que se abrio. Un solo modal a la vez.
      // 'type'/'data' sobreviven un momento al cierre a proposito: si se
      // borraran de golpe, el modal se vaciaria a mitad de la animacion de
      // salida y se veria parpadear.
      modal: { open: false, type: null, data: null },
      modalCleanupTimer: null,
      quantity: 1,
      isAddingToCart: false,
      isSavingNews: false,
      savedNewsIds: [],

      // Aviso flotante reutilizado del resto de la app (Alert.vue).
      openAlert: false,
      alertType: 'success',
      alertMessage: '',
      alertKey: 0,

      recentNews: [],
      recentProducts: [],
      popularPosts: [],
      sports: [],
      stats: {
        users: 0,
        events: 0,
        posts: 0,
      },
      isLoading: false,
      featuredEvents: [], // Ahora se inicializa vacío, se llenará dinámicamente

      // 'sportName' es el nombre tal cual esta en el directorio: sirve para
      // cruzar la tarjeta con el deporte real y poder abrir su ficha completa
      // en el pop-out (descripcion, equipo necesario, donde practicarlo).
      categories: [
        {
          name: "Baseball",
          sportName: "Béisbol",
          image: "/imagenes/DirectorioDeDeportes/baseball.jpg",
          popular: true,
          participation: 90,
        },
        {
          name: "Basketball",
          sportName: "Baloncesto",
          image: "/imagenes/DirectorioDeDeportes/Baloncesto.jpg",
          popular: true,
          participation: 50,
        },
        {
          name: "Domino",
          sportName: "Dominó",
          image: "/imagenes/DirectorioDeDeportes/Domino.jpg",
          popular: true,
          participation: 60,
        },
      ],
    }
  },

  computed: {
    // El año del copyright no se escribe a mano: asi el pie no se queda
    // desfasado el 1 de enero.
    currentYear() {
      return new Date().getFullYear();
    },

    // Tope del selector de cantidad del pop-out abierto: el stock del
    // producto o las boletas que quedan del evento. Si el dato no viene,
    // se usa un tope razonable para no bloquear la compra.
    maxQuantity() {
      const item = this.modal.data;
      if (!item) return 1;

      const available = this.modal.type === 'product' ? item.stock : item.quantity;
      if (available === null || available === undefined || available === '') return 10;

      const parsed = Number(available);
      return Number.isFinite(parsed) ? Math.max(0, Math.floor(parsed)) : 10;
    }
  },

  methods: {


    async fetchInitialData() {
      // El Directorio ya usaba el cache de secciones; el Home no, asi que
      // salir y volver relanzaba las cuatro peticiones cada vez. Ahora se
      // reusan si tienen menos de un minuto.
      const guardado = this.$store.getters.sectionCache('home', CACHE_HOME_MS);
      if (guardado) {
        this.stats = guardado.stats;
        this.recentNews = guardado.recentNews;
        this.recentProducts = guardado.recentProducts;
        this.popularPosts = guardado.popularPosts;
        this.featuredEvents = guardado.featuredEvents ?? [];
        return true;
      }

      this.isLoading = true;
      try {
        const [stats, news, products, posts] = await Promise.all([
          axios.get('/home-stats'),
          axios.get('/recent-news'),
          axios.get('/recent-products'),
          axios.get('/popular-posts')
        ]);

        this.stats = stats.data || {};
        this.recentNews = (news.data || []).filter(n => !!n.image);
        this.recentProducts = products.data.products || [];
        this.popularPosts = this.extractPopularPosts(posts);

        // Los eventos destacados los trae fetchFeaturedEvents(), que corre en
        // paralelo; se guardan desde alli para no encadenar las dos peticiones.
        this.guardarHomeEnCache();

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


    // =====================================================================
    //  POP-OUTS
    //
    //  Todas las secciones abren el mismo componente (HomeModal) y solo
    //  cambian el 'type'. El bloqueo del scroll, el cierre con Escape y el
    //  manejo del foco los resuelve HomeModal, no esta vista.
    // =====================================================================

    openModal(type, data) {
      if (!data) return;

      clearTimeout(this.modalCleanupTimer);
      // Copia superficial: lo que se edite dentro del pop-out (por ejemplo el
      // estado "guardada" de una noticia) no debe mutar el listado de atras
      // hasta que el servidor confirme.
      this.modal = { open: true, type, data: { ...data } };
      this.quantity = 1;
    },

    closeModal() {
      this.modal.open = false;
      this.quantity = 1;

      // Se espera a que termine la animacion de salida (0.22s en
      // HomeModal.vue) antes de soltar el contenido.
      clearTimeout(this.modalCleanupTimer);
      this.modalCleanupTimer = setTimeout(() => {
        if (!this.modal.open) {
          this.modal.type = null;
          this.modal.data = null;
        }
      }, 260);
    },

    openNews(news) {
      this.openModal('news', news);
    },

    openProduct(product) {
      this.openModal('product', product);
    },

    openEvent(event) {
      this.openModal('event', event);
    },

    openPost(post) {
      this.openModal('post', post);
    },

    buscarDeporte(category) {
      return this.sports.find(
        sport => normalize(sport.name) === normalize(category.sportName || category.name)
      );
    },

    // La tarjeta del Home es fija (imagen + barra de popularidad), pero la
    // ficha que se abre sale del directorio real.
    //
    // Antes el directorio se pedia en mounted(): 36 KB y ~2,5 s en CADA carga
    // del Home solo por si el usuario hacia click en una de las tres tarjetas.
    // Ahora se pide la primera vez que hace falta. Para que el click nunca se
    // quede sin respuesta, el pop-out se abre de inmediato con lo que ya tiene
    // la tarjeta (nombre e imagen) y el resto de la ficha entra en cuanto
    // responde el servidor.
    async openSport(category) {
      const cargado = this.buscarDeporte(category);
      if (cargado) {
        this.openModal('sport', cargado);
        return;
      }

      const nombre = category.sportName || category.name;

      this.openModal('sport', {
        name: nombre,
        image: category.image,
        description: '',
        requirements: [],
        places: [],
        isLoading: true
      });

      await this.ensureSports();

      // Mientras se descargaba, el usuario pudo cerrar el pop-out o abrir otro:
      // en ese caso no se toca nada.
      if (!this.modal.open || this.modal.type !== 'sport') return;
      if (normalize(this.modal.data?.name) !== normalize(nombre)) return;

      const ficha = this.buscarDeporte(category);
      this.modal.data = ficha
        ? { ...ficha }
        : {
          ...this.modal.data,
          isLoading: false,
          description: 'Todavía estamos preparando la ficha completa de este deporte. Mientras tanto, puedes explorarlo en el directorio.'
        };
    },

    // Pide el directorio una sola vez aunque la llamen varias tarjetas a la
    // vez (hover sobre una, click en otra): la promesa en vuelo se comparte.
    ensureSports() {
      if (this.sports.length > 0) return Promise.resolve();

      if (!this.sportsPromise) {
        this.sportsPromise = this.fetchSports().finally(() => {
          this.sportsPromise = null;
        });
      }

      return this.sportsPromise;
    },

    incrementQuantity() {
      if (this.quantity < this.maxQuantity) this.quantity++;
    },

    decrementQuantity() {
      if (this.quantity > 1) this.quantity--;
    },

    hasStockInfo(item) {
      const value = this.modal.type === 'product' ? item?.stock : item?.quantity;
      return value !== null && value !== undefined && value !== '';
    },

    canBuyEvent(event) {
      return Number(event?.price) > 0 && this.maxQuantity > 0 && Number.isFinite(Number(event?.id));
    },

    postTieneImagen(post) {
      return !!post?.imagen && !String(post.imagen).includes('no_image');
    },

    // Solo se pintan enlaces http(s). Sin este filtro, un "website" guardado
    // como javascript:... en la base de datos se convertiria en un enlace
    // ejecutable al hacerle click.
    safeUrl(url) {
      if (!url) return null;
      try {
        const parsed = new URL(String(url), window.location.origin);
        return ['http:', 'https:'].includes(parsed.protocol) ? parsed.href : null;
      } catch (error) {
        return null;
      }
    },

    truncate(text, length) {
      const value = String(text || '');
      return value.length > length ? `${value.substring(0, length)}...` : value;
    },

    notify(type, message) {
      this.alertType = type;
      this.alertMessage = message;
      this.alertKey++;
      this.openAlert = true;
    },

    eventDay(event) {
      if (event?.date_iso) return new Date(event.date_iso).getDate();
      return String(event?.date || '').split('/')[0] || '';
    },

    eventMonth(event) {
      if (event?.date_iso) {
        return new Date(event.date_iso).toLocaleDateString('es-ES', { month: 'short' });
      }
      return String(event?.date || '').split('/')[1] || '';
    },

    // =====================================================================
    //  ACCIONES DEL USUARIO DENTRO DE LOS POP-OUTS
    //
    //  Ninguna manda el user_id: el backend saca al usuario del token
    //  (auth:sanctum), asi que no se puede comprar ni guardar a nombre de
    //  otra cuenta cambiando el payload desde el navegador.
    // =====================================================================

    async addProductToCart(product) {
      if (!this.user || this.isAddingToCart) return;

      this.isAddingToCart = true;
      try {
        await axios.post('/cart/items', {
          item_type: 'product',
          item_id: product.id,
          quantity: this.quantity
        });

        window.dispatchEvent(new CustomEvent('cart-updated'));
        this.notify('success', `¡${product.name} agregado al carrito!`);
        this.closeModal();
      } catch (error) {
        console.error('Error al agregar el producto al carrito:', error);
        this.notify('error', this.cartErrorMessage(error));
      } finally {
        this.isAddingToCart = false;
      }
    },

    async addEventToCart(event) {
      if (!this.user || this.isAddingToCart) return;

      this.isAddingToCart = true;
      try {
        await axios.post('/cart/items', {
          item_type: 'event',
          item_id: event.id,
          quantity: this.quantity
        });

        window.dispatchEvent(new CustomEvent('cart-updated'));
        this.notify('success', `¡Boletas para ${event.Title} agregadas al carrito!`);
        this.closeModal();
      } catch (error) {
        console.error('Error al agregar el evento al carrito:', error);
        this.notify('error', this.cartErrorMessage(error));
      } finally {
        this.isAddingToCart = false;
      }
    },

    cartErrorMessage(error) {
      if (error.response?.status === 401) {
        return 'Tu sesión expiró. Vuelve a iniciar sesión para comprar.';
      }
      return 'No se pudo agregar al carrito. Inténtalo de nuevo.';
    },

    isNewsSaved(newsId) {
      return this.savedNewsIds.includes(Number(newsId));
    },

    async fetchSavedNews() {
      if (!this.user) return;

      try {
        const { data } = await axios.get('/saved-news');
        this.savedNewsIds = (data || []).map(Number);
      } catch (error) {
        console.error('Error al cargar las noticias guardadas:', error);
      }
    },

    async toggleSaveNews(news) {
      if (!this.user || this.isSavingNews) return;

      this.isSavingNews = true;
      try {
        const { data } = await axios.post(`/news/${news.id}/toggle-save`);
        const id = Number(news.id);

        if (data.saved) {
          if (!this.savedNewsIds.includes(id)) this.savedNewsIds.push(id);
          this.notify('success', 'Noticia guardada en tu perfil');
        } else {
          this.savedNewsIds = this.savedNewsIds.filter(saved => saved !== id);
          this.notify('success', 'Noticia quitada de tus guardadas');
        }
      } catch (error) {
        console.error('Error al guardar la noticia:', error);
        this.notify('error', 'No se pudo guardar la noticia. Inténtalo de nuevo.');
      } finally {
        this.isSavingNews = false;
      }
    },

    // El directorio se guarda en el mismo cache de seccion que usa
    // DirectorioView, asi que navegar Home -> Directorio no vuelve a pedirlo.
    async fetchSports() {
      const cached = this.$store.getters.sectionCache('directorio');
      if (Array.isArray(cached) && cached.length > 0) {
        this.sports = cached;
        return;
      }

      try {
        const { data } = await axios.get('/sports');
        this.sports = (data.sports || []).map(sport => ({
          id: sport.id,
          name: sport.name,
          region: sport.region,
          type: sport.type,
          popularity: sport.popularity,
          // En la base de datos las rutas vienen sin "/" inicial: sin
          // normalizar, el navegador las resolveria relativas a la ruta actual.
          image: String(sport.image || '').startsWith('http') || String(sport.image || '').startsWith('/')
            ? sport.image
            : `/${sport.image}`,
          shortDescription: sport.short_description ?? sport.shortDescription,
          description: sport.description,
          requirements: sport.requirements || [],
          places: sport.places || [],
        }));
        this.$store.dispatch('cacheSection', { key: 'directorio', data: this.sports });
      } catch (error) {
        console.error('Error al cargar el directorio de deportes:', error);
      }
    },

    scrollToTop() {
      window.scrollTo({
        top: 0,
        behavior: 'smooth'
      });
    },

    // Guarda de una vez todo lo que pinta el Home. Se llama desde las dos
    // funciones que cargan datos: la ultima en terminar deja la foto completa.
    guardarHomeEnCache() {
      this.$store.dispatch('cacheSection', {
        key: 'home',
        data: {
          stats: this.stats,
          recentNews: this.recentNews,
          recentProducts: this.recentProducts,
          popularPosts: this.popularPosts,
          featuredEvents: this.featuredEvents,
        }
      });
    },

    async fetchFeaturedEvents() {
      // Si fetchInitialData ya restauro la seccion desde el cache, los eventos
      // vinieron con ella y no hay nada que pedir.
      if (this.featuredEvents.length > 0) return;

      try {
        const response = await axios.get('/featured-events');
        this.featuredEvents = response.data.events || [];
        this.guardarHomeEnCache();
      } catch (error) {
        this.featuredEvents = [
          {
            id: 1,
            Title: "Torneo Nacional de Baseball",
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
    },

    // --- Realtime (Supabase) ---

    // 'fresh=1' se salta el cache de 60 s del backend. Solo lo usa el realtime:
    // si acaba de entrar un usuario o publicarse un post, el contador tiene que
    // reflejarlo al momento, no cuando venza el cache. La carga inicial del Home
    // si usa el valor cacheado, que es donde estaba el costo.
    async refreshStats() {
      try {
        const { data } = await axios.get('/home-stats', { params: { fresh: 1 } });
        this.stats = data || {};
      } catch (error) {
        console.error('Error refrescando stats:', error);
      }
    },

    // Se descarga el cliente de Supabase bajo demanda (ver nota del import de
    // arriba). Si falla la descarga o la conexion, la pagina sigue funcionando
    // igual: los contadores se quedan con el valor que ya trajo /home-stats,
    // simplemente no se actualizan solos.
    async subscribeRealtime() {
      let supabase;
      try {
        ({ supabase } = await import('../../supabaseClient'));
      } catch (error) {
        console.error('No se pudo cargar el cliente de Supabase:', error);
        return;
      }

      // El usuario pudo haberse ido del Home mientras se descargaba el modulo:
      // sin esta guarda se abriria un canal que ya nadie va a cerrar.
      if (this.isUnmounted) return;

      this.supabase = supabase;
      this.realtimeChannel = supabase
        .channel('home-stats-realtime')
        // calendars y posts son tablas publicas: se puede escuchar directo
        .on('postgres_changes', { event: '*', schema: 'public', table: 'calendars' }, () => {
          this.refreshStats();
        })
        .on('postgres_changes', { event: '*', schema: 'public', table: 'posts' }, () => {
          this.refreshStats();
        })
        // users tiene datos privados (email, telefono): en vez de escuchar la tabla
        // directo, un trigger en la base de datos manda solo un "aviso" por Broadcast
        // cuando cambia la cantidad de usuarios, sin exponer ninguna fila.
        .on('broadcast', { event: 'user_count_changed' }, () => {
          this.refreshStats();
        })
        .subscribe();
    },

        getUserImage(user) {
      if (!user) {
        return '/imagenes/Perfil-Icon.png';
      }

      if (user.image && user.image.startsWith('http')) {
        return user.image;
      }

      if (user.image) {
        return `/storage/users/${user.id}/${user.image}`;
      }

      return '/imagenes/Perfil-Icon.png';
    }

  },

  // Estas tres referencias van fuera de data() a proposito: son objetos
  // internos (el cliente de Supabase y su canal) que no se pintan en la
  // plantilla. Metiendolos en data() Vue los envolveria en un Proxy reactivo
  // y recorreria el cliente entero, que no hace falta y cuesta.
  created() {
    this.supabase = null;
    this.realtimeChannel = null;
    this.isUnmounted = false;
    this.sportsPromise = null;
  },

  mounted() {

    // El usuario se lee ANTES de disparar las peticiones: fetchSavedNews solo
    // tiene sentido si hay sesion, y antes esto se leia al final del mounted.
    try {
      const userData = sessionStorage.getItem('user');
      this.user = userData ? JSON.parse(userData) : null;
    } catch (error) {
      console.error('Datos de usuario invalidos en sessionStorage:', error);
      this.user = null;
    }

    // Se encadenan a proposito: fetchInitialData puede restaurar la seccion
    // entera (eventos incluidos) desde el cache, y en ese caso
    // fetchFeaturedEvents no tiene nada que pedir. Si no hay cache, la peticion
    // sale igual, solo un instante despues.
    this.fetchInitialData().then(() => this.fetchFeaturedEvents());
    this.animateElements();
    this.fetchSavedNews();
    // fetchSports() ya no se llama aqui: el directorio se pide cuando el
    // usuario se acerca a una tarjeta de deporte (ver ensureSports).

    this.subscribeRealtime();

    // Aqui se registraba un listener de scroll:
    //     this.throttledScroll = throttle(this.handleScroll, 100);
    //     window.addEventListener('scroll', this.throttledScroll);
    // 'handleScroll' no existia en este componente, asi que se escuchaba el
    // scroll de toda la pagina para acabar llamando a undefined. Nunca dio
    // error porque el propio throttle lo tapaba con un "if (func)".
  },

  beforeUnmount() {
    this.isUnmounted = true;
    clearTimeout(this.modalCleanupTimer);

    // 'this.supabase' puede seguir en null si se sale del Home antes de que
    // termine de descargarse el cliente; en ese caso no hay canal que cerrar.
    if (this.realtimeChannel && this.supabase) {
      this.supabase.removeChannel(this.realtimeChannel);
      this.realtimeChannel = null;
    }
  }
}
</script>



<style scoped>
@import '../../../scss/Home/home.scss';

@import '../../../scss/Home/home_navbar.scss';

/* Pop-outs: se importa despues de home.scss para poder ajustar las tarjetas
   que ahora son pulsables sin tocar los estilos originales. */
@import '../../../scss/Home/home_modal.scss';


img {
  content-visibility: auto;
}

/* Aqui habia un 'will-change: transform, opacity' permanente sobre las tres
   familias de tarjetas. 'will-change' le pide al navegador que promueva el
   elemento a su propia capa de compositor y la mantenga reservada; dejarlo
   puesto siempre significa decenas de capas vivas todo el rato (memoria de
   GPU) para animaciones que duran 0,6 s una sola vez, o que ni siquiera
   ocurren hasta que se pasa el raton por encima. Los navegadores ya promueven
   solos al empezar una transicion de transform/opacity, que es exactamente lo
   que hacen estas tarjetas. */

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
