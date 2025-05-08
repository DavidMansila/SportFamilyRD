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

           <!-- Secciones para lo usuarios y no usuarios -->
           <a href="/Noticias" class="nav-link">Noticias</a>
           <a href="/Calendario" class="nav-link">Calendario</a>
           <a href="/Tienda" class="nav-link">Tienda</a>
           <a href="/Entrenadores" class="nav-link">Entrenadores</a>
           <a href="/Foro" class="nav-link">Foro</a>

          <!-- Secciones para entrenadores -->
           <a v-if = "userType == 'entrenador'" href="/SolicitudesUsuarios" class="nav-link">Solicitudes</a>

          <!-- Secciones para entrenadores -->
          <a v-if = "userType == 'admin'" href="/SolicitudesEntrenadores" class="nav-link">Solicitudes</a>


      </div>

      <div class="Imagenes">

        <a href="#" class="Carrito">
          <img src="/imagenes/Carrito-Icon.png" alt="Carrito" class="carrito-icon"/>
        </a>

        <a href= "/Ajustes" class="Ajustes">
          <img src="/imagenes/Ajustes-Icon.png" alt="Ajustes" class="ajustes-icon"/>
        </a>

        <a href= "/Perfil" class="Perfil">
          <img src="/imagenes/Perfil-Icon.png" alt="Perfil" class="perfil-icon"/>
        </a>

        <a :href=" login ? '/Login' : '/Logout' " class="Logout">
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




    <!-- Sección de Posts -->
    <div class="posts-grid">
      <div v-if="postsFiltrados.length === 0" class="no-posts-container">
    <div class="no-posts-content">
        <h2 class="no-posts">No hay publicaciones disponibles</h2>
        <p class="no-posts-subtitle">¡Sé el primero en iniciar una publicación!</p>
        <button @click="abrirModal" class="no-posts-btn">
          <span>Crear nuevo post</span>
        </button>
        <div class="no-posts-image-container">
          <img src="/imagenes/no-news.png" alt="No hay publicaciones" class="no-posts-image no-posts-image-float">
        </div>
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
            <span class="post-author">Usuario{{ post.id }}</span>
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
              <span class="post-likes" @click.stop="toggleLike(post)">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" 
                    :stroke="post.isLiked ? 'currentColor' : 'currentColor'"
                    :fill="post.isLiked ? 'currentColor' : 'none'" 
                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M14 9V5a3 3 0 0 0-3-3l-4 9v11h11.28a2 2 0 0 0 2-1.7l1.38-9a2 2 0 0 0-2-2.3zM7 22H4a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2h3"></path>
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
            <button @click.stop="abrirPopout(post)" class="btn-leer">Ver discusión</button>
          </div>

      </div>
    </div>




    <!-- Popout para ver publicación completa -->
    <transition name="fade">
      <div v-if="postSeleccionado" class="post-popout-overlay" @click.self="cerrarPopout">
        <div class="post-popout-container">
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
                <div class="post-actions" v-if="isPostAuthor(postSeleccionado)">
                  <button @click="editarPost" class="edit-post-btn" title="Editar post">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                      <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                      <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                    </svg>
                  </button>
                  <button @click="confirmarEliminarPost" class="delete-post-btn" title="Eliminar post">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                      <polyline points="3 6 5 6 21 6"></polyline>
                      <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                    </svg>
                  </button>
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
                        <button 
                          v-if="isCommentAuthor(comentario)"
                          @click="editarComentario(comentario)" 
                          class="comment-action edit-comment"
                          title="Editar comentario"
                        >
                          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                          </svg>
                        </button>
                        <button 
                          v-if="isCommentAuthor(comentario)"
                          @click="eliminarComentario(comentario.id)" 
                          class="comment-action delete-comment"
                          title="Eliminar comentario"
                        >
                          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="3 6 5 6 21 6"></polyline>
                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                          </svg>
                        </button>
                      </div>




                      <!-- Respuestas -->
                      <div v-if="comentariosExpandidos.includes(comentario.id)" class="comment-replies">
                        <!-- Cargador mientras se obtienen las respuestas -->
                        <div v-if="!comentario.respuestas" class="loading-replies">
                          Cargando respuestas...
                        </div>

                        <!-- Mostrar respuestas si existen -->
                        <div v-else-if="comentario.respuestas.length > 0">
                          <div v-for="(respuesta, i) in comentario.respuestas" :key="respuesta.id" class="comment-item reply-item">
                            <div class="comment-avatar-wrapper">
                              <div class="comment-avatar-placeholder">
                                <span>U{{ respuesta.userId % 100 }}</span>
                              </div>
                            </div>
                            <div class="comment-content">
                              <div class="comment-header">
                                <span class="comment-author">Usuario{{ respuesta.userId }}</span>
                                <span class="comment-time">{{ formatRelativeTime(respuesta.created_at) }}</span>
                              </div>
                              <p class="comment-text">{{ respuesta.texto }}</p>
                              <div class="comment-actions">
                                <button @click="likeComentario(respuesta.id)" class="comment-action like-comment" :class="{ liked: respuesta.isLiked }">
                                  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                                    <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                                  </svg>
                                  <span>{{ respuesta.likes }}</span>
                                </button>
                                <button @click="toggleReply(respuesta.id)" class="comment-action reply-comment">
                                  {{ comentarioRespondiendo === respuesta.id ? 'Cancelar' : 'Responder' }}
                                </button>
                              </div>
                            </div>
                          </div>
                        </div>
                
                        <!-- Mensaje si no hay respuestas -->
                        <div v-else class="no-replies">
                          No hay respuestas aún. ¡Sé el primero en responder!
                        </div>
                        

                        <!-- Formulario para responder a comentario específico -->
                        <div v-if="comentarioRespondiendo === comentario.id" class="reply-form-container">
                          <div class="replying-to">
                            Respondiendo a <strong>@Usuario{{ getCommentAuthor(comentarioRespondiendo) }}</strong>
                            <button @click="cancelarRespuesta" class="cancel-reply-btn">
                              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="18" y1="6" x2="6" y2="18"></line>
                                <line x1="6" y1="6" x2="18" y2="18"></line>
                              </svg>
                            </button>
                          </div>
                          <form @submit.prevent="submitReply" class="reply-form">
                            <input
                              v-model="nuevoComentario"
                              type="text"
                              placeholder="Escribe tu respuesta..."
                              class="reply-input"
                              ref="replyInput"
                            >
                            <button 
                              type="submit" 
                              :disabled="!nuevoComentario.trim()" 
                              class="submit-reply-btn"
                              :class="{ disabled: !nuevoComentario.trim() }"
                            >
                              {{ editandoComentario ? 'Actualizar' : 'Enviar' }}
                            </button>
                          </form>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>




                <!-- Formulario de comentario principal o respuesta -->
                <div class="add-comment-form">
                  <div v-if="comentarioRespondiendo && !editandoComentario" class="replying-to">
                    Respondiendo a <strong>@Usuario{{ getCommentAuthor(comentarioRespondiendo) }}</strong>
                    <button @click="cancelarRespuesta" class="cancel-reply-btn">
                      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                      </svg>
                    </button>
                  </div>
                  <div v-if="editandoComentario" class="replying-to">
                    Editando comentario
                    <button @click="cancelarEdicionComentario" class="cancel-reply-btn">
                      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                      </svg>
                    </button>
                  </div>
                  <form @submit.prevent="addComment" class="comment-form">
                    <input
                      v-model="nuevoComentario"
                      ref="comentarioInput"
                      type="text"
                      :placeholder="editandoComentario ? 'Edita tu comentario...' : comentarioRespondiendo ? 'Escribe tu respuesta...' : 'Escribe un comentario...'"
                      class="comment-input"
                      @focus="inputFocused = true"
                      @blur="inputFocused = false"
                      @keydown.esc="editandoComentario ? cancelarEdicionComentario() : cancelarRespuesta()"
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



    <!-- Modal para crear/editar post -->
    <div v-if="mostrarModal" class="modal-overlay" @click.self="cerrarModal">
      <div class="modal-container">
        <div class="modal-header">
          <h2>{{ modoEdicion ? 'Editar post' : 'Crear nuevo post' }}</h2>
          <button @click="cerrarModal" class="modal-close-btn">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <line x1="18" y1="6" x2="6" y2="18"></line>
              <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
          </button>
        </div>
        
        <div class="modal-content-wrapper">
          <form @submit.prevent="submitPost" class="modal-form">
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
                <span v-else-if="modoEdicion && postSeleccionado?.imagen" class="file-upload-name">
                  Imagen actual: {{ postSeleccionado.imagen.split('/').pop() }}
                </span>
              </label>
              <button 
                v-if="modoEdicion && postSeleccionado?.imagen" 
                @click.prevent="eliminarImagen" 
                class="btn-eliminar-imagen"
              >
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
          <div v-if="mostrarModal && (imagenMiniatura || (modoEdicion && postSeleccionado?.imagen))" class="external-preview">
            <img :src="imagenMiniatura || postSeleccionado.imagen" alt="Previsualización" class="preview-image">
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
        id: null
      },

      imagenMiniatura: null,
      imageLoaded: false,

      postSeleccionado: null,
      scrollPosition: 0,

      inputFocused: false,

      nuevoComentario: '',

      modoEdicion: false,

      comentariosExpandidos: [],
      comentarioRespondiendo: null,
      editandoComentario: null

    };
  },



  methods: {



    // METODOS DE USUARIO
    abrirModal() {
      this.mostrarModal = true;
      document.body.style.overflow = 'hidden';
    },

    cerrarModal() {
      this.mostrarModal = false;
      this.limpiarFormulario();
      document.body.style.overflow = 'auto';
    },

    limpiarFormulario() {
      this.nuevoPost = {
        titulo: '',
        contenido: '',
        categoria: '',
        imagenFile: null,
        id: null
      };
      this.imagenMiniatura = null;
      this.modoEdicion = false;
    },






    // METODOS PARA POST
    async submitPost() {
      const formData = new FormData();
      formData.append('titulo', this.nuevoPost.titulo);
      formData.append('contenido', this.nuevoPost.contenido);
      formData.append('categoria', this.nuevoPost.categoria);
      
      if (this.nuevoPost.imagenFile) {
        formData.append('imagen', this.nuevoPost.imagenFile);
      } else if (this.modoEdicion && !this.imagenMiniatura && this.postSeleccionado?.imagen) {
        // Indica que se debe eliminar la imagen existente
        formData.append('eliminar_imagen', 'true');
      }

      try {
        const config = { 
          headers: { 'content-type': 'multipart/form-data' } 
        };
        
        if (this.modoEdicion && this.nuevoPost.id) {
          formData.append('_method', 'PUT');
          await axios.post(`/post/${this.nuevoPost.id}`, formData, config);
          this.$toast.success('Post actualizado correctamente');
        } else {
          await axios.post('/post', formData, config);
          this.$toast.success('Post creado correctamente');
        }

        this.limpiarFormulario();
        this.cerrarModal();
        await this.getPost();
        
        // Si estamos editando desde el popout, actualizamos el post seleccionado
        if (this.postSeleccionado && this.modoEdicion) {
          const postActualizado = this.posts.find(p => p.id === this.postSeleccionado.id);
          if (postActualizado) {
            this.postSeleccionado = { ...postActualizado };
          }
        }
      } catch (error) {
        console.error('Error al guardar el post:', error);
        this.$toast.error(`Error al ${this.modoEdicion ? 'actualizar' : 'crear'} el post`);
      }
    },

    eliminarImagen() {
      this.nuevoPost.imagenFile = null;
      this.imagenMiniatura = null;
      this.$toast.info('Imagen marcada para eliminación');
    },

    async getPost() {
      try {
        const response = await axios.get('/post');
        this.posts = response.data.posts;
        this.postsFiltrados = [...this.posts];
      } catch (error) {
        console.error('Error al obtener los posts:', error);
        this.$toast.error('Error al cargar los posts');
      }
    },

    handleFileSelect(event) {
      const file = event.target.files[0];
      if (!file) return;
      
      if (!file.type.match('image.*')) {
        this.$toast.error('Por favor, selecciona solo imágenes (JPEG, PNG, etc.)');
        return;
      }
      
      if (file.size > 2 * 1024 * 1024) {
        this.$toast.error('La imagen es demasiado grande. Máximo 2MB permitido.');
        return;
      }
      
      this.nuevoPost.imagenFile = file;
      
      const reader = new FileReader();
      reader.onload = (e) => {
        this.imagenMiniatura = e.target.result;
      };
      reader.readAsDataURL(file);
    },



    toggleLike(post) {
    // Guarda el estado actual antes de cambiarlo
    const wasLiked = post.isLiked;
    const oldLikes = post.likes_quantity;
    
    // Cambia el estado visual inmediatamente
    post.isLiked = !wasLiked;
    post.likes_quantity = wasLiked ? oldLikes - 1 : oldLikes + 1;
    
    // Llama a la API
    axios.post(`/post/${post.id}/likes_quantity`, {
        isLiked: post.isLiked
      })
      .catch(error => {
        console.error('Error al dar like:', error);
        // Si hay error, revierte los cambios visuales
        post.isLiked = wasLiked;
        post.likes_quantity = oldLikes;
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






    // METODOS PARA FILTRADO Y BUSQUEDA
    filtrarPosts() {
      let postsFiltrados = this.categoriaSeleccionada 
        ? this.posts.filter(post => post.categoria === this.categoriaSeleccionada)
        : [...this.posts];

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

    cambiarCategoria(categoria) {
      this.categoriaSeleccionada = categoria;
      this.filtrarPosts();
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






    // METODOS PARA EL POPOUT DEL POST
    abrirPopout(post) {
      this.scrollPosition = window.pageYOffset || document.documentElement.scrollTop;
      document.body.style.overflow = 'hidden';
      document.body.style.position = 'fixed';
      document.body.style.top = `-${this.scrollPosition}px`;
      document.body.style.width = '100%';
      
      this.postSeleccionado = { ...post, isLiked: false };
    },
    
    cerrarPopout() {
      this.postSeleccionado = null;
      this.nuevoComentario = '';
      this.comentarioRespondiendo = null;
      this.editandoComentario = null;
      
      document.body.style.overflow = 'auto';
      document.body.style.position = '';
      document.body.style.top = '';
      document.body.style.width = '';
      
      window.scrollTo(0, this.scrollPosition);
    },

    toggleLike() {
      if (this.postSeleccionado.isLiked) {
        this.postSeleccionado.likes_quantity--;
      } else {
        this.postSeleccionado.likes_quantity++;
      }
      this.postSeleccionado.isLiked = !this.postSeleccionado.isLiked;
      
      // Aquí deberías hacer la llamada API para actualizar el like
      axios.post(`/post/${this.postSeleccionado.id}/likes_quantity`, {
        isLiked: this.postSeleccionado.isLiked
      });
    },






    // METODOS PARA COMENTARIOS
    addComment() {
        axios.post('/post/create-comment', {
          post_id: this.postSeleccionado.id,
          texto: this.nuevoComentario
        })
        .then(response => {
          this.nuevoComentario = '';
          this.scrollToBottom();
          
          // Actualizar los posts y luego el post seleccionado
          this.getPost()
          .then(() => {
            // Encontrar el post actualizado en la lista
            const postActualizado = this.posts.find(post => Number(post.id) === Number(this.postSeleccionado.id));
            
            if (postActualizado) {
              // Mantener el estado actual de isLiked y otros estados locales
              const wasLiked = this.postSeleccionado.isLiked;
              this.postSeleccionado = { ...postActualizado };
              this.postSeleccionado.isLiked = wasLiked;
              
              // Forzar la actualización de la UI si es necesario
              this.$forceUpdate();
            }
          })
          .catch(error => {
            console.error('Error al actualizar los posts:', error);
          });
        })
        .catch(error => {
          console.error('Error al agregar comentario:', error);
          
          this.$nextTick(() => {
            if (this.$refs.comentarioInput) {
              this.$refs.comentarioInput.focus();
            }
          });
        });
      },


      async getReply(commentId) {
      try {
        const response = await axios.get(`/post/get-reply/${commentId}`);
        if (this.postSeleccionado) {
          const comment = this.postSeleccionado.comments.find(c => c.id === commentId);
          if (comment) {
            comment.respuestas = response.data.replies;
            
            if (!this.comentariosExpandidos.includes(commentId)) {
              this.comentariosExpandidos.push(commentId);
            }
          }
        }
      } catch (error) {
        console.error('Error al obtener respuestas:', error);
        this.$toast.error('Error al cargar las respuestas');
      }
    },


    async submitReply() {
      try {
        if (this.editandoComentario) {
          await axios.put(`/post/update-comment/${this.editandoComentario.id}`, {
            texto: this.nuevoComentario
          });
          this.$toast.success('Respuesta actualizada');
        } else {
          await axios.post(`/post/create-reply/${this.comentarioRespondiendo}`, {
            texto: this.nuevoComentario
          });
          this.$toast.success('Respuesta enviada');
        }

        this.nuevoComentario = '';
        this.comentarioRespondiendo = null;
        this.editandoComentario = null;
        
        // Actualizar los comentarios
        await this.actualizarComentarios();
      } catch (error) {
        console.error('Error al guardar respuesta:', error);
        this.$toast.error('Error al guardar la respuesta');
      }
    },

    async actualizarComentarios() {
      try {
        const response = await axios.get(`/post/${this.postSeleccionado.id}`);
        this.postSeleccionado.comments = response.data.post.comments;
        this.scrollToBottom();
      } catch (error) {
        console.error('Error al actualizar comentarios:', error);
      }
    },

    toggleCommentExpansion(commentId) {
      const comment = this.postSeleccionado.comments.find(c => c.id === commentId);
      
      if (!this.comentariosExpandidos.includes(commentId)) {
        this.comentariosExpandidos.push(commentId);
        if (!comment.respuestas) {
          this.getReply(commentId);
        }
      } else {
        const index = this.comentariosExpandidos.indexOf(commentId);
        this.comentariosExpandidos.splice(index, 1);
      }
    },






    // METODOS PARA EDITAR Y ELIMINAR 
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

    editarPost() {
      this.nuevoPost = {
        id: this.postSeleccionado.id,
        titulo: this.postSeleccionado.titulo,
        contenido: this.postSeleccionado.contenido,
        categoria: this.postSeleccionado.categoria,
        imagenFile: null
      };
      
      if (this.postSeleccionado.imagen) {
        this.imagenMiniatura = this.postSeleccionado.imagen;
      }
      
      this.modoEdicion = true;
      this.mostrarModal = true;
      this.cerrarPopout();
    },

    async confirmarEliminarPost() {
      if (confirm('¿Estás seguro de que quieres eliminar este post?')) {
        try {
          await axios.delete(`/post/${this.postSeleccionado.id}`);
          this.cerrarPopout();
          await this.getPost();
          this.$toast.success('Post eliminado correctamente');
        } catch (error) {
          console.error('Error al eliminar el post:', error);
          this.$toast.error('Error al eliminar el post');
        }
      }
    },

    editarComentario(comentario) {
      this.nuevoComentario = comentario.texto;
      this.editandoComentario = { id: comentario.id };
      this.comentarioRespondiendo = null;
      this.$nextTick(() => {
        this.$refs.comentarioInput.focus();
      });
    },

    async eliminarComentario(commentId) {
      if (confirm('¿Estás seguro de que quieres eliminar este comentario?')) {
        try {
          await axios.delete(`/post/${post_id}/comment/${comments.id}`);
          await this.actualizarComentarios();
          this.$toast.success('Comentario eliminado');
        } catch (error) {
          console.error('Error al eliminar comentario:', error);
          this.$toast.error('Error al eliminar el comentario');
        }
      }
    },







    // METODOS DE INTERACCION
    toggleReply(commentId) {
      if (this.comentarioRespondiendo === commentId) {
        this.cancelarRespuesta();
      } else {
        this.comentarioRespondiendo = commentId;
        this.editandoComentario = null;
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

    cancelarEdicionComentario() {
      this.editandoComentario = null;
      this.nuevoComentario = '';
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

    likeComentario(commentId) {
      const comment = this.findCommentById(commentId);
      if (comment) {
        comment.isLiked = !comment.isLiked;
        comment.likes += comment.isLiked ? 1 : -1;
        
        // Aquí deberías hacer la llamada API para actualizar el like

        //AGREGAR LIKES_QUANTITY A LOS COMENTARIOS BASE DE DATOS 

        axios.post(`/post/comment/${commentId}/like_quantity`);
      }
    },
    
    findCommentById(id) {
      // Buscar en comentarios principales
      for (const comment of this.postSeleccionado.comments) {
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

    getCommentAuthor(commentId) {
      const comment = this.postSeleccionado.comments.find(c => c.id === commentId) ||
                    this.postSeleccionado.comments.flatMap(c => c.respuestas || []).find(r => r.id === commentId);
      return comment ? comment.userId : '';
    },




    // METODOS UTILITARIOS
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

    onImageLoad() {
      this.imageLoaded = true;
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