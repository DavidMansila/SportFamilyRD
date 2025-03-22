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

            <a class="Perfil">
                <img src="/imagenes/Perfil-Icon.png" alt="Perfil" class="perfil-icon"/>
            </a>

            <a :href=" login ? '/Login' : '/Logout' " class="Logout">
                <img src="/imagenes/Logout-Icon.png" alt="Logout" class="logout-icon"/>
            </a>

        </div>
      </nav>

      

    <!-- Título del Foro -->
    <h1 class="titulo-foro">Foro de Discusión</h1>

    <!-- Sección de Posts -->
    <div class="posts-container">
      <div
        v-for="post in posts"
        :key="post.id"
        class="post-card"
      >
        <div class="post-header">
          <h3 class="post-titulo">{{ post.titulo }}</h3>
          <span class="post-date">Publicado el {{ post.fecha }}</span>
        </div>
        <p class="post-contenido">{{ post.contenido }}</p>
        <div class="post-footer">
          <a href="/Publicacion" class="btn-leer">Leer más</a>
          <div class="post-stats">
            <span class="post-likes">👍 {{ post.likes }}</span>
            <span class="post-comments">💬 {{ post.comentarios }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Botón de Crear Nuevo Post -->
    <div class="crear-post-container">
      <button @click="abrirModal" class="btn-crear-post">Crear un nuevo post</button>
    </div>
  </div>

    <!-- Modal para crear un nuevo post -->
    <div v-if="mostrarModal" class="modal">
      <div class="modal-contenido">
        <!-- Encabezado del modal -->
        <div class="modal-header">
          <h2>Crear un nuevo post</h2>
          <button @click="cerrarModal" class="modal-cerrar-btn">×</button>
        </div>

        <!-- Formulario de creación de post -->
        <form @submit.prevent="guardarPost" class="modal-formulario">
          <!-- Campo: Título -->
          <div class="form-group">
            <label for="titulo">Título</label>
            <input
              v-model="nuevoPost.titulo"
              id="titulo"
              type="text"
              placeholder="Escribe un título"
              required
            />
          </div>

          <!-- Campo: Contenido -->
          <div class="form-group">
            <label for="contenido">Contenido</label>
            <textarea
              v-model="nuevoPost.contenido"
              id="contenido"
              placeholder="Escribe el contenido de tu post"
              required
            ></textarea>
          </div>

          <!-- Campo: Categoría -->
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

          <!-- Campo: Etiquetas -->
          <div class="form-group">
            <label for="etiquetas">Etiquetas</label>
            <input
              v-model="nuevoPost.etiquetas"
              id="etiquetas"
              type="text"
              placeholder="Escribe etiquetas separadas por comas"
            />
          </div>

          <!-- Campo: Imagen -->
          <div class="form-group">
            <label for="imagen">Subir imagen</label>
            <input
              type="file"
              id="imagen"
              @change="manejarSubidaImagen"
              accept="image/*"
            />
          </div>

          <!-- Botones del formulario -->
          <div class="form-botones">
            <button type="submit" class="btn-guardar">Guardar</button>
            <button type="button" @click="cerrarModal" class="btn-cancelar">Cancelar</button>
          </div>
        </form>
      </div>
    </div>


</template>

<script>
export default {
  name: 'ForoComponent',
  data() {
    return {
      posts: [
        { id: 1, titulo: '¿Quién es tu jugador favorito?', contenido: 'Hablemos de nuestros jugadores favoritos en el fútbol.', fecha: '12/10/2023', likes: 15, comentarios: 8 },
        { id: 2, titulo: 'Consejos para correr más rápido', contenido: '¿Qué ejercicios te ayudan a correr más rápido?', fecha: '10/10/2023', likes: 22, comentarios: 12 },
        { id: 3, titulo: 'Mejores trucos para jugar baloncesto', contenido: 'Comparte tus mejores trucos y técnicas para el baloncesto.', fecha: '08/10/2023', likes: 30, comentarios: 18 },
        { id: 4, titulo: '¿Qué opinas de la inteligencia artificial en el deporte?', contenido: 'Abre un debate sobre el futuro de la IA en los deportes.', fecha: '05/10/2023', likes: 45, comentarios: 25 },
      ],
      mostrarModal: false,
      nuevoPost: {
        titulo: '',
        contenido: '',
        categoria: '',
        etiquetas: '',
        imagen: null,
    },
  };
},
methods: {
    // Abrir el modal
    abrirModal() {
      this.mostrarModal = true;
    },
    // Cerrar el modal
    cerrarModal() {
      this.mostrarModal = false;
      this.limpiarFormulario();
    },
    // Guardar el post
    guardarPost() {
      const nuevoId = this.posts.length + 1;
      this.posts.unshift({
        id: nuevoId,
        titulo: this.nuevoPost.titulo,
        contenido: this.nuevoPost.contenido,
        categoria: this.nuevoPost.categoria,
        etiquetas: this.nuevoPost.etiquetas.split(',').map(tag => tag.trim()),
        imagen: this.nuevoPost.imagen,
        fecha: new Date().toLocaleDateString(),
        likes: 0,
        comentarios: 0,
      });
      this.cerrarModal();
    },
    // Limpiar el formulario
    limpiarFormulario() {
      this.nuevoPost = {
        titulo: '',
        contenido: '',
        categoria: '',
        etiquetas: '',
        imagen: null,
      };
    },
    // Manejar la subida de imágenes
    manejarSubidaImagen(event) {
      const file = event.target.files[0];
      if (file) {
        this.nuevoPost.imagen = URL.createObjectURL(file);
      }
    },
  },
};
</script>


<style scoped>
@import '../../../scss/Foro/foro.scss';
</style>