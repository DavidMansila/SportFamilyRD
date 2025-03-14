<template>
    <div class="post-detail-page">
      <!-- Botón para volver al foro -->
      <router-link to="/foro" class="btn-volver">← Volver al foro</router-link>
  
      <!-- Contenido del post -->
      <div class="post-content">
        <h1 class="post-titulo">{{ post.titulo }}</h1>
        <p class="post-fecha">Publicado el {{ formatFecha(post.fecha) }}</p>
        <p class="post-contenido">{{ post.contenido }}</p>
      </div>
  
      <!-- Sección de comentarios -->
      <div class="comentarios-section">
        <h2>Comentarios</h2>
  
        <!-- Formulario para añadir un nuevo comentario -->
        <div class="nuevo-comentario">
          <textarea
            v-model="nuevoComentario"
            placeholder="Escribe tu comentario..."
            :class="{ 'error': errorComentario }"
          ></textarea>
          <small v-if="errorComentario" class="error-message">El comentario no puede estar vacío.</small>
          <button @click="agregarComentario" class="btn-comentar">Comentar</button>
        </div>
  
        <!-- Lista de comentarios -->
        <div class="comentarios-list">
          <div
            v-for="(comentario, index) in comentarios"
            :key="comentario.id"
            class="comentario"
          >
            <div class="comentario-header">
              <span class="comentario-autor">Usuario Anónimo</span>
              <span class="comentario-fecha">{{ formatFecha(comentario.fecha) }}</span>
            </div>
            <p class="comentario-texto">{{ comentario.texto }}</p>
            <button @click="responderAComentario(index)" class="btn-responder">Responder</button>
  
            <!-- Respuestas a comentarios -->
            <div v-if="comentario.respuestas && comentario.respuestas.length" class="respuestas">
              <div
                v-for="(respuesta, i) in comentario.respuestas"
                :key="i"
                class="respuesta"
              >
                <div class="respuesta-header">
                  <span class="respuesta-autor">Usuario Anónimo</span>
                  <span class="respuesta-fecha">{{ formatFecha(respuesta.fecha) }}</span>
                </div>
                <p class="respuesta-texto">{{ respuesta.texto }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </template>
  
  <script>
  import axios from 'axios';
  
  export default {
    name: 'PostDetail',
    props: ['id'],
    data() {
      return {
        post: {},
        nuevoComentario: '',
        comentarios: [],
        errorComentario: false,
      };
    },
    created() {
      this.cargarPost();
    },
    methods: {
      async cargarPost() {
        try {
          const response = await axios.get(`/api/posts/${this.id}`);
          this.post = response.data;
          this.comentarios = this.post.comments || [];
        } catch (error) {
          console.error('Error al cargar el post:', error);
        }
      },
      async agregarComentario() {
        if (!this.nuevoComentario.trim()) {
          this.errorComentario = true;
          return;
        }
        this.errorComentario = false;
  
        try {
          const response = await axios.post('/api/comments', {
            post_id: this.id,
            texto: this.nuevoComentario,
          });
          this.comentarios.push({ ...response.data, respuestas: [] });
          this.nuevoComentario = '';
        } catch (error) {
          console.error('Error al agregar el comentario:', error);
        }
      },
      async responderAComentario(index) {
        const respuesta = prompt('Escribe tu respuesta:');
        if (!respuesta) return;
  
        try {
          const response = await axios.post('/api/replies', {
            comment_id: this.comentarios[index].id,
            texto: respuesta,
          });
          if (!this.comentarios[index].respuestas) {
            this.comentarios[index].respuestas = [];
          }
          this.comentarios[index].respuestas.push(response.data);
        } catch (error) {
          console.error('Error al responder al comentario:', error);
        }
      },
      formatFecha(fecha) {
        return new Date(fecha).toLocaleDateString('es-ES', {
          year: 'numeric',
          month: 'long',
          day: 'numeric',
          hour: '2-digit',
          minute: '2-digit',
        });
      },
    },
  };
  </script>
  
  <style scoped>
  .post-detail-page {
    max-width: 800px;
    margin: 0 auto;
    padding: 20px;
  }
  
  .btn-volver {
    display: inline-block;
    margin-bottom: 20px;
    color: #007bff;
    text-decoration: none;
    font-weight: bold;
    transition: color 0.3s ease;
  }
  
  .btn-volver:hover {
    color: #0056b3;
  }
  
  .post-content {
    background-color: #fff;
    padding: 25px;
    border-radius: 10px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    margin-bottom: 30px;
  }
  
  .post-titulo {
    font-size: 2rem;
    color: #333;
    margin-bottom: 10px;
  }
  
  .post-fecha {
    font-size: 0.9rem;
    color: #777;
    margin-bottom: 20px;
  }
  
  .post-contenido {
    font-size: 1.1rem;
    color: #555;
    line-height: 1.6;
  }
  
  .comentarios-section {
    margin-top: 40px;
  }
  
  .nuevo-comentario textarea {
    width: 100%;
    height: 100px;
    padding: 10px;
    border-radius: 5px;
    border: 1px solid #ddd;
    margin-bottom: 10px;
    font-family: inherit;
    font-size: 1rem;
    resize: vertical;
  }
  
  .nuevo-comentario textarea.error {
    border-color: #ff4d4d;
  }
  
  .error-message {
    color: #ff4d4d;
    font-size: 0.9rem;
    margin-bottom: 10px;
    display: block;
  }
  
  .btn-comentar {
    background-color: #28a745;
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 5px;
    cursor: pointer;
    font-size: 1rem;
    transition: background-color 0.3s ease;
  }
  
  .btn-comentar:hover {
    background-color: #218838;
  }
  
  .comentarios-list {
    margin-top: 20px;
  }
  
  .comentario {
    background-color: #f9f9f9;
    padding: 20px;
    border-radius: 10px;
    margin-bottom: 15px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  }
  
  .comentario-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
  }
  
  .comentario-autor {
    font-weight: bold;
    color: #333;
  }
  
  .comentario-fecha {
    font-size: 0.9rem;
    color: #777;
  }
  
  .comentario-texto {
    font-size: 1rem;
    color: #555;
    line-height: 1.6;
  }
  
  .btn-responder {
    background-color: #007bff;
    color: white;
    border: none;
    padding: 5px 10px;
    border-radius: 3px;
    cursor: pointer;
    margin-top: 10px;
    font-size: 0.9rem;
    transition: background-color 0.3s ease;
  }
  
  .btn-responder:hover {
    background-color: #0056b3;
  }
  
  .respuestas {
    margin-left: 20px;
    margin-top: 10px;
  }
  
  .respuesta {
    background-color: #e9ecef;
    padding: 15px;
    border-radius: 5px;
    margin-bottom: 10px;
  }
  
  .respuesta-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
  }
  
  .respuesta-autor {
    font-weight: bold;
    color: #333;
  }
  
  .respuesta-fecha {
    font-size: 0.9rem;
    color: #777;
  }
  
  .respuesta-texto {
    font-size: 0.9rem;
    color: #555;
    line-height: 1.6;
  }
  </style>