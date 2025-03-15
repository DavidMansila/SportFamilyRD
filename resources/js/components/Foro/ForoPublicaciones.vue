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
  @import '../../../scss/Foro/foropublicaciones.scss';
  </style>