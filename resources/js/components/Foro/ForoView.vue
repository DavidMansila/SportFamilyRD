<template>
  <div class="foro-page" :class="{ 'no-scroll': postSeleccionado }">


    <!-- Navbar -->
    <Navbar />



    <!-- Hero Section -->
    <div class="hero-section">
      <div class="hero-content">
        <h1 class="titulo-foro">Comunidad Deportiva</h1>
        <p class="hero-subtitle">Conecta, comparte y aprende con otros apasionados del deporte</p>
        <button @click="abrirModal" class="btn-crear-post hero-btn" v-if="user">
          Crear nuevo post
        </button>
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
        <button @click="cambiarCategoria('🏅 Deporte')" :class="{ active: categoriaSeleccionada === '🏅 Deporte' }"
          class="filtro-btn">
          🏅 Deporte
        </button>
        <button @click="cambiarCategoria('🏋️ Gimnasio y Fitness')"
          :class="{ active: categoriaSeleccionada === '🏋️ Gimnasio y Fitness' }" class="filtro-btn">
          🏋️ Gimnasio y Fitness
        </button>
        <button @click="cambiarCategoria('📍 Lugares y Centros')"
          :class="{ active: categoriaSeleccionada === '📍 Lugares y Centros' }" class="filtro-btn">
          📍 Lugares y Centros
        </button>
        <button @click="cambiarCategoria('🧠 Consejos y Bienestar')"
          :class="{ active: categoriaSeleccionada === '🧠 Consejos y Bienestar' }" class="filtro-btn">
          🧠 Consejos y Bienestar
        </button>
      </div>
    </div>




    <!-- Sección de Posts -->
    <div class="posts-grid">

      <div class="no-posts-container" v-if="postsFiltrados.length === 0">

        <div class="no-posts-content">
          <h1 class="no-posts">¡No hay publicaciones!</h1>
          <p class="no-posts-subtitle">Sé el primero en crear una...</p>
          <button @click="abrirModal" class="btn-crear-post no-posts-btn">Crear Publicación</button>
          <img src="/imagenes/no-news.png" alt="No hay publicaciones" class="no-posts-image">
        </div>

      </div>

      <div v-else v-for="(post, index) in postsPaginados" :key="index" class="post-card"
        :style="`--hue: ${index * 60 % 360}`">
        <div class="post-header-container">
          <div class="post-categoria" :style="{ backgroundColor: categoryColor(post.categoria) }">
            {{ post.categoria || 'General' }}
          </div>
          <div class="post-header">
            <h3 class="post-titulo">{{ post.titulo }}</h3>
          </div>
        </div>

        <div class="post-imagen" v-if="tieneImagen(post)">
          <img :src="post.imagen" :alt="`Imagen de: ${post.titulo}`" class="post-image" loading="lazy"
            @load="onImageLoad(post.id)" :class="{ loaded: imageLoaded[post.id] }" />
        </div>

        <!-- El recorte del texto lo hace el CSS (line-clamp), no JavaScript:
             asi se adapta al ancho real de la tarjeta y no corta a mitad de
             palabra como hacia el substring(0,150) anterior. -->
        <p class="post-contenido">{{ post.contenido }}</p>

        <div class="post-autor">
          <img :src="getUserImage(post.user)" class="post-autor-avatar" alt="" loading="lazy" />
          <div class="post-autor-datos">
            <span class="post-author">{{ post.user?.name || `Usuario${post.user_id}` }}</span>
            <span class="post-date">{{ formatDate(post.created_at) }}</span>
          </div>
        </div>

        <div class="post-footer">
          <div class="post-stats">
            <span class="post-likes" @click.stop="toggleLike('post', post.id)" :class="{ liked: post.isLiked }">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24">
                <path
                  d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z" />
              </svg>
              {{ post.likes_count || 0 }}
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
    <transition name="fade" @before-enter="beforeEnter" @enter="enter" @leave="leave" :css="false">
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
                <img :src="postSeleccionado.imagen" @load="onImageLoad('selected')"
                  :class="{ loaded: imageLoaded['selected'] }" />
              </div>


              <div class="post-popout-body">
                <p class="post-popout-text">{{ postSeleccionado.contenido }}</p>
                <div class="post-meta-info">
                  <span class="post-date">{{ formatDate(postSeleccionado.created_at) }}</span>
                  <span class="post-visibility">Público</span>
                </div>
              </div>


              <div class="post-interactions">
                <button @click="toggleLike('post', postSeleccionado.id)" :class="{ liked: postSeleccionado.isLiked }"
                  class="interaction-btn like-btn">
                  <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24">
                    <path fill="currentColor"
                      d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z" />
                  </svg>
                  <span>{{ postSeleccionado.likes_count }}</span>
                </button>
                <button @click="focusComentario" class="interaction-btn comment-btn">
                  <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24">
                    <path fill="currentColor"
                      d="M21.99 4c0-1.1-.89-2-1.99-2H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h14l4 4-.01-18z" />
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
                  <img :src="getUserImage(postSeleccionado.user)" class="author-avatar" alt="Avatar del autor">
                  <div>
                    <h3 class="author-name">{{ postSeleccionado.user?.name || `Usuario${postSeleccionado.user_id}` }}
                    </h3>
                  </div>
                  <span class="post-category-badge"
                    :style="{ backgroundColor: categoryColor(postSeleccionado.categoria) }">
                    {{ postSeleccionado.categoria || 'General' }}
                  </span>
                </div>

                <!-- EDITAR Y ELIMINAR POST | HAY QUE PONER QUE SI EL ID DEL USUARIO COINCIDE CON EL ID DEL USUARIO DEL POST PUES PUEDE EDITAR Y ELIMINAR DICHO POST-->
                <div class="post-actions">
                  <button v-if="isPostAuthor(postSeleccionado)" @click="abrirEditarModal(postSeleccionado)"
                    class="edit-post-btn" title="Editar post">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                      stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                      <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                      <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                    </svg>
                  </button>
                  <button v-if="isPostAuthor(postSeleccionado) || (user && user.user_type === 'admin')"
                    @click="confirmarEliminarPost" class="delete-post-btn" title="Eliminar post">
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

                  <!-- Comentarios principales // HAY QUE PONER QUE SI EL COMENTARIO USER ID ES IGUAL AL USER ID QUE INICIO SECCION PUES QUE TE SALGAN LOS BOTONES DE EDITAR Y ELIMINAR--->

                  <div v-for="(comentario) in postSeleccionado.comments" :key="comentario.id" class="comment-item">
                    <div class="comment-avatar-wrapper">
                      <img :src="getUserImage(comentario.user)" class="comment-avatar" alt="Avatar del usuario" loading="lazy">
                    </div>
                    <div class="comment-content">
                      <div class="comment-header">

                        <span class="comment-author">{{ comentario.user?.name || `Usuario${comentario.user_id}`
                          }}</span>
                        <span class="comment-time">{{ formatRelativeTime(comentario.created_at) }}</span>
                        <button v-if="comentario.replies && comentario.replies.length > 0"
                          @click="toggleCommentExpansion(comentario.id)" class="toggle-replies-btn">
                          {{ comentariosExpandidos.includes(comentario.id) ? 'Ocultar respuestas' : `Ver
                          ${comentario.replies.length} respuesta(s)` }}
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
                            Guardar Comentario
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
                        <button @click="toggleLike('comment', comentario.id)" class="comment-action like-comment"
                          :class="{ liked: comentario.isLiked }">
                          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24">
                            <path
                              d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z" />
                          </svg>
                          <span>{{ comentario.likes_count || 0 }}</span>
                        </button>

                        <!-- EDITAR Y ELIMINAR COMENTARIOS BOTONES -->
                        <button v-if="isCommentAuthor(comentario)" @click="editarComentario(comentario)"
                          class="comment-action edit-btn">
                          Editar
                        </button>
                        <button v-if="isCommentAuthor(comentario) || (user && user.user_type === 'admin')"
                          @click="eliminarComentario(comentario)" class="comment-action delete-btn">
                          Eliminar
                        </button>

                        <button @click="toggleReply(comentario.id)" class="comment-action reply-comment">
                          {{ comentarioRespondiendo === comentario.id ? 'Cancelar' : 'Responder' }}
                        </button>
                      </div>




                      <!-- Respuestas // HAY QUE PONER QUE SI EL REPLY USER ID ES IGUAL AL USER ID QUE INICIO SECCION PUES QUE TE SALGAN LOS BOTONES DE EDITAR Y ELIMINAR-->
                      <div
                        v-if="comentariosExpandidos.includes(comentario.id) && comentario.replies && comentario.replies.length > 0"
                        class="comment-replies">
                        <div v-for="reply in comentario.replies" :key="reply.id" class="comment-item reply-item">
                          <div class="comment-avatar-wrapper">
                            <img :src="getUserImage(reply.user)" class="comment-avatar" alt="Avatar del usuario" loading="lazy">
                          </div>
                          <div class="comment-content">
                            <div class="comment-header">
                              <span class="comment-author">{{ reply.user?.name || `Usuario${reply.user_id}` }}</span>
                              <span class="comment-time">{{ formatRelativeTime(reply.created_at) }}</span>
                            </div>


                            <div v-if="reply.id === replyEditando" class="edit-comment-form">
                              <textarea v-model="replyEditado" class="comment-edit-input" rows="3"
                                placeholder="Edita tu comentario..." autofocus></textarea>
                              <div class="edit-actions">
                                <button @click="guardarEdicionReply(reply, comentario.id)" class="btn-save">
                                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                                    <polyline points="17 21 17 13 7 13 7 21"></polyline>
                                    <polyline points="7 3 7 8 15 8"></polyline>
                                  </svg>
                                  Guardar respuesta
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

                            <p class="comment-text">{{ reply.texto }}</p>
                            <div class="comment-actions">
                              <button @click="toggleLike('reply', reply.id)" class="comment-action like-comment"
                                :class="{ liked: reply.isLiked }">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24">
                                  <path
                                    d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z" />
                                </svg>
                                <span>{{ reply.likes_count || 0 }}</span>
                              </button>

                              <!-- EDITAR Y ELIMINAR REPLY BOTONES -->
                              <button v-if="isReplyAuthor(reply)" @click="editarReply(reply)"
                                class="comment-action edit-btn">
                                Editar
                              </button>
                              <button v-if="isReplyAuthor(reply) || (user && user.user_type === 'admin')"
                                @click="eliminarReply(reply)" class="comment-action delete-btn">
                                Eliminar
                              </button>


                            </div>
                          </div>
                        </div>
                      </div>


                    </div>
                  </div>
                </div>



                <!-- Formulario de comentario principal o respuesta -->
                <div class="add-comment-form" v-if="user">
                  <div v-if="comentarioRespondiendo" class="replying-to">
                    Respondiendo a <strong> @{{ getUsernameFromCommentId(comentarioRespondiendo) }}</strong>
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

                <div v-else class="login-prompt">
                  <p>Debes <router-link to="/signup">iniciar sesión</router-link> para participar en la conversación
                  </p>
                </div>

              </div>
            </div>
          </div>
        </div>
      </div>
    </transition>



    <!-- Botón flotante para crear post -->
    <!-- <button @click="abrirModal" class="floating-btn" v-if="user">
      <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <line x1="12" y1="5" x2="12" y2="19"></line>
        <line x1="5" y1="12" x2="19" y2="12"></line>
      </svg>
    </button> -->



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
                  <option value="🏅 Deporte">🏅 Deporte</option>
                  <option value="🏋️ Gimnasio y Fitness">🏋️ Gimnasio y Fitness</option>
                  <option value="📍 Lugares y Centros">📍 Lugares y Centros</option>
                  <option value="🧠 Consejos y Bienestar">🧠 Consejos y Bienestar</option>
                </select>
              </div>
            </div>

            <div class="form-group">
              <label class="file-upload-label">
                <input type="file" id="imagen" @change="handleFileSelect" accept=".jpg,.jpeg,.png,.gif"
                  class="file-upload-input" />
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

  <!-- Burbuja de Mensajes Flotante -->
  <ChatBubbleComponent v-if="user && !mostrarModal && !postSeleccionado" :user="user" />

  <Alert v-if="openModal" :key="alertKey" :type="alertType" :message="alertMessage" @close="openModal = false" />
</template>

<script>
import axios from 'axios';
import gsap from 'gsap';
import { supabase } from '../../supabaseClient';
import Navbar from '../navbarComponent.vue';
import paginatorComponent from '@/components/paginatorComponent.vue';
import ChatBubbleComponent from '../ChatBubbleComponent.vue';
import Alert from '../Alert.vue';

export default {
  name: 'ForoComponent',
  components: {
    paginatorComponent,
    Navbar,
    ChatBubbleComponent,
    Alert
  },
  data() {
    return {
      openModal: false,
      alertType: 'success', // 'success', 'error', 'alert'
      alertMessage: '',
      alertKey: 0, // Para forzar la re-renderización del componente Alert
      user: null,
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
      comentarioEditando: null,
      replyEditando: '',
      replyEditado: null,
      imageLoaded: {}
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
    },
  },

  methods: {
    // Métodos de UI
    abrirModal() {
      if (!this.user) {

        this.alertType = 'alert';
        this.alertMessage = 'Por favor, inicia sesión para crear un nuevo post.';
        this.alertKey++;
        this.openModal = true;
        return;
      }
      this.mostrarModal = true;
      document.body.style.overflow = 'hidden';
    },

    cerrarModal() {
      this.mostrarModal = false;
      this.nuevoPost = { titulo: '', contenido: '', categoria: '', imagenFile: null };
      this.imagenMiniatura = null;
      this.modoEdicion = false;

      if (!this.postSeleccionado) {
        document.body.style.overflow = 'auto';
      }
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
      try {
        this.scrollPosition = window.pageYOffset || document.documentElement.scrollTop;
        document.body.style.overflow = 'hidden';

        this.postSeleccionado = {
          ...post,
          isLiked: post.isLiked || false,
          likes_count: post.likes_count || 0,
          comments: (post.comments || []).map(comment => ({
            ...comment,
            isLiked: comment.isLiked || false,
            likes: comment.likes || 0,
            replies: (comment.replies || []).map(reply => ({
              ...reply,
              isLiked: reply.isLiked || false,
              likes: reply.likes || 0
            }))
          }))
        };
      } catch (error) {
        console.error('Popup open error:', error);
      }
    },

    cerrarPopout() {
      try {
        document.body.style.overflow = 'auto';
        this.postSeleccionado = null;
        this.comentarioRespondiendo = null;
        this.nuevoComentario = '';
      } catch (error) {
        console.error('Popup close error:', error);
      }
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
      try {
        this.$nextTick(() => {
          const input = this.$refs.comentarioInput;
          if (input && input.focus) {
            input.focus();
          }
        });
      } catch (error) {
        this.handleError(error, 'focusing comment input');
      }
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

      if (!this.user?.id) {

        this.alertType = 'alert';
        this.alertMessage = 'Por favor, inicia sesión para crear un nuevo post.';
        this.alertKey++;
        this.openModal = true;
        return;
      }

      const formData = new FormData();
      formData.append('titulo', this.nuevoPost.titulo);
      formData.append('contenido', this.nuevoPost.contenido);
      formData.append('categoria', this.nuevoPost.categoria);
      formData.append('user_id', this.user.id);

      if (this.nuevoPost.imagenFile) {
        formData.append('imagen', this.nuevoPost.imagenFile);
      }

      try {
        await axios.post('/post', formData, {
          headers: { 'Content-Type': 'multipart/form-data' },
        });
        await this.getPost();
        this.cerrarModal();
      } catch (error) {
        console.error('Error creando post:', error);

        this.alertType = 'error';
        this.alertMessage = 'Error al crear el post. Por favor, inténtalo de nuevo.';
        this.alertKey++;
        this.openModal = true;
      }
    },


    async getPost() {
      try {
        // Configurar parámetros con user_id si está disponible
        const params = {};
        if (this.user && this.user.id) {
          params.user_id = this.user.id;
        }

        const response = await axios.get('/post', { params });

        this.posts = response.data.posts.sort((a, b) => {
          return new Date(b.created_at) - new Date(a.created_at);
        }).map(post => ({
          ...post,
          user: post.user || {},
          isLiked: post.is_liked || false,
          likes_count: post.likes_count || 0,
          comments: (post.comments || []).map(comment => ({
            ...comment,
            user: comment.user || {},
            isLiked: comment.is_liked || false,
            likes: comment.likes_count || 0,
            replies: (comment.replies || []).map(reply => ({
              ...reply,
              user: reply.user || {},
              isLiked: reply.is_liked || false,
              likes: reply.likes_count || 0
            }))
          }))
        }));

        this.postsFiltrados = [...this.posts];
        this.$store.dispatch('cacheSection', { key: 'foro', data: this.posts });
      } catch (error) {
        console.error('Post fetch error:', error);
      }
    },


    handleFileSelect(event) {
      const file = event.target.files[0];
      if (!file) return;

      const allowedTypes = [
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/jpg'
      ];

      if (!allowedTypes.includes(file.type)) {

        this.alertType = 'error';
        this.alertMessage = `Tipo de archivo no válido: ${file.type}. Usa JPEG, PNG o GIF.`;
        this.alertKey++;
        this.openModal = true;

        event.target.value = '';
        return;
      }

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
        if (!this.user) {

          this.alertType = 'alert';
          this.alertMessage = 'Por favor, inicia sesión para comentar.';
          this.alertKey++;
          this.openModal = true;
          return;
        }

        const endpoint = this.comentarioRespondiendo
          ? `/post/create-reply/${this.comentarioRespondiendo}`
          : `/post/create-comment`;

        const payload = {
          texto: this.nuevoComentario,
          post_id: this.postSeleccionado.id,
          user_id: this.user.id
        };

        const response = await axios.post(endpoint, payload);

        if (!response.data || !response.data.success) {
          throw new Error(response.data.message || 'Error desconocido');
        }

        // 1. Primero actualizamos el post en el array principal
        const postIndex = this.posts.findIndex(p => p.id === this.postSeleccionado.id);
        if (postIndex !== -1) {
          if (this.comentarioRespondiendo) {
            const newReply = {
              ...response.data.reply,
              id: response.data.reply.id,
              created_at: new Date().toISOString(),
              likes_count: 0,
              isLiked: false,
              user_id: this.user.id,
              user: {
                id: this.user.id,
                name: this.user.name,
                image: this.user.image
              }
            };

            const parentCommentIndex = this.posts[postIndex].comments.findIndex(
              c => c.id === this.comentarioRespondiendo
            );

            if (parentCommentIndex !== -1) {
              if (!this.posts[postIndex].comments[parentCommentIndex].replies) {
                this.$set(this.posts[postIndex].comments[parentCommentIndex], 'replies', []);
              }
              this.posts[postIndex].comments[parentCommentIndex].replies.unshift(newReply);
            }
          } else {
            const newComment = {
              ...response.data.comment,
              id: response.data.comment.id,
              created_at: new Date().toISOString(),
              likes_count: 0,
              isLiked: false,
              user_id: this.user.id,
              user: {
                id: this.user.id,
                name: this.user.name,
                image: this.user.image
              },
              replies: []
            };
            this.posts[postIndex].comments.unshift(newComment);
          }

          this.postSeleccionado = { ...this.posts[postIndex] };
        }

        this.nuevoComentario = '';
        this.comentarioRespondiendo = null;

      } catch (error) {
        console.error('Error detallado:', error.response?.data || error.message);

        this.alertType = 'error';
        this.alertMessage = `Error al guardar el comentario: ${error.response?.data?.message || error.message}`;
        this.alertKey++;
        this.openModal = true;
      }
    },

    // METODOS PARA LIKES
    async toggleLike(type, id) {
      if (!this.user) {

        this.alertType = 'alert';
        this.alertMessage = 'Por favor, inicia sesión para dar like.';
        this.alertKey++;
        this.openModal = true;
        return;
      }

      try {
        const response = await axios.post('/toggle-like', {
          likeable_type: type,
          likeable_id: id,
          user_id: this.user.id
        });

        // Función para actualizar cualquier elemento
        const updateItem = (item) => {
          if (item.id === id) {
            item.isLiked = response.data.is_liked;
            item.likes_count = response.data.likes_count;
            return true;
          }
          return false;
        };

        // Buscar y actualizar en posts principales
        this.posts.forEach(post => {
          // Actualizar el post mismo
          if (type === 'post' && updateItem(post)) return;

          // Buscar en comentarios
          post.comments?.forEach(comment => {
            // Actualizar comentario
            if (type === 'comment' && updateItem(comment)) return;

            // Buscar en respuestas
            comment.replies?.forEach(reply => {
              // Actualizar respuesta
              if (type === 'reply' && updateItem(reply)) return;
            });
          });
        });

        // Actualizar en post seleccionado si está abierto
        if (this.postSeleccionado) {
          // Actualizar el post seleccionado mismo
          if (type === 'post' && updateItem(this.postSeleccionado)) return;

          // Buscar en comentarios del post seleccionado
          this.postSeleccionado.comments?.forEach(comment => {
            if (type === 'comment' && updateItem(comment)) return;

            comment.replies?.forEach(reply => {
              if (type === 'reply' && updateItem(reply)) return;
            });
          });
        }

      } catch (error) {
        console.error('Error al dar like:', error);
      }
    },



    // METODOS PARA EDITAR Y ELIMINAR EN POST

    isPostAuthor(post) {
      return this.user && post.user_id === this.user.id;
    },

    isCommentAuthor(comment) {
      return this.user && comment.user_id === this.user.id;
    },

    isReplyAuthor(reply) {
      return this.user && reply.user_id === this.user.id;
    },

    // En el método editarPost
    async editarPost() {
      try {
        let response;
        const userId = this.user.id;

        if (this.nuevoPost.imagenFile) {
          const formData = new FormData();
          formData.append('_method', 'PUT');
          formData.append('titulo', this.nuevoPost.titulo);
          formData.append('contenido', this.nuevoPost.contenido);
          formData.append('categoria', this.nuevoPost.categoria);
          formData.append('imagen', this.nuevoPost.imagenFile);
          formData.append('user_id', this.user.id);

          response = await axios.post(`/post/${this.nuevoPost.id}`, formData, {
            headers: { 'Content-Type': 'multipart/form-data' },
          });
        } else {
          response = await axios.put(`/post/${this.nuevoPost.id}`, {
            ...this.nuevoPost,
            user_id: userId
          });
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

        this.alertType = 'error';
        this.alertMessage = error.response?.data?.message || 'Error al editar el post. Por favor, inténtalo de nuevo.';
        this.alertKey++;
        this.openModal = true;
      }
    },

    async confirmarEliminarPost() {
      if (confirm('¿Estás seguro de eliminar este post?')) {
        try {
          const postId = this.postSeleccionado.id;

          const response = await axios.delete(`/post/${postId}`, {
            data: {
              user_id: this.user.id,
              user_type: this.user.user_type
            }
          });

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

          this.alertType = 'error';
          this.alertMessage = error.response?.data?.message || 'Error al eliminar el post. Por favor, inténtalo de nuevo.';
          this.alertKey++;
          this.openModal = true;
        }
      }
    },

    abrirEditarModal(post) {
      this.cerrarPopout();

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

      // Agregar clase para deshabilitar scroll
      document.body.style.overflow = 'hidden';
    },


    // METODOS PARA EDITAR Y ELIMINAR EN COMENTARIOS

    editarComentario(comentario) {
      this.comentarioEditando = comentario.id;
      this.comentarioEditado = comentario.texto;
    },

    editarReply(reply) {
      this.replyEditando = reply.id;
      this.replyEditado = reply.texto;
    },


    guardarEdicionComentario(comentario) {

      if (!this.comentarioEditado.trim()) {

        this.alertType = 'alert';
        this.alertMessage = 'El comentario no puede estar vacío';
        this.alertKey++;
        this.openModal = true;
        return;
      }

      const endpoint = `/post/update-comment/${comentario.id}`;

      axios.put(endpoint, { texto: this.comentarioEditado, user_id: this.user.id })
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

          this.alertType = 'error';
          this.alertMessage = 'Error al guardar cambios: ' + (error.response?.data?.message || error.message);
          this.alertKey++;
          this.openModal = true;
        });


      setTimeout(() => {
        this.comentarioEditando = null;
        this.comentarioEditado = '';
      }, 1500);

    },


    guardarEdicionReply(reply, comentarioID) {

      if (!this.replyEditado.trim()) {

        this.alertType = 'alert';
        this.alertMessage = 'La respuesta no puede estar vacía';
        this.alertKey++;
        this.openModal = true;
        return;
      }

      const endpoint = `/post/update-reply/${reply.id}`


      axios.put(endpoint, { comment_id: comentarioID, texto: this.replyEditado, user_id: this.user.id })
        .then(response => {
          this.getPost()
            .then(() => {
              const postEncontrado = this.posts.find(post => Number(post.id) === Number(this.postSeleccionado.id));
              if (postEncontrado) {
                this.postSeleccionado = { ...postEncontrado };
              }
            })
            .catch(error => {
              console.error('Error al editar respuesta:', error);
            });
        })
        .catch(error => {
          console.error('Error editando respuesta:', error);

          this.alertType = 'error';
          this.alertMessage = 'Error al guardar cambios: ' + (error.response?.data?.message || error.message);
          this.alertKey++;
          this.openModal = true;
        });


      setTimeout(() => {
        this.replyEditando = null;
        this.replyEditado = '';
      }, 1500);

    },



    eliminarComentario(comentario) {
      if (!confirm('¿Eliminar este comentario permanentemente?')) return;

      const endpoint = comentario.parent_id
        ? `/post/destroy-reply/${comentario.id}`
        : `/post/delete-comment/${comentario.id}`;

      axios.delete(endpoint, {
        data: {
          user_id: this.user.id,
          user_type: this.user.user_type
        }
      })
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

          this.alertType = 'error';
          this.alertMessage = 'Error al eliminar el comentario: ' + (error.response?.data?.message || error.message);
          this.alertKey++;
          this.openModal = true;
        });
    },

    cancelarEdicionComentario() {
      this.comentarioEditando = null;
      this.comentarioEditado = '';
    },





    // METODOS PARA EDITAR Y ELIMINAR EN REPLYS

    eliminarReply(reply) {
      if (!confirm('¿Eliminar esta respuesta permanentemente?')) return;

      const endpoint = `/post/destroy-reply/${reply.id}`

      axios.delete(endpoint, {
        data: {
          user_id: this.user.id,
          user_type: this.user.user_type
        }
      })
        .then(() => {
          this.getPost()
            .then(() => {
              const postEncontrado = this.posts.find(post => Number(post.id) === Number(this.postSeleccionado.id));
              if (postEncontrado) {
                this.postSeleccionado = { ...postEncontrado };
              }
            })
            .catch(error => {
              console.error('Error al eliminar la respuesta:', error);
            });
        })
        .catch(error => {
          console.error('Error eliminando respuesta:', error);

          this.alertType = 'error';
          this.alertMessage = 'Error al eliminar la respuesta: ' + (error.response?.data?.message || error.message);
          this.alertKey++;
          this.openModal = true;
        });
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
        '🏅 Deporte': '#007bff',
        '🏋️ Gimnasio y Fitness': '#6f42c1',
        '📍 Lugares y Centros': '#fd7e14',
        '🧠 Consejos y Bienestar': '#20c997'
      };
      return colors[categoria] || '#adb5bd';
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


    getUsernameFromCommentId(commentId) {
      const comment = this.findCommentById(commentId);
      if (comment) {
        return comment.user?.name || `Usuario${comment.user_id}`;
      }
      return '';
    },

    toggleReply(commentId) {
      this.comentarioRespondiendo = this.comentarioRespondiendo === commentId ? null : commentId;
    },

    cancelarRespuesta() {
      this.comentarioRespondiendo = null;
      this.nuevoComentario = '';
    },


    // El backend siempre devuelve una URL de imagen: si el post no tiene una,
    // manda un "no_image.png" de relleno. Eso dejaba un recuadro gris feo en
    // las tarjetas sin foto; aqui lo detectamos para no pintar nada.
    tieneImagen(post) {
      return !!post.imagen && !String(post.imagen).includes('no_image');
    },

    getUserImage(user) {
      if (!user) {
        return '/storage/users/Perfil-Icon.png';
      }

      if (user.image && user.image.startsWith('http')) {
        return user.image;
      }

      if (user.image) {
        return `/storage/users/${user.id}/${user.image}`;
      }

      return '/storage/users/Perfil-Icon.png';
    },


    onImageLoad(postId) {
      this.imageLoaded = {
        ...this.imageLoaded,
        [postId]: true
      };
    },

    handleError(error, context = '') {
      console.error(`Error in ${context}:`, error);
    },



    beforeEnter(el) {
      if (!el) return;
      el.style.opacity = 0;
    },
    enter(el, done) {
      if (!el) return done();
      gsap.to(el, {
        opacity: 1,
        duration: 0.3,
        onComplete: done
      });
    },
    leave(el, done) {
      if (!el) return done();
      gsap.to(el, {
        opacity: 0,
        duration: 0.2,
        onComplete: done
      });
    },


    // --- Realtime (Supabase) ---

    subscribeRealtime() {
      this.realtimeChannel = supabase
        .channel('foro-realtime')
        .on('postgres_changes', { event: '*', schema: 'public', table: 'posts' }, () => {
          this.refetchPostsDebounced();
        })
        .on('postgres_changes', { event: '*', schema: 'public', table: 'likes' }, () => {
          this.refetchPostsDebounced();
        })
        .on('postgres_changes', { event: '*', schema: 'public', table: 'comments' }, () => {
          this.refetchPostsDebounced();
        })
        .on('postgres_changes', { event: '*', schema: 'public', table: 'replies' }, () => {
          this.refetchPostsDebounced();
        })
        .subscribe();
    },

    refetchPostsDebounced() {
      clearTimeout(this._realtimeDebounceTimer);
      this._realtimeDebounceTimer = setTimeout(() => {
        this.getPost();
      }, 300);
    },

  },
  mounted() {
    this.user = JSON.parse(sessionStorage.getItem('user')) || null;

    const cachedPosts = this.$store.getters.sectionCache('foro');
    if (cachedPosts) {
      this.posts = cachedPosts;
      this.postsFiltrados = [...this.posts];
    } else {
      this.getPost();
    }

    this.subscribeRealtime();
  },
  beforeUnmount() {
    document.body.style.overflow = 'auto';
    this.postSeleccionado = null;
    clearTimeout(this._realtimeDebounceTimer);
    if (this.realtimeChannel) {
      supabase.removeChannel(this.realtimeChannel);
    }
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


.login-prompt {
  padding: 1rem;
  text-align: center;
  background: #f8f9fa;
  border-radius: 8px;
  margin-top: 1rem;
  border: 1px solid #eee;
}

.login-prompt a {
  color: #007bff;
  text-decoration: none;
  font-weight: 500;
}

.login-prompt a:hover {
  text-decoration: underline;
}


/* Estilos para likes */
.post-likes,
.comment-action.like-comment {
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  gap: 4px;
}

.post-likes:hover,
.comment-action.like-comment:hover {
  opacity: 0.8;
}

.post-likes.liked,
.comment-action.like-comment.liked {
  color: #e0245e;
}

.post-likes.liked svg,
.comment-action.like-comment.liked svg {
  stroke: #e0245e;
  fill: #e0245e;
}

/* Corazón sólido cuando está activo */
.post-likes.liked svg path,
.comment-action.like-comment.liked svg path {
  fill: #e0245e;
}

/* Corazón vacío cuando no está activo */
.post-likes:not(.liked) svg path,
.comment-action.like-comment:not(.liked) svg path {
  fill: none;
}

.post-likes svg,
.comment-action.like-comment svg {
  stroke-width: 2;
}

/* Mostrar borde siempre */
.post-likes svg path,
.comment-action.like-comment svg path {
  stroke: currentColor;
  fill: none;
}

/* Rellenar cuando está activo */
.post-likes.liked svg path,
.comment-action.like-comment.liked svg path {
  fill: #e0245e;
  stroke: #e0245e;
}

/* Animación al dar like */
@keyframes heartBeat {
  0% {
    transform: scale(1);
  }

  25% {
    transform: scale(1.2);
  }

  50% {
    transform: scale(0.95);
  }

  75% {
    transform: scale(1.1);
  }

  100% {
    transform: scale(1);
  }
}

.liked {
  animation: heartBeat 0.5s ease;
}


/* Asegurar que los corazones sean visibles siempre */
.post-likes svg path,
.comment-action.like-comment svg path {
  stroke: currentColor;
  /* Contorno visible */
  stroke-width: 1.5;
  /* Grosor del contorno */
  fill: none;
  /* Sin relleno por defecto */
}

/* Rellenar cuando está activo */
.post-likes.liked svg path,
.comment-action.like-comment.liked svg path {
  fill: #e0245e;
  /* Relleno rojo cuando está activo */
  stroke: #e0245e;
  /* Contorno rojo cuando está activo */
}





/* Estilos para avatares */
.post-author-info {
  display: flex;
  align-items: flex-start;
  gap: 12px;
  margin-bottom: 15px;
}

.post-author-avatar,
.author-avatar,
.comment-avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  object-fit: cover;
  border: 2px solid #fff;
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}

.comment-avatar-wrapper {
  display: flex;
  align-items: flex-start;
}

.comment-avatar {
  width: 32px;
  height: 32px;
  margin-right: 10px;
}

/* Ajustes para el popup */
.author-avatar {
  width: 50px;
  height: 50px;
}

/* En modo responsive */
@media (max-width: 768px) {

  .post-author-avatar,
  .author-avatar {
    width: 36px;
    height: 36px;
  }

  .comment-avatar {
    width: 28px;
    height: 28px;
  }
}


.modal-overlay {
  z-index: 1001 !important;
}

.post-popout-overlay {
  z-index: 1000;
}
</style>