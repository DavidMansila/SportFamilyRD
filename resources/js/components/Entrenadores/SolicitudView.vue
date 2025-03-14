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
  /* Estilos generales */
  .container {
    max-width: 800px;
    margin: 0 auto;
    padding: 20px;
  }
  
  .page-title {
    font-size: 2.5rem;
    color: #333;
    margin-bottom: 40px;
    text-align: center;
    padding-top: 30px;
  }
  
  /* Estilos del formulario */
  .solicitud-form {
    background: white;
    border-radius: 12px;
    padding: 30px;
    box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
  }
  
  .form-group {
    margin-bottom: 20px;
  }
  
  .form-group label {
    display: block;
    font-size: 1.1rem;
    color: #333;
    margin-bottom: 8px;
  }
  
  .form-group input,
  .form-group select {
    width: 100%;
    padding: 10px;
    font-size: 1rem;
    border: 1px solid #ccc;
    border-radius: 8px;
    transition: border-color 0.3s ease-in-out;
  }
  
  .form-group input:focus,
  .form-group select:focus {
    border-color: #007bff;
    outline: none;
  }
  
  .form-group input[type="file"] {
    padding: 5px;
  }
  
  /* Estilos de los botones */
  .form-buttons {
    display: flex;
    justify-content: space-between;
    margin-top: 30px;
  }
  
  .btn {
    padding: 12px 30px;
    font-size: 1rem;
    border: none;
    border-radius: 25px;
    cursor: pointer;
    transition: all 0.3s ease-in-out;
  }
  
  .btn-primary {
    background-color: #007bff;
    color: white;
  }
  
  .btn-primary:hover {
    background-color: #0056b3;
  }
  
  .btn-secondary {
    background-color: #6c757d;
    color: white;
  }
  
  .btn-secondary:hover {
    background-color: #5a6268;
  }
  
  /* Responsive Design */
  @media (max-width: 768px) {
    .page-title {
      font-size: 2rem;
    }
  
    .solicitud-form {
      padding: 20px;
    }
  
    .form-buttons {
      flex-direction: column;
      gap: 10px;
    }
  
    .btn {
      width: 100%;
    }
  }
  </style>