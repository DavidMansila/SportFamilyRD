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
        <input type="text" placeholder="Buscar en el foro..." class="search-input">
        <button class="search-btn">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="11" cy="11" r="8"></circle>
            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
          </svg>
        </button>
      </div>
      <div class="filtros-categorias">
        <button class="filtro-btn active">Todos</button>
        <button class="filtro-btn">Deporte</button>
        <button class="filtro-btn">Gym</button>
        <button class="filtro-btn">Experiencia</button>
        <button class="filtro-btn">Lugares</button>
      </div>
    </div>


    
    <!-- Sección de Posts con diseño de tarjetas -->
    <div class="posts-grid">

      <div  v-if=" posts.length === 0">
        <h2 class="no-posts">No hay publicaciones disponibles</h2>
        <p class="no-posts-subtitle">¡Sé el primero en iniciar una publicacione!</p>
        <button @click="abrirModal" class="btn-crear-post no-posts-btn">Crear nuevo post</button>
        <img src="/imagenes/no-news.png" alt="No hay publicaciones" class="no-posts-image">
      </div>  

      <div 
        v-else-if="posts.length > 0"
        v-for="(post, index) in posts" 
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
              <!-- <img src="/imagenes/avatar-default.png" alt="Autor" class="author-avatar"> -->
              Usuario{{ post.id }}
            </span>
            <span class="post-date">{{ post.created_at }}</span>
          </div>
        </div>

        <div class="post-imagen">
          <img :src="post.imagen" alt="Imagen del post" class="post-image">
        </div>
        
        <p class="post-contenido">{{ post.contenido.substring(0, 150) }}...</p>
        
        <div class="post-footer">
          <div class="post-stats">
            <span class="post-likes">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M14 9V5a3 3 0 0 0-3-3l-4 9v11h11.28a2 2 0 0 0 2-1.7l1.38-9a2 2 0 0 0-2-2.3zM7 22H4a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2h3"></path>
              </svg>
              {{ post.likes }}
            </span>
            <span class="post-comments">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
              </svg>
              {{ post.comentarios }}
            </span>
          </div>
          <a href="/Publicacion" class="btn-leer">Ver discusión</a>
        </div>
      </div>
    </div>

    <!-- todo aqui esta el vif v-if="usuario.tipo == 'Admin'" -->
    <!-- Botón flotante para crear post -->
    <button  @click="abrirModal" class="floating-btn">
      <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <line x1="12" y1="5" x2="12" y2="19"></line>
        <line x1="5" y1="12" x2="19" y2="12"></line>
      </svg>
    </button>





    <!-- Modal para crear post -->
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
          <!-- Mostrar previsualización de la imagen -->
              <img v-if="imagenMiniatura" :src="imagenMiniatura" alt="Previsualización" class="image-preview">
         </div>



          <div class="form-actions">
            <button type="button" @click="cerrarModal" class="btn btn-secondary">Cancelar</button>
            <button type="submit" class="btn btn-primary">Publicar</button>
          </div>
        </form>
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
      mostrarModal: false,
      nuevoPost: {
        titulo: '',
        contenido: '',
        categoria: '',
        imagenFile: null,  // Para el archivo de imagen
      },
      imagenMiniatura: null // Para la previsualización
    };
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
      
      // Validar tipo de archivo
      if (!file.type.match('image.*')) {
        alert('Por favor, selecciona solo imágenes (JPEG, PNG, etc.)');
        return;
      }
      
      // Validar tamaño (ejemplo: máximo 2MB)
      if (file.size > 2 * 1024 * 1024) {
        alert('La imagen es demasiado grande. Máximo 2MB permitido.');
        return;
      }
      
      // Asignar el archivo para enviarlo al servidor
      this.nuevoPost.imagenFile = file;
      
      // Crear URL temporal para previsualización
      const reader = new FileReader();
      reader.onload = (e) => {
        this.imagenMiniatura = e.target.result;
      };
      reader.readAsDataURL(file);
    },

        loadImg(file){
            let reader = new FileReader();
            reader.onload = (e) =>{
                this.imagenMiniatura = e.target.result;
            }
            reader.readAsDataURL(file);
        },

    getPost(){
      // axios.get('/foro/example')
      axios.get('/post')
        .then(response => {
          this.posts = response.data.posts;
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
        axios.post('/post', formData, header);
        
        this.getPost();
        this.limpiarFormulario();
        this.cerrarModal();
        
      } catch (error) {
        console.error('Error al crear el post:', error);
        alert('Error al crear el post. Por favor, intenta nuevamente.');
      }
    },
},

mounted() {
  this.getPost();
},
}
</script>



<style scoped>
@import '../../../scss/Foro/foro.scss';
</style>
