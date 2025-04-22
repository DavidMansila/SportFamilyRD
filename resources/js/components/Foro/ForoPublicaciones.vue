<template>
  <div class="foro-page">

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
        <a href= "/Perfil" class="Perfil">
          <img src="/imagenes/Perfil-Icon.png" alt="Perfil" class="perfil-icon"/>
        </a>
        <a :href="'/Login'" class="Logout">
          <img src="/imagenes/Logout-Icon.png" alt="Logout" class="logout-icon"/>
        </a>
      </div>
    </nav>



    <!-- Contenido principal de la publicación -->
    <div class="publicacion-container">
      <!-- Botón para volver al foro -->
      <router-link to="/Foro" class="btn-volver">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <line x1="19" y1="12" x2="5" y2="12"></line>
          <polyline points="12 19 5 12 12 5"></polyline>
        </svg>
        Volver al foro
      </router-link>

      <!-- Cabecera del post -->
      <div class="publicacion-card">
        <div class="post-categoria" :style="{backgroundColor: `hsl(${hue}, 70%, 50%)`}">
          {{ post.categoria || 'General' }}
        </div>
        
        <div class="publicacion-header">
          <h1 class="post-titulo">{{ post.titulo }}</h1>
          
          <div class="post-meta">
            <span class="post-author">
              Usuario{{ post.id }}
            </span>
            <span class="post-date">{{ post.fecha }}</span>
          </div>
        </div>

        <!-- Contenido del post -->
        <div class="publicacion-contenido">
          <div v-if="post.imagen" class="publicacion-imagen">
            <img :src="post.imagen" :alt="post.titulo">
          </div>
          <p class="post-contenido">{{ post.contenido }}</p>
        </div>

        <!-- Interacciones -->
        <div class="publicacion-interacciones">
          <button class="btn-interaccion" @click="toggleLike">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M14 9V5a3 3 0 0 0-3-3l-4 9v11h11.28a2 2 0 0 0 2-1.7l1.38-9a2 2 0 0 0-2-2.3zM7 22H4a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2h3"></path>
            </svg>
            {{ post.likes }} Me gusta
          </button>
          <button class="btn-interaccion">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
            </svg>
            {{ comentarios.length }} Comentarios
          </button>
        </div>
      </div>

      <!-- Sección de comentarios -->
      <section class="comentarios-section">
        <h2 class="comentarios-titulo">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
          </svg>
          Comentarios
        </h2>

        <!-- Formulario para nuevo comentario -->
        <div class="nuevo-comentario">
          <div class="comentario-form">
            <textarea
              v-model="nuevoComentario.texto"
              placeholder="Escribe tu comentario..."
              @focus="resetError"
              :class="{ 'error': errorComentario }"
            ></textarea>
            <div v-if="errorComentario" class="error-message">El comentario no puede estar vacío</div>
            <div class="comentario-acciones">
              <button @click="agregarComentario" class="btn-publicar">Publicar comentario</button>
            </div>
          </div>
        </div>

        <!-- Lista de comentarios -->
        <div class="comentarios-lista" v-if="comentarios.length > 0">
          <div 
            v-for="comentario in comentarios" 
            :key="comentario.id" 
            class="comentario"
          >
            <div class="comentario-contenido">
              <div class="comentario-header">
                <span class="comentario-autor">Usuario{{ comentario.id }}</span>
                <span class="comentario-fecha">{{ comentario.fecha }}</span>
              </div>
              <div class="comentario-texto">{{ comentario.texto }}</div>
              <div class="comentario-acciones">
                <button @click="toggleMeGusta(comentario)" class="btn-me-gusta">
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14 9V5a3 3 0 0 0-3-3l-4 9v11h11.28a2 2 0 0 0 2-1.7l1.38-9a2 2 0 0 0-2-2.3zM7 22H4a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2h3"></path>
                  </svg>
                  {{ comentario.likes || 0 }}
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Mensaje cuando no hay comentarios -->
        <div v-else class="sin-comentarios">
          <img src="/imagenes/no-news.png" alt="Sin comentarios" class="sin-comentarios-img">
          <h3>No hay comentarios aún</h3>
          <p>Sé el primero en comentar esta publicación</p>
        </div>
      </section>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'PublicacionForo',
  data() {
    return {
      post: {
        id: 1,
        titulo: 'Ejemplo de publicación',
        contenido: 'Este es el contenido de ejemplo de la publicación. Aquí se compartirían experiencias, preguntas o discusiones sobre temas deportivos.',
        categoria: 'Deporte',
        fecha: '15 de junio, 2023',
        likes: 24,
        comentarios: 3,
        imagen: '/imagenes/post-ejemplo.jpg'
      },
      comentarios: [
        {
          id: 1,
          texto: '¡Excelente publicación! Totalmente de acuerdo con lo que comentas.',
          fecha: '15 de junio, 2023 - 10:30',
          likes: 5
        },
        {
          id: 2,
          texto: '¿Podrías recomendar algún lugar específico para practicar esto?',
          fecha: '16 de junio, 2023 - 08:45',
          likes: 2
        },
        {
          id: 3,
          texto: 'Gracias por compartir tu experiencia, me ha sido muy útil.',
          fecha: '17 de junio, 2023 - 15:20',
          likes: 0
        }
      ],
      nuevoComentario: {
        texto: '',
      },
      errorComentario: false,
      hue: Math.floor(Math.random() * 360)
    };
  },
  methods: {
    async cargarPublicacion() {
      try {
        const postId = this.$route.params.id;
        const response = await axios.get(`/posts/${postId}`);
        this.post = response.data;
        this.hue = (postId * 60) % 360;
        
        const commentsResponse = await axios.get(`/posts/${postId}/comments`);
        this.comentarios = commentsResponse.data;
      } catch (error) {
        console.error('Error al cargar la publicación:', error);
      }
    },
    
    resetError() {
      this.errorComentario = false;
    },
    
    async agregarComentario() {
      if (!this.nuevoComentario.texto.trim()) {
        this.errorComentario = true;
        return;
      }
      
      try {
        const response = await axios.post('/comments', {
          post_id: this.post.id,
          texto: this.nuevoComentario.texto
        });
        
        this.comentarios.push({
          id: response.data.id,
          texto: this.nuevoComentario.texto,
          fecha: 'Ahora mismo',
          likes: 0
        });
        
        this.nuevoComentario.texto = '';
        this.post.comentarios += 1;
      } catch (error) {
        console.error('Error al agregar comentario:', error);
      }
    },
    
    async toggleLike() {
      try {
        const response = await axios.post(`/posts/${this.post.id}/like`);
        this.post.likes = response.data.likes;
      } catch (error) {
        console.error('Error al dar like:', error);
      }
    },
    
    async toggleMeGusta(comentario) {
      try {
        const response = await axios.post(`/comments/${comentario.id}/like`);
        comentario.likes = response.data.likes;
      } catch (error) {
        console.error('Error al dar like al comentario:', error);
      }
    }
  },
  mounted() {
    this.cargarPublicacion();
  }
};
</script>


<style scoped>
@import '../../../scss/Foro/foroPublicaciones.scss';
</style>
  

