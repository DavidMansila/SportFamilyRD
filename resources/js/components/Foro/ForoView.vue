<template>
  <div class="foro-page" :class="{ 'no-scroll': postSeleccionado }">

    <!-- Navbar -->
    <nav class="navbar">
      <div class="logo-container">
        <a href="/" class="logo-container">
          <img src="/imagenes/logo2.png" alt="SportFamilyRD Logo" class="logo" />
        </a>
      </div>

      <div class="nav-links">

        <!-- Secciones para lo usuarios y no usuarios -->
        <a href="/Noticias" class="nav-link">Noticias</a>
        <a href="/Calendario" class="nav-link">Calendario</a>
        <a href="/Tienda" class="nav-link">Tienda</a>
        <a href="/Entrenadores" class="nav-link">Entrenadores</a>
        <a href="/Foro" class="nav-link">Foro</a>

        <!-- Secciones para entrenadores -->
        <a v-if="userType == 'entrenador'" href="/SolicitudesUsuarios" class="nav-link">Solicitudes</a>

        <!-- Secciones para entrenadores -->
        <a v-if="userType == 'admin'" href="/SolicitudesEntrenadores" class="nav-link">Solicitudes</a>


      </div>

      <div class="Imagenes">

        <a href="#" class="Carrito">
          <img src="/imagenes/Carrito-Icon.png" alt="Carrito" class="carrito-icon" />
        </a>

        <a href="/Ajustes" class="Ajustes">
          <img src="/imagenes/Ajustes-Icon.png" alt="Ajustes" class="ajustes-icon" />
        </a>

        <a href="/Perfil" class="Perfil">
          <img src="/imagenes/Perfil-Icon.png" alt="Perfil" class="perfil-icon" />
        </a>

        <a :href="login ? '/Login' : '/Logout'" class="Logout">
          <img src="/imagenes/Logout-Icon.png" alt="Logout" class="logout-icon" />
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
        <input type="text" v-model="terminoBusqueda" @input="filtrarPosts" placeholder="Buscar en el foro..."
          class="search-input">
        <button class="search-btn" @click="filtrarPosts">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="11" cy="11" r="8"></circle>
            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
          </svg>
        </button>
      </div>

      <div class="filtros-categorias">
        <button @click="cambiarCategoria('')" :class="{ active: categoriaSeleccionada === '' }" class="filtro-btn">
          Todos
        </button>
        <button @click="cambiarCategoria('Deporte')" :class="{ active: categoriaSeleccionada === 'Deporte' }"
          class="filtro-btn">
          Deporte
        </button>
        <button @click="cambiarCategoria('Gym')" :class="{ active: categoriaSeleccionada === 'Gym' }"
          class="filtro-btn">
          Gym
        </button>
        <button @click="cambiarCategoria('Experiencia')" :class="{ active: categoriaSeleccionada === 'Experiencia' }"
          class="filtro-btn">
          Experiencia
        </button>
        <button @click="cambiarCategoria('Lugares')" :class="{ active: categoriaSeleccionada === 'Lugares' }"
          class="filtro-btn">
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

      <div v-else v-for="(post, index) in postsPaginados" :key="index" class="post-card"
        :style="`--hue: ${index * 60 % 360}`">
        <div class="post-categoria" :style="{ backgroundColor: categoryColor(post.categoria) }">
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
          <img :src="post.imagen" alt="Imagen del post" class="post-image" @load="onImageLoad"
            :class="{ loaded: imageLoaded }" />
        </div>

        <p class="post-contenido">{{ post.contenido.substring(0, 150) }}...</p>

        <div class="post-footer">
          <div class="post-stats">
            <span class="post-likes">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                :stroke="post.isLiked ? 'currentColor' : 'currentColor'" :fill="post.isLiked ? 'currentColor' : 'none'"
                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path
                  d="M14 9V5a3 3 0 0 0-3-3l-4 9v11h11.28a2 2 0 0 0 2-1.7l1.38-9a2 2 0 0 0-2-2.3zM7 22H4a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2h3">
                </path>
              </svg>
              {{ post.likes_quantity }}
            </span>
            <span class="post-comments" @click.stop="abrirPopoutYFocalizarComentario(post)">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
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

              <!-- Sección de body -->
              <div class="post-popout-body">
                <h2 class="post-popout-title">{{ postSeleccionado.titulo }}</h2>
              </div>

              <div class="image-container">
                <img :src="postSeleccionado.imagen" :alt="postSeleccionado.titulo" class="post-popout-image">
              </div>


              <div class="post-popout-body">
                <p class="post-popout-text">{{ postSeleccionado.contenido }}</p>
                <div class="post-meta-info">
                  <span class="post-date">{{ formatDate(postSeleccionado.created_at) }}</span>
                  <span class="post-visibility">Público</span>
                </div>
              </div>


              <div class="post-interactions">
                <button @click="likePost" class="interaction-btn like-btn" :class="{ liked: postSeleccionado.isLiked }">
                  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                    <path
                      d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z" />
                  </svg>
                  <span>{{ postSeleccionado.likes_quantity }}</span>
                </button>
                <button @click="focusComentario" class="interaction-btn comment-btn">
                  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                    <path d="M21.99 4c0-1.1-.89-2-1.99-2H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h14l4 4-.01-18z" />
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
                    <span class="post-category-badge"
                      :style="{ backgroundColor: categoryColor(postSeleccionado.categoria) }">
                      {{ postSeleccionado.categoria || 'General' }}
                    </span>
                  </div>
                </div>

                <!-- EDITAR Y ELIMINAR POST | HAY QUE PONER QUE SI EL ID DEL USUARIO COINCIDE CON EL ID DEL USUARIO DEL POST PUES PUEDE EDITAR Y ELIMINAR DICHO POST-->
                <div class="post-actions" v-if="isPostAuthor(postSeleccionado)">
                  <button @click="abrirEditarModal(postSeleccionado)" class="edit-post-btn" title="Editar post">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                      stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                      <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                      <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                    </svg>
                  </button>
                  <button @click="confirmarEliminarPost" class="delete-post-btn" title="Eliminar post">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                      stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                      <polyline points="3 6 5 6 21 6"></polyline>
                      <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                    </svg>
                  </button>
                </div>


                <button @click="cerrarPopout" class="close-popout-btn" :class="{ 'hidden': inputFocused }">
                  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                    <path
                      d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z" />
                  </svg>
                </button>
              </div>



              <!-- Contenido del post -->
              <!-- Sección de comentarios -->
              <div class="post-comments-section">
                <h4 class="comments-title">
                  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                    <path d="M21.99 4c0-1.1-.89-2-1.99-2H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h14l4 4-.01-18z" />
                  </svg>
                  Conversación ({{ postSeleccionado.comments.length }})
                </h4>

                <div class="comments-container" ref="commentsContainer">
                  <!-- Comentarios principales -->
                  <div v-for="(comentario) in postSeleccionado.comments" :key="comentario.id" class="comment-item">
                    <div class="comment-avatar-wrapper">
                      <div class="comment-avatar-placeholder">
                        <span>U{{ comentario.userId }}</span>
                      </div>
                    </div>
                    <div class="comment-content">
                      <div class="comment-header">

                        <span class="comment-author"> {{ comentario.user?.id || comentario.userId || '' }}</span>
                        <span class="comment-time">{{ formatRelativeTime(comentario.created_at) }}</span>
                        <button v-if="comentario.respuestas && comentario.respuestas.length > 0"
                          @click="toggleCommentExpansion(comentario.id)" class="toggle-replies-btn">
                          {{ comentariosExpandidos.includes(comentario.id) ? 'Ocultar respuestas' : `Ver
                          ${comentario.respuestas.length} respuesta(s)` }}
                        </button>
                      </div>


                      <!-- Mostrar formulario de edición o texto normal -->
                      <div v-if="comentario.id === comentarioEditando" class="edit-comment-form">
                        <textarea v-model="comentarioEditado" class="comment-edit-input" rows="3"
                          placeholder="Edita tu comentario..." autofocus></textarea>
                        <div class="edit-actions">
                          <button @click="guardarEdicionComentario(comentario)" class="btn-save">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                              fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                              stroke-linejoin="round">
                              <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                              <polyline points="17 21 17 13 7 13 7 21"></polyline>
                              <polyline points="7 3 7 8 15 8"></polyline>
                            </svg>
                            Guardar
                          </button>
                          <button @click="cancelarEdicionComentario" class="btn-cancel">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                              fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                              stroke-linejoin="round">
                              <circle cx="12" cy="12" r="10"></circle>
                              <line x1="15" y1="9" x2="9" y2="15"></line>
                              <line x1="9" y1="9" x2="15" y2="15"></line>
                            </svg>
                            Cancelar
                          </button>
                        </div>
                      </div>


                      <p class="comment-text">{{ comentario.texto }}</p>
                      <div class="comment-actions">
                        <button @click="likeComentario(comentario.id)" class="comment-action like-comment"
                          :class="{ liked: comentario.isLiked }">
                          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                            <path
                              d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z" />
                          </svg>
                          <span>{{ comentario.likes }}</span>
                        </button>

                        <!-- Nuevos botones -->
                        <button v-if="isCommentAuthor(comentario)" @click="editarComentario(comentario)"
                          class="comment-action edit-btn">
                          Editar
                        </button>
                        <button v-if="isCommentAuthor(comentario)" @click="eliminarComentario(comentario)"
                          class="comment-action delete-btn">
                          Eliminar
                        </button>

                        <button @click="toggleReply(comentario.id)" class="comment-action reply-comment">
                          {{ comentarioRespondiendo === comentario.id ? 'Cancelar' : 'Responder' }}
                        </button>
                      </div>




                      <!-- Respuestas -->
                      <div
                        v-if="comentariosExpandidos.includes(comentario.id) && comentario.respuestas && comentario.respuestas.length > 0"
                        class="comment-replies">
                        <div v-for="respuesta in comentario.respuestas" :key="respuesta.id"
                          class="comment-item reply-item">
                          <div class="comment-avatar-wrapper">
                            <div class="comment-avatar-placeholder">
                              <span>{{ respuesta.user?.id || respuesta.userId || '' }}</span>
                            </div>
                          </div>
                          <div class="comment-content">
                            <div class="comment-header">
                              <span class="comment-author">Usuario{{ respuesta.userId }}</span>
                              <span class="comment-time">{{ formatRelativeTime(respuesta.created_at) }}</span>
                            </div>
                            <p class="comment-text">{{ respuesta.texto }}</p>
                            <div class="comment-actions">
                              <button @click="likeComentario(respuesta.id)" class="comment-action like-comment"
                                :class="{ liked: respuesta.isLiked }">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                                  <path
                                    d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z" />
                                </svg>
                                <span>{{ respuesta.likes }}</span>
                              </button>
                            </div>
                          </div>
                        </div>
                      </div>


                    </div>
                  </div>
                </div>





                <!-- Formulario de comentario principal o respuesta -->
                <div class="add-comment-form">
                  <div v-if="comentarioRespondiendo" class="replying-to">
                    Respondiendo a <strong>@Usuario{{ findCommentById(comentarioRespondiendo)?.userId }}</strong>
                    <button @click="cancelarRespuesta" class="cancel-reply-btn">
                      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                      </svg>
                    </button>
                  </div>
                  <form @submit.prevent=" addComment()" class="comment-form">
                    <input v-model="nuevoComentario" ref="comentarioInput" type="text"
                      :placeholder="comentarioRespondiendo ? 'Escribe tu respuesta...' : 'Escribe un comentario...'"
                      class="comment-input" @focus="inputFocused = true" @blur="inputFocused = false"
                      @keydown.esc="cancelarRespuesta">
                    <button type="submit" :disabled="!nuevoComentario.trim()" class="submit-comment-btn"
                      :class="{ disabled: !nuevoComentario.trim() }">
                      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                        <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z" />
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
      <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <line x1="12" y1="5" x2="12" y2="19"></line>
        <line x1="5" y1="12" x2="19" y2="12"></line>
      </svg>
    </button>



    <!-- Modal para crear/editar post -->
    <div v-if="mostrarModal" class="modal-overlay" @click.self="cerrarModal">
      <div class="modal-container">
        <div class="modal-header">
          <h2>{{ modoEdicion ? 'Editar post' : 'Crear nuevo post' }}</h2>
          <button @click="cerrarModal" class="modal-close-btn">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
              stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <line x1="18" y1="6" x2="6" y2="18"></line>
              <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
          </button>
        </div>

        <div class="modal-content-wrapper">
          <form @submit.prevent="submitPost" class="modal-form">
            <div class="form-group">
              <label for="titulo">Título</label>
              <input v-model="nuevoPost.titulo" id="titulo" type="text" placeholder="¿De qué quieres hablar?"
                required />
            </div>

            <div class="form-group">
              <label for="contenido">Contenido</label>
              <textarea v-model="nuevoPost.contenido" id="contenido"
                placeholder="Comparte tus ideas, preguntas o experiencias..." rows="5" required></textarea>
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
                <input type="file" id="imagen" @change="handleFileSelect" accept="image/*" class="file-upload-input" />
                <span class="file-upload-btn">
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                    <polyline points="17 8 12 3 7 8"></polyline>
                    <line x1="12" y1="3" x2="12" y2="15"></line>
                  </svg>
                  Subir imagen
                </span>
                <span v-if="nuevoPost.imagenFile" class="file-upload-name">
                  {{ nuevoPost.imagenFile.name }}
                </span>
                <span v-else-if="modoEdicion && postSeleccionado?.imagen" class="file-upload-name">
                  Imagen actual: {{ postSeleccionado.imagen.split('/').pop() }}
                </span>
              </label>
              <button v-if="modoEdicion && postSeleccionado?.imagen" @click.prevent="eliminarImagen"
                class="btn-eliminar-imagen">
                Eliminar imagen
              </button>
            </div>

            <div class="form-actions">
              <button type="button" @click="cerrarModal" class="btn btn-secondary">Cancelar</button>
              <button type="submit" class="btn btn-primary">
                {{ modoEdicion ? 'Actualizar post' : 'Publicar post' }}
              </button>
            </div>
          </form>

          <!-- Vista previa externa -->
          <div v-if="mostrarModal && (imagenMiniatura || (modoEdicion && postSeleccionado?.imagen))"
            class="external-preview">
            <img :src="imagenMiniatura || postSeleccionado.imagen" alt="Previsualización" class="preview-image">
          </div>
        </div>
      </div>
    </div>

    <!-- Paginación -->
    <paginatorComponent v-model="currentPage" :total-items="postsFiltrados.length" :items-per-page="itemsPerPage"
      :max-pages-shown="5" @page-changed="handlePageChange" />

  </div>
</template>




<script>
import axios from 'axios';
import paginatorComponent from '@/components/paginatorComponent.vue';

export default {
  name: 'ForoComponent',
  components: {
    paginatorComponent,
  },
  data() {
    return {
      currentPage: 1,
      itemsPerPage: 9,


      posts: [],
      postsFiltrados: [],
      categoriaSeleccionada: '',
      terminoBusqueda: '',
      mostrarModal: false,
      nuevoPost: {
        titulo: '',
        contenido: '',
        categoria: '',
        imagenFile: null
      },
      imagenMiniatura: null,
      postSeleccionado: null,
      scrollPosition: 0,
      inputFocused: false,
      nuevoComentario: '',
      comentariosExpandidos: [],
      comentarioRespondiendo: null,


      modoEdicion: false,
      editandoComentario: null,

      comentarioEditado: '',
      comentarioEditando: null
    };
  },

  computed: {

    postsPaginados() {
      const start = (this.currentPage - 1) * this.itemsPerPage;
      const end = start + this.itemsPerPage;
      return this.postsFiltrados.slice(start, end);

    },

    totalPages() {
      return Math.ceil(this.postsFiltrados.length / this.itemsPerPage);
    }
  },

  methods: {
    // Métodos de UI
    abrirModal() {
      this.mostrarModal = true;
      document.body.style.overflow = 'hidden';
    },

    cerrarModal() {
      this.mostrarModal = false;
      this.nuevoPost = { titulo: '', contenido: '', categoria: '', imagenFile: null };
      this.imagenMiniatura = null;
      document.body.style.overflow = 'auto';
    },


    handlePageChange(newPage) {
      this.currentPage = newPage;

      this.$nextTick(() => {
        window.scrollTo({
          top: 0,
          behavior: 'smooth'
        });
      });
    },


    async submitPost() {
      if (this.modoEdicion) {
        await this.editarPost();
      } else {
        await this.createPost();
      }
    },



    // Métodos para el Popup
    abrirPopout(post) {
      // Guardar posición antes de cualquier cambio de estilo
      this.scrollPosition = window.pageYOffset || document.documentElement.scrollTop;

      // Aplicar estilos sin position: fixed
      document.body.style.overflow = 'hidden';
      document.documentElement.style.scrollBehavior = 'auto'; // Prevenir scroll suave

      this.postSeleccionado = {
        ...post,
        isLiked: false,
        comments: post.comments.map(comment => ({
          ...comment,
          isLiked: false,
          respuestas: comment.respuestas || []
        }))
      };
    },

    cerrarPopout() {
      // Restaurar estilos correctamente
      document.body.style.overflow = 'auto';
      document.body.style.position = 'relative'; // Cambiar de static a relative
      document.body.style.top = 'auto';

      // Restaurar scroll después de actualizar el DOM
      this.$nextTick(() => {
        window.scrollTo({
          top: this.scrollPosition,
          behavior: 'auto'
        });
      });

      // Restablecer estados
      this.postSeleccionado = null;
      this.comentarioRespondiendo = null;
      this.nuevoComentario = '';
    },

    toggleCommentExpansion(commentId) {
      const index = this.comentariosExpandidos.indexOf(commentId);
      if (index > -1) {
        this.comentariosExpandidos.splice(index, 1);
      } else {
        this.comentariosExpandidos.push(commentId);
      }
    },

    focusComentario() {
      this.$nextTick(() => {
        this.$refs.comentarioInput?.focus();
      });
    },

    abrirPopoutYFocalizarComentario(post) {
      this.abrirPopout(post);
      this.$nextTick(() => {
        // Espera a que el popout esté renderizado
        setTimeout(() => {
          const input = this.$refs.comentarioInput;
          if (input) {
            input.focus();
            // Desplázate al área de comentarios
            const container = this.$refs.commentsContainer;
            if (container) {
              container.scrollTop = container.scrollHeight;
            }
          }
        }, 300);
      });
    },





    // Métodos para Posts
    async createPost() {
      const formData = new FormData();
      formData.append('titulo', this.nuevoPost.titulo);
      formData.append('contenido', this.nuevoPost.contenido);
      formData.append('categoria', this.nuevoPost.categoria);

      if (this.nuevoPost.imagenFile) {
        formData.append('imagen', this.nuevoPost.imagenFile);
      }

      try {
        await axios.post('/post', formData, {
          headers: { 'Content-Type': 'multipart/form-data' }
        });
        await this.getPost();
        this.cerrarModal();
      } catch (error) {
        console.error('Error creando post:', error);
        alert('Error al crear el post');
      }
    },


    async getPost() {
      try {
        const response = await axios.get('/post');
        // Ordenar posts por fecha descendente
        this.posts = response.data.posts.sort((a, b) => {
          return new Date(b.created_at) - new Date(a.created_at);
        }).map(post => ({
          ...post,
          comments: post.comments
            // Orden descendente para comentarios principales
            .sort((a, b) => new Date(b.created_at) - new Date(a.created_at))
            .map(comment => ({
              ...comment,
              // CORRECCIÓN: Ordenar respuestas por fecha descendente
              respuestas: (comment.replies || []).sort((a, b) =>
                new Date(b.created_at) - new Date(a.created_at)), // Cambiado a b - a
              user: { id: comment.user_id }
            }))
        }));
        this.postsFiltrados = [...this.posts];
      } catch (error) {
        console.error('Error obteniendo posts:', error);
      }
    },


    handleFileSelect(event) {
      const file = event.target.files[0];
      if (!file) return;

      this.nuevoPost.imagenFile = file;
      const reader = new FileReader();
      reader.onload = (e) => {
        this.imagenMiniatura = e.target.result;
      };
      reader.readAsDataURL(file);
    },



    // METODOS PARA COMENTARIOS

    async addComment() {
      if (!this.nuevoComentario.trim()) return;

      try {
        const endpoint = this.comentarioRespondiendo
          ? `/post/create-reply/${this.comentarioRespondiendo}`
          : `/post/create-comment`;

        const payload = {
          texto: this.nuevoComentario,
          post_id: this.postSeleccionado.id
        };

        const response = await axios.post(endpoint, payload);

        if (!response.data || !response.data.success) {
          throw new Error(response.data.message || 'Error desconocido');
        }

        // 1. Primero actualizamos el post en el array principal
        const postIndex = this.posts.findIndex(p => p.id === this.postSeleccionado.id);
        if (postIndex !== -1) {
          const newComment = {
            ...response.data.comment,
            created_at: new Date().toISOString(),
            likes: 0,
            isLiked: false,
            user: { id: response.data.comment.user_id },
            respuestas: []
          };

          if (this.comentarioRespondiendo) {
            const parentCommentIndex = this.posts[postIndex].comments.findIndex(
              c => c.id === this.comentarioRespondiendo
            );
            if (parentCommentIndex !== -1) {
              this.posts[postIndex].comments[parentCommentIndex].respuestas =
                this.posts[postIndex].comments[parentCommentIndex].respuestas || [];
              this.posts[postIndex].comments[parentCommentIndex].respuestas.unshift(newComment);
            }
          } else {
            this.posts[postIndex].comments.unshift(newComment);
          }

          // 2. Luego actualizamos el post seleccionado con los datos del array principal
          // Esto evita la duplicación ya que usamos la misma fuente de datos
          this.postSeleccionado = {
            ...this.posts[postIndex],
            isLiked: this.postSeleccionado.isLiked // Mantenemos el estado del like
          };
        }

        this.nuevoComentario = '';
        this.comentarioRespondiendo = null;

      } catch (error) {
        console.error('Error detallado:', error.response?.data || error.message);
        alert(`Error al guardar: ${error.response?.data?.message || error.message}`);
      }
    },





    // METODOS PARA LIKES

    async likePost() {
      try {
        const response = await axios.post(`/post/${this.postSeleccionado.id}/likes_quantity`); // Usar /post/ID/likes_quantity
        this.postSeleccionado.likes_quantity = response.data.likes_quantity;
        this.postSeleccionado.isLiked = response.data.is_liked;
      } catch (error) {
        console.error('Error actualizando like:', error);
      }
    },

    async likeComentario(commentId) {
      try {
        const comment = this.findCommentById(commentId);
        // Usar la ruta correcta para comentarios
        const response = await axios.post(`/comment/${commentId}/likes_quantity`);
        // Actualizar desde la respuesta
        comment.likes = response.data.likes_quantity;
        comment.isLiked = response.data.is_liked;
      } catch (error) {
        console.error('Error actualizando like de comentario:', error);
      }
    },






    // METODOS PARA EDITAR Y ELIMINAR EN POST

    isPostAuthor(post) {
      // Implementa según tu sistema de autenticación
      // Ejemplo: return post.userId === this.$store.state.user.id;
      return true; // Cambiar según tu lógica
    },

    isCommentAuthor(comment) {
      // Implementa según tu sistema de autenticación
      // Ejemplo: return comment.userId === this.$store.state.user.id;
      return true; // Cambiar según tu lógica
    },


    // En el método editarPost
    async editarPost() {
      try {
        let response;

        if (this.nuevoPost.imagenFile) {
          const formData = new FormData();
          formData.append('_method', 'PUT');
          formData.append('titulo', this.nuevoPost.titulo);
          formData.append('contenido', this.nuevoPost.contenido);
          formData.append('categoria', this.nuevoPost.categoria);
          formData.append('imagen', this.nuevoPost.imagenFile);

          response = await axios.post(`/post/${this.nuevoPost.id}`, formData, {
            headers: { 'Content-Type': 'multipart/form-data' },
          });
        } else {
          response = await axios.put(`/post/${this.nuevoPost.id}`, this.nuevoPost);
        }

        // Actualizar lista de posts
        await this.getPost();

        // Cerrar modal y popup
        this.cerrarModal();
        this.postSeleccionado = null;

        // Forzar actualización del paginador
        this.currentPage = Math.min(this.currentPage, Math.ceil(this.postsFiltrados.length / this.itemsPerPage));
        window.scrollTo({ top: 0, behavior: 'smooth' });

      } catch (error) {
        console.error('Error:', error.response?.data);
        alert(error.response?.data?.message || 'Error al editar');
      }
    },


    async confirmarEliminarPost() {
      if (confirm('¿Estás seguro de eliminar este post?')) {
        try {
          const postId = this.postSeleccionado.id;

          const response = await axios.delete(`/post/${postId}`);

          if (response.status === 200) {
            // Eliminar de los arrays principales
            this.posts = this.posts.filter(p => p.id !== postId);
            this.postsFiltrados = this.postsFiltrados.filter(p => p.id !== postId);

            // Cerrar el popup
            this.cerrarPopout();

            // Forzar actualización del paginador
            this.currentPage = Math.min(this.currentPage, Math.ceil(this.postsFiltrados.length / this.itemsPerPage));
          }
        } catch (error) {
          console.error('Error eliminando post:', error.response?.data || error.message);
          alert('No se pudo eliminar el post');
        }
      }
    },


    abrirEditarModal(post) {
      this.modoEdicion = true;
      this.mostrarModal = true;
      this.nuevoPost = {
        id: post.id,
        titulo: post.titulo,
        contenido: post.contenido,
        categoria: post.categoria,
        imagenFile: null
      };
      this.imagenMiniatura = post.imagen;
    },






    // METODOS PARA EDITAR Y ELIMINAR EN COMENTARIOS

    editarComentario(comentario) {
      this.comentarioEditando = comentario.id;
      this.comentarioEditado = comentario.texto;
    },


    guardarEdicionComentario(comentario) {

      if (!this.comentarioEditado.trim()) {
        alert('El comentario no puede estar vacío');
        return;
      }

      const endpoint = comentario.parent_id
        ? `/post/update-reply/${comentario.id}`
        : `/post/update-comment/${comentario.id}`;

      axios.put(endpoint, { texto: this.comentarioEditado })
        .then(response => {
          this.getPost()
            .then(() => {
              const postEncontrado = this.posts.find(post => Number(post.id) === Number(this.postSeleccionado.id));
              if (postEncontrado) {
                this.postSeleccionado = { ...postEncontrado };
              }
            })
            .catch(error => {
              console.error('Error al editar comentario:', error);
            });
        })
        .catch(error => {
          console.error('Error editando comentario:', error);
          alert('Error al guardar cambios: ' + (error.response?.data?.message || error.message));
        });

        setTimeout(() => {
    this.comentarioEditando = null;
    this.comentarioEditado = '';
}, 1500); // 300ms de delay para una transición suave

    },



    eliminarComentario(comentario) {
      if (!confirm('¿Eliminar este comentario permanentemente?')) return;

      const endpoint = comentario.parent_id
        ? `/post/destroy-reply/${comentario.id}`
        : `/post/delete-comment/${comentario.id}`;

      axios.delete(endpoint)
        .then(() => {
          this.getPost()
            .then(() => {
              const postEncontrado = this.posts.find(post => Number(post.id) === Number(this.postSeleccionado.id));
              if (postEncontrado) {
                this.postSeleccionado = { ...postEncontrado };
              }
            })
            .catch(error => {
              console.error('Error al eliminar el comentario:', error);
            });
        })
        .catch(error => {
          console.error('Error eliminando comentario:', error);
          alert('Error al eliminar');
        });
    },

    cancelarEdicionComentario() {
      this.comentarioEditando = null;
      this.comentarioEditado = '';
    },




    // METODOS AUXILIARES

    filtrarPosts() {
      const filtered = this.posts.filter(post => {
        const matchesCategory = this.categoriaSeleccionada
          ? post.categoria === this.categoriaSeleccionada
          : true;

        const matchesSearch = this.terminoBusqueda
          ? post.titulo.toLowerCase().includes(this.terminoBusqueda.toLowerCase()) ||
          post.contenido.toLowerCase().includes(this.terminoBusqueda.toLowerCase())
          : true;

        return matchesCategory && matchesSearch;
      });

      // Resetear a la primera página cuando se filtran resultados
      this.currentPage = 1;
      this.postsFiltrados = filtered;
    },



    cambiarCategoria(categoria) {
      this.categoriaSeleccionada = categoria;
      this.filtrarPosts();
    },

    categoryColor(categoria) {
      const colors = {
        'Deporte': '#17a2b8',
        'Gym': '#28a745',
        'Experiencia': '#ffc107',
        'Lugares': '#dc3545'
      };
      return colors[categoria] || '#6c757d';
    },

    formatDate(dateString) {
      const options = { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' };
      return new Date(dateString).toLocaleDateString('es-ES', options);
    },

    formatRelativeTime(dateString) {
      const now = new Date();
      const date = new Date(dateString);
      const diff = now - date;

      const minutes = Math.floor(diff / 60000);
      if (minutes < 1) return 'Ahora mismo';
      if (minutes < 60) return `Hace ${minutes} min`;

      const hours = Math.floor(minutes / 60);
      if (hours < 24) return `Hace ${hours} h`;

      const days = Math.floor(hours / 24);
      return `Hace ${days} d`;
    },

    scrollToBottom() {
      this.$nextTick(() => {
        const container = this.$refs.commentsContainer;
        if (container) container.scrollTop = container.scrollHeight;
      });
    },

    findCommentById(id) {
      const searchComments = (comments) => {
        for (const comment of comments) {
          if (comment.id === id) return comment;
          if (comment.respuestas?.length) {
            const found = searchComments(comment.respuestas);
            if (found) return found;
          }
        }
        return null;
      };
      return searchComments(this.postSeleccionado?.comments || []);
    },

    toggleReply(commentId) {
      this.comentarioRespondiendo = this.comentarioRespondiendo === commentId ? null : commentId;
    },

    cancelarRespuesta() {
      this.comentarioRespondiendo = null;
      this.nuevoComentario = '';
    }






  },
  mounted() {
    this.getPost();
    document.title = 'Foro';
  }
}
</script>





<style scoped>
@import '../../../scss/Foro/foro.scss';

@import '../../../scss/Foro/foro_filtro_y_busqueda.scss';

@import '../../../scss/Foro/foro_post.scss';

@import '../../../scss/Foro/foro_pop_out_post.scss';

@import '../../../scss/Foro/foro_modal.scss';

@import '../../../scss/Foro/foro_navbar.scss';

</style>