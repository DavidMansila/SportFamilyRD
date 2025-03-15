<template>
    <div class="solicitud-view">
      <!-- Navbar (se mantiene igual) -->
      <nav class="navbar">
        <!-- ... (código del navbar) ... -->
      </nav>
  
      <!-- Contenido principal -->
      <div class="container">
        <h2 class="page-title">Solicitud para ser Entrenador</h2>
  
        <!-- Formulario de solicitud -->
        <form @submit.prevent="enviarSolicitud" class="solicitud-form">
          <!-- Campo para el nombre -->
          <div class="form-group">
            <label for="nombre">Nombre completo:</label>
            <input
              type="text"
              id="nombre"
              v-model="formulario.nombre"
              placeholder="Ingresa tu nombre completo"
              required
            />
          </div>
  
          <!-- Campo para el correo electrónico -->
          <div class="form-group">
            <label for="email">Correo electrónico:</label>
            <input
              type="email"
              id="email"
              v-model="formulario.email"
              placeholder="Ingresa tu correo electrónico"
              required
            />
          </div>
  
          <!-- Campo para el deporte -->
          <div class="form-group">
            <label for="deporte">Deporte que entrenas:</label>
            <select id="deporte" v-model="formulario.deporte" required>
              <option value="">Selecciona un deporte</option>
              <option value="Fútbol">Fútbol</option>
              <option value="Baloncesto">Baloncesto</option>
              <option value="Tenis">Tenis</option>
              <option value="Natación">Natación</option>
              <option value="Ciclismo">Ciclismo</option>
              <option value="Atletismo">Atletismo</option>
              <option value="Artes Marciales">Artes Marciales</option>
            </select>
          </div>
  
          <!-- Campo para la experiencia -->
          <div class="form-group">
            <label for="experiencia">Años de experiencia:</label>
            <input
              type="number"
              id="experiencia"
              v-model="formulario.experiencia"
              placeholder="Ingresa tus años de experiencia"
              required
            />
          </div>
  
          <!-- Campo para subir certificado -->
          <div class="form-group">
            <label for="certificado">Subir certificado (opcional):</label>
            <input
              type="file"
              id="certificado"
              @change="subirCertificado"
              accept=".pdf,.jpg,.png"
            />
          </div>
  
          <!-- Campo para subir pruebas adicionales -->
          <div class="form-group">
            <label for="pruebas">Subir pruebas adicionales (opcional):</label>
            <input
              type="file"
              id="pruebas"
              @change="subirPruebas"
              accept=".pdf,.jpg,.png"
            />
          </div>
  
          <!-- Botones de acción -->
          <div class="form-buttons">
            <a href="/Entrenadores" class="btn btn-secondary">Volver atrás</a>
            <button type="submit" class="btn btn-primary">Enviar Solicitud</button>
          </div>
        </form>
      </div>
    </div>
  </template>
  
  <script>
  export default {
    name: 'SolicitudView',
    data() {
      return {
        formulario: {
          nombre: '',
          email: '',
          deporte: '',
          experiencia: '',
          certificado: null,
          pruebas: null,
        },
      };
    },
    methods: {
      // Método para subir el certificado
      subirCertificado(event) {
        this.formulario.certificado = event.target.files[0];
      },
  
      // Método para subir pruebas adicionales
      subirPruebas(event) {
        this.formulario.pruebas = event.target.files[0];
      },
  
      // Método para enviar la solicitud
      enviarSolicitud() {
        // Aquí puedes agregar la lógica para enviar los datos al backend
        console.log('Formulario enviado:', this.formulario);
  
        // Simulación de envío exitoso
        alert('Solicitud enviada con éxito. Nos pondremos en contacto contigo pronto.');
        this.limpiarFormulario();
      },
  
      // Método para limpiar el formulario después de enviar
      limpiarFormulario() {
        this.formulario = {
          nombre: '',
          email: '',
          deporte: '',
          experiencia: '',
          certificado: null,
          pruebas: null,
        };
      },
  
      // Método para volver atrás
      volverAtras() {
        this.$router.go(); // Navega a la página anterior
      },
    },
  };
  </script>
  

  <style scoped>
  @import '../../../scss/Entrenadores/solicitud.scss';
</style>

