<template>
  <div class="foro-page" :class="{ 'no-scroll': postSeleccionado }">

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


    

    <!-- Hero Section -->
    <div class="hero-section">
      <div class="hero-content">
        <h1 class="titulo-foro">Comunidad Deportiva</h1>
        <p class="hero-subtitle">Conecta, comparte y aprende con otros apasionados del deporte</p>
        <button @click="abrirModal" class="btn-crear-post hero-btn">Crear nuevo post</button>
      </div>
      <div class="hero-overlay"></div>
    </div>




    <!-- Filtros y búsqueda -->
    <div class="filtros-container">
      <div class="search-bar">
        <input 
          type="text" 
          v-model="terminoBusqueda"
          @input="filtrarPosts"
          placeholder="Buscar en el foro..." 
          class="search-input"
        >
        <button class="search-btn" @click="filtrarPosts">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="11" cy="11" r="8"></circle>
            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
          </svg>
        </button>
      </div>
      <div class="filtros-categorias">
        <button 
          @click="cambiarCategoria('')" 
          :class="{ active: categoriaSeleccionada === '' }" 
          class="filtro-btn"
        >
          Todos
        </button>
        <button 
          @click="cambiarCategoria('Deporte')" 
          :class="{ active: categoriaSeleccionada === 'Deporte' }" 
          class="filtro-btn"
        >
          Deporte
        </button>
        <button 
          @click="cambiarCategoria('Gym')" 
          :class="{ active: categoriaSeleccionada === 'Gym' }" 
          class="filtro-btn"
        >
          Gym
        </button>
        <button 
          @click="cambiarCategoria('Experiencia')" 
          :class="{ active: categoriaSeleccionada === 'Experiencia' }" 
          class="filtro-btn"
        >
          Experiencia
        </button>
        <button 
          @click="cambiarCategoria('Lugares')" 
          :class="{ active: categoriaSeleccionada === 'Lugares' }" 
          class="filtro-btn"
        >
          Lugares
        </button>
      </div>
    </div>





    <!-- Sección de Posts con diseño de tarjetas -->
    <div class="posts-grid">
      
      <div v-if="postsFiltrados.length === 0">

        <div class="no-posts">
          <h2 class="no-posts">No hay publicaciones disponibles</h2>
          <p class="no-posts-subtitle">¡Sé el primero en iniciar una publicación!</p>
          <button @click="abrirModal" class="btn-crear-post no-posts-btn">Crear nuevo post</button>
          <img src="/imagenes/no-news.png" alt="No hay publicaciones" class="no-posts-image">
        </div>

      </div>  

      <div 
        v-else
        v-for="(post, index) in postsFiltrados" 
        :key="index" 
        class="post-card"
        :style="`--hue: ${index * 60 % 360}`"
      >
        <div class="post-categoria" :style="{backgroundColor: `hsl(${index * 60 % 360}, 70%, 50%)`}">
          {{ post.categoria || 'General' }}
        </div>
        <div class="post-header">
          <h3 class="post-titulo">{{ post.titulo }}</h3>
        
          <div class="post-meta">
            <span class="post-author">
              Usuario{{ post.id }}
            </span>
            <span class="post-date">{{ formatDate(post.created_at) }}</span>
          </div>
        </div>

        <div class="post-imagen">
          <img 
            :src="post.imagen" 
            alt="Imagen del post" 
            class="post-image" 
            @load="onImageLoad"
            :class="{ loaded: imageLoaded }"
          />  
        </div>      
        
        <p class="post-contenido">{{ post.contenido.substring(0, 150) }}...</p>
        
        <div class="post-footer">
          <div class="post-stats">
            <span class="post-likes">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M14 9V5a3 3 0 0 0-3-3l-4 9v11h11.28a2 2 0 0 0 2-1.7l1.38-9a2 2 0 0 0-2-2.3zM7 22H4a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2h3"></path>
              </svg>
              {{ post.likes_quantity }}
            </span>
            <span class="post-comments">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
              </svg>
              {{ post.comments.length }}
            </span>
          </div>

          <button @click="abrirPopout(post)" class="btn-leer">Ver discusión</button>
        </div>
      </div>
    </div>







    <!-- Popout para ver publicación completa -->
    <transition name="fade">
      <div v-if="postSeleccionado" class="post-popout-overlay" @click.self="cerrarPopout">
        <div class="post-popout-container">
          <!-- Contenedor principal -->
          <div class="post-popout-content">
            <!-- Sección de imagen -->
            <div class="post-popout-media">
              <div class="image-container">
                <img :src="postSeleccionado.imagen" :alt="postSeleccionado.titulo" class="post-popout-image">
              </div>
              <div class="post-interactions">
                <button @click="toggleLike" class="interaction-btn like-btn" :class="{ liked: postSeleccionado.isLiked }">
                  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                    <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                  </svg>
                  <span>{{ postSeleccionado.likes_quantity }}</span>
                </button>
                <button @click="focusComentario" class="interaction-btn comment-btn">
                  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                    <path d="M21.99 4c0-1.1-.89-2-1.99-2H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h14l4 4-.01-18z"/>
                  </svg>
                  <span>{{ postSeleccionado.comments.length }}</span>
                </button>
              </div>
            </div>
            


            <!-- Sección de contenido -->
            <div class="post-popout-details">
              <!-- Cabecera -->
              <div class="post-popout-header">
                <div class="post-author-info">
                  <div class="author-avatar-wrapper">
                    <span class="author-online-dot"></span>
                  </div>
                  <div>
                    <h3 class="author-name">Usuario{{ postSeleccionado.id }}</h3>
                    <span class="post-category-badge" :style="{backgroundColor: categoryColor(postSeleccionado.categoria)}">
                      {{ postSeleccionado.categoria || 'General' }}
                    </span>
                  </div>
                </div>
                <button @click="cerrarPopout" class="close-popout-btn" :class="{ 'hidden': inputFocused }">
                  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                    <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/>
                  </svg>
                </button>
              </div>


              
              <!-- Contenido del post -->
              <div class="post-popout-body">
                <h2 class="post-popout-title">{{ postSeleccionado.titulo }}</h2>
                <p class="post-popout-text">{{ postSeleccionado.contenido }}</p>
                <div class="post-meta-info">
                  <span class="post-date">{{ formatDate(postSeleccionado.created_at) }}</span>
                  <span class="post-visibility">Público</span>
                </div>
              </div>
              


              <!-- Sección de comentarios -->
              <div class="post-comments-section">
                <h4 class="comments-title">
                  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                    <path d="M21.99 4c0-1.1-.89-2-1.99-2H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h14l4 4-.01-18z"/>
                  </svg>
                  Conversación ({{ postSeleccionado.comments.length }})
                </h4>
                
                <div class="comments-container" ref="commentsContainer">




                  <!-- Comentarios principales -->
                  <div v-for="(comentario, index) in postSeleccionado.comments" :key="comentario.id" class="comment-item">
                    <div class="comment-avatar-wrapper">
                      <div class="comment-avatar-placeholder">
                        <span>U{{ comentario.userId % 100 }}</span>
                      </div>
                    </div>
                    <div class="comment-content">
                      <div class="comment-header">
                        <span class="comment-author">Usuario{{ comentario.userId }}</span>
                        <span class="comment-time">{{ formatRelativeTime(comentario.created_at) }}</span>
                        <button 
                          v-if="comentario.respuestas && comentario.respuestas.length > 0"
                          @click="toggleCommentExpansion(comentario.id)"
                          class="toggle-replies-btn"
                        >
                          {{ comentariosExpandidos.includes(comentario.id) ? 'Ocultar respuestas' : `Ver ${comentario.respuestas.length} respuesta(s)` }}
                        </button>
                      </div>
                      <p class="comment-text">{{ comentario.texto }}</p>
                      <div class="comment-actions">
                        <button @click="likeComentario(comentario.id)" class="comment-action like-comment" :class="{ liked: comentario.isLiked }">
                          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                            <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                          </svg>
                          <span>{{ comentario.likes }}</span>
                        </button>
                        <button @click="toggleReply(comentario.id)" class="comment-action reply-comment">
                          {{ comentarioRespondiendo === comentario.id ? 'Cancelar' : 'Responder' }}
                        </button>
                      </div>



                      
                      <!-- Respuestas -->
                      <!-- <div v-if="comentariosExpandidos.includes(comentario.id) && comentario.respuestas && comentario.respuestas.length > 0" 
                          class="comment-replies">
                        <div v-for="(respuesta, i) in comentario.respuestas" :key="respuesta.id" class="comment-item reply-item">
                          <div class="comment-avatar-wrapper">
                            <div class="comment-avatar-placeholder">
                              <span>U{{ respuesta.userId % 100 }}</span>
                            </div>
                          </div>
                          <div class="comment-content">
                            <div class="comment-header">
                              <span class="comment-author">Usuario{{ respuesta.userId }}</span>
                              <span class="comment-time">{{ formatRelativeTime(respuesta.fecha) }}</span>
                            </div>
                            <p class="comment-text">{{ respuesta.texto }}</p>
                            <div class="comment-actions">
                              <button @click="likeComentario(respuesta.id)" class="comment-action like-comment" :class="{ liked: respuesta.isLiked }">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                                  <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                                </svg>
                                <span>{{ respuesta.likes }}</span>
                              </button>
                            </div>
                          </div>
                        </div>
                      </div> -->

                      
                    </div>
                  </div>
                </div>




                
                <!-- Formulario de comentario principal o respuesta -->
                <div class="add-comment-form">
                  <div v-if="comentarioRespondiendo" class="replying-to">
                    Respondiendo a <strong>@Usuario{{ comentarioRespondiendoo.userId }}</strong>
                    <button @click="cancelarRespuesta" class="cancel-reply-btn">
                      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                      </svg>
                    </button>
                  </div>
                  <form @submit.prevent=" addComment()" class="comment-form">
                    <input
                      v-model="nuevoComentario"
                      ref="comentarioInput"
                      type="text"
                      :placeholder="comentarioRespondiendo ? 'Escribe tu respuesta...' : 'Escribe un comentario...'"
                      class="comment-input"
                      @focus="inputFocused = true"
                      @blur="inputFocused = false"
                      @keydown.esc="cancelarRespuesta"
                    >
                    <button 
                      type="submit" 
                      :disabled="!nuevoComentario.trim()" 
                      class="submit-comment-btn"
                      :class="{ disabled: !nuevoComentario.trim() }"
                    >
                      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                        <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/>
                      </svg>
                    </button>
                  </form>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </transition>






    <!-- Botón flotante para crear post -->
    <button @click="abrirModal" class="floating-btn">
      <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <line x1="12" y1="5" x2="12" y2="19"></line>
        <line x1="5" y1="12" x2="19" y2="12"></line>
      </svg>
    </button>

    <div v-if="mostrarModal" class="modal-overlay" @click.self="cerrarModal">
  <div class="modal-container">
    <div class="modal-header">
      <h2>Crear nuevo post</h2>
      <button @click="cerrarModal" class="modal-close-btn">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <line x1="18" y1="6" x2="6" y2="18"></line>
          <line x1="6" y1="6" x2="18" y2="18"></line>
        </svg>
      </button>
    </div>
    
    <div class="modal-content-wrapper">
      <form @submit.prevent="createPost()" class="modal-form">
        <div class="form-group">
          <label for="titulo">Título</label>
          <input
            v-model="nuevoPost.titulo"
            id="titulo"
            type="text"
            placeholder="¿De qué quieres hablar?"
            required
          />
        </div>

        <div class="form-group">
          <label for="contenido">Contenido</label>
          <textarea
            v-model="nuevoPost.contenido"
            id="contenido"
            placeholder="Comparte tus ideas, preguntas o experiencias..."
            rows="5"
            required
          ></textarea>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label for="categoria">Categoría</label>
            <select v-model="nuevoPost.categoria" id="categoria" required>
              <option value="">Selecciona una categoría</option>
              <option value="Deporte">Deporte</option>
              <option value="Gym">Gym</option>
              <option value="Experiencia">Experiencia</option>
              <option value="Lugares">Lugares</option>
            </select>
          </div>
        </div>

        <div class="form-group">
          <label class="file-upload-label">
            <input
              type="file"
              id="imagen"
              @change="handleFileSelect"
              accept="image/*"
              class="file-upload-input"
            />
            <span class="file-upload-btn">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                <polyline points="17 8 12 3 7 8"></polyline>
                <line x1="12" y1="3" x2="12" y2="15"></line>
              </svg>
              Subir imagen
            </span>
            <span v-if="nuevoPost.imagenFile" class="file-upload-name">
              {{ nuevoPost.imagenFile.name }}
            </span>
          </label>
        </div>

        <div class="form-actions">
          <button type="button" @click="cerrarModal" class="btn btn-secondary">Cancelar</button>
          <button type="submit" class="btn btn-primary">Publicar</button>
        </div>
      </form>
      

        <!-- Vista previa externa -->
     <div v-if="mostrarModal && imagenMiniatura" class="external-preview">
        <img :src="imagenMiniatura" alt="Previsualización" class="preview-image">
    </div>

    </div>
  </div>
</div>



  </div>
</template>





<script>
import axios from 'axios';

export default {
  name: 'ForoComponent',
  data() {
    return {

      posts: [],

      postsFiltrados: [],

      categoriaSeleccionada: '',

      terminoBusqueda: '',

      mostrarModal: false,

      nuevoPost: {
        titulo: '',
        contenido: '',
        categoria: '',
        imagenFile: null,
      },

      imagenMiniatura: null,
      imageLoaded: false,

      postSeleccionado: null,
      scrollPosition: 0,
      inputFocused: false,
      

      nuevoComentario: '',

    }
  },

  methods: {


    abrirModal() {
      this.mostrarModal = true;
      document.body.style.overflow = 'hidden';
    },


    cerrarModal() {
      this.mostrarModal = false;
      this.limpiarFormulario();
      document.body.style.overflow = 'auto';
    },


  
    cambiarCategoria(categoria) {
      this.categoriaSeleccionada = categoria;
      this.filtrarPosts();
    },




    filtrarPosts() {
      // Primero filtramos por categoria
      let postsFiltrados = this.categoriaSeleccionada 
        ? this.posts.filter(post => post.categoria === this.categoriaSeleccionada)
        : [...this.posts];

      // Luego filtramos por termino de busqueda si existe
      if (this.terminoBusqueda) {
        const termino = this.terminoBusqueda.toLowerCase();
        postsFiltrados = postsFiltrados.filter(post => 
          post.titulo.toLowerCase().includes(termino) || 
          post.contenido.toLowerCase().includes(termino) ||
          (post.categoria && post.categoria.toLowerCase().includes(termino))
        );
      }
      this.postsFiltrados = postsFiltrados;
    },




    guardarPost() {
      const nuevoId = Math.max(...this.posts.map(p => p.id)) + 1;
      const nuevoPost = {
        id: nuevoId,
        titulo: this.nuevoPost.titulo,
        contenido: this.nuevoPost.contenido,
        categoria: this.nuevoPost.categoria,
        imagen: this.nuevoPost.imagenFile,
        fecha: new Date().toLocaleDateString(),
        likes: 0,
        comentarios: 0,
      };
      this.posts.unshift(nuevoPost);
      this.cerrarModal();
    },




    limpiarFormulario() {
      this.nuevoPost = {
        titulo: '',
        contenido: '',
        categoria: '',
        imagenFile: null
      };
      this.imagenMiniatura = null;
    },
    



    handleFileSelect(event) {
      const file = event.target.files[0];
      if (!file) return;
      
      if (!file.type.match('image.*')) {
        alert('Por favor, selecciona solo imágenes (JPEG, PNG, etc.)');
        return;
      }
      
      if (file.size > 2 * 1024 * 1024) {
        alert('La imagen es demasiado grande. Máximo 2MB permitido.');
        return;
      }
      
      this.nuevoPost.imagenFile = file;
      
      const reader = new FileReader();
      reader.onload = (e) => {
        this.imagenMiniatura = e.target.result;
      };
      reader.readAsDataURL(file);
    },


    loadImg(file) {
      let reader = new FileReader();
      reader.onload = (e) => {
        this.imagenMiniatura = e.target.result;
      };
      reader.readAsDataURL(file);
    },


    onImageLoad() {
      this.imageLoaded = true;
    },


    getPost() {
      return axios.get('/post')
        .then(response => {
          this.posts = response.data.posts;
          this.postsFiltrados = [...this.posts];
        })
        .catch(error => {
          console.error('Error al obtener los posts:', error);
        });
    },
    

    async createPost() {
      
      const formData = new FormData();
      formData.append('titulo', this.nuevoPost.titulo);
      formData.append('contenido', this.nuevoPost.contenido);
      formData.append('categoria', this.nuevoPost.categoria);
      
      if (this.nuevoPost.imagenFile) {
        formData.append('imagen', this.nuevoPost.imagenFile);
      }

      try {
        let header = { headers: { 'content-type': 'multipart/form-data' } };
        await axios.post('/post', formData, header);

        this.limpiarFormulario();
        this.cerrarModal();
        
      } catch (error) {
        console.error('Error al crear el post:', error);
        alert('Error al crear el post. Por favor, intenta nuevamente.');
      }
    },





    abrirPopout(post) {
      // Guardar posición del scroll antes de abrir el popout
      this.scrollPosition = window.pageYOffset || document.documentElement.scrollTop;
      
      // Bloquear scroll del body
      document.body.style.overflow = 'hidden';
      document.body.style.position = 'fixed';
      document.body.style.top = `-${this.scrollPosition}px`;
      document.body.style.width = '100%';
      
      this.postSeleccionado = { ...post, isLiked: false};
      
    },
    


    
    cerrarPopout() {

      this.postSeleccionado = null;
      this.nuevoComentario = '';
      
      // Restaurar scroll del body
      document.body.style.overflow = 'auto';
      document.body.style.position = '';
      document.body.style.top = '';
      document.body.style.width = '';
      
      // Restaurar posición del scroll
      window.scrollToBottom(this.scrollPosition);
    },
    


    toggleLike() {
      if (this.postSeleccionado.isLiked) {
        this.postSeleccionado.likes--;
      } else {
        this.postSeleccionado.likes++;
      }
      this.postSeleccionado.isLiked = !this.postSeleccionado.isLiked;
    },
    


    focusComentario() {
      this.$nextTick(() => {
        this.$refs.comentarioInput.focus();
        this.scrollToBottom();
      });
    },
    


    scrollToBottom() {
      this.$nextTick(() => {
        const container = this.$refs.commentsContainer;
        if (container) {
          container.scrollTop = container.scrollHeight;
        }
      });
    },
    

    toggleCommentExpansion(commentId) {
      const index = this.comentariosExpandidos.indexOf(commentId);
      if (index === -1) {
        this.comentariosExpandidos.push(commentId);
      } else {
        this.comentariosExpandidos.splice(index, 1);
      }
      
      // Forzar re-render para asegurar que el scroll se actualice
      this.$nextTick(() => {
        this.scrollToBottom();
      });
    },
    

    toggleReply(commentId) {
      if (this.comentarioRespondiendo === commentId) {
        this.cancelarRespuesta();
      } else {
        this.comentarioRespondiendo = commentId;
        this.$nextTick(() => {
          this.$refs.comentarioInput.focus();
          this.scrollToBottom();
        });
      }
    },
    

    cancelarRespuesta() {
      this.comentarioRespondiendo = null;
      this.nuevoComentario = '';
    },
    

    


    addComment() {
      axios.post('/post/create-comment', {
        post_id: this.postSeleccionado.id,
        texto: this.nuevoComentario
      })
      .then(response => {
        this.nuevoComentario = '';
        this.scrollToBottom();
       
        this.getPost()
        .then(() => {
          const postEncontrado = this.posts.find(post => Number(post.id) === Number(this.postSeleccionado.id));
          if (postEncontrado) {
            this.postSeleccionado = { ...postEncontrado };
          }
        })
        .catch(error => {
          console.error('Error al actualizar los posts:', error);
        });
          
      })
      .catch(error => {
        console.error('Error al agregar comentario:', error);
      });
    },



    

    likeComentario(commentId) {
      const comment = this.findCommentById(commentId);
      if (comment) {
        if (comment.isLiked) {
          comment.likes--;
        } else {
          comment.likes++;
        }
        comment.isLiked = !comment.isLiked;
      }
    },
    



    findCommentById(id) {
      // Buscar en comentarios principales
      for (const comment of this.comentarios) {
        if (comment.id === id) return comment;
        
        // Buscar en respuestas
        if (comment.respuestas) {
          for (const reply of comment.respuestas) {
            if (reply.id === id) return reply;
          }
        }
      }
      return null;
    },
    




    categoryColor(categoria) {
      const colors = {
        'General': '#6c757d',
        'Deporte': '#17a2b8',
        'Gym': '#28a745',
        'Experiencia': '#ffc107',
        'Lugares': '#dc3545'
      };
      return colors[categoria] || colors['General'];
    },

    


    formatDate(date) {
      if (!date) return '';
      const d = new Date(date);
      return d.toLocaleDateString('es-ES', { 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      });
    },
    

    

    formatRelativeTime(date) {
      const now = new Date();
      const diff = now - new Date(date);
      const minutes = Math.floor(diff / 60000);
      
      if (minutes < 1) return 'Ahora mismo';
      if (minutes < 60) return `Hace ${minutes} min`;
      if (minutes < 1440) return `Hace ${Math.floor(minutes / 60)} h`;
      return `Hace ${Math.floor(minutes / 1440)} d`;
    },



  },



  mounted() {
    this.getPost();
  }
}
</script>





<style scoped>

@import '../../../scss/Foro/foro.scss';

@import '../../../scss/Foro/foro_filtro_y_busqueda.scss';

@import '../../../scss/Foro/foro_post.scss';

@import '../../../scss/Foro/foro_pop_out_post.scss';

@import '../../../scss/Foro/foro_modal.scss';

@import '../../../scss/Foro/foro_responsive.scss';

@import '../../../scss/Foro/foro_navbar.scss';

</style>