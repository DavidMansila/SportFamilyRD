<template>
    <div class="min-h-screen bg-gray-100 flex">
      <!-- Sidebar -->
      <div class="sidebar bg-blue-800 text-white w-64 p-6 flex flex-col justify-between">
        <div>
          <div class="logo-section flex items-center gap-3 mb-8">
            <h1 class="logo-text text-2xl font-bold">Ajustes</h1>
          </div>
          <div class="menu">
            <ul>
              <li class="menu-item" @click="cambiarVista('perfil')">
                <a href="#" class="menu-link">Perfil</a>
              </li>
              <li class="menu-item" @click="cambiarVista('seguridad')">
                <a href="#" class="menu-link">Seguridad</a>
              </li>
              <li class="menu-item" @click="cambiarVista('notificaciones')">
                <a href="#" class="menu-link">Notificaciones</a>
              </li>
              <li class="menu-item" @click="cambiarVista('idioma')">
                <a href="#" class="menu-link">Idioma</a>
              </li>
              <li class="menu-item" @click="cambiarVista('privacidad')">
                <a href="#" class="menu-link">Privacidad</a>
              </li>
              <a href="/" class="menu-link1">SportFamilyRD</a>
            </ul>
          </div>
        </div>
        <div class="footer text-center mt-8 text-sm">
          <p>&copy; 2025 SportFamilyRD - Todos los derechos reservados</p>
        </div>
      </div>
  
      <!-- Main Content -->
      <div class="content flex-1 p-8 flex justify-center items-start">
        <div class="settings-card bg-white p-8 rounded-lg shadow-lg w-full max-w-4xl">
          <h1 class="page-title text-3xl font-semibold text-gray-800 mb-8">Configuracion</h1>
  

          <!-- Perfil -->
          <div v-if="vista === 'perfil'">
            <form @submit.prevent="guardarPerfil">
              <div class="form-group mb-6">
                <label for="email" class="block text-lg">Email</label>
                <input v-model="email" type="email" id="email" class="input" placeholder="Email"/>
              </div>
              <div class="form-group mb-6">
                <label for="usuario" class="block text-lg">Nombre de Usuario</label>
                <input v-model="usuario" type="text" id="usuario" class="input" placeholder="Nombre de usuario"/>
              </div>
              <div class="form-group mb-6">
                <label class="block text-lg">Imagen de Perfil</label>
                <input type="file" @change="subirImagen" class="input"/>
              </div>
              <button type="submit" class="btn primary">Guardar Perfil</button>
            </form>
          </div>

  
          <!-- Seguridad -->
          <div v-if="vista === 'seguridad'">
            <form @submit.prevent="guardarSeguridad">
              <div class="form-group mb-6">
                <label for="password" class="block text-lg">Nueva Contraseña</label>
                <input v-model="password" type="password" id="password" class="input" placeholder="Introduce nueva contraseña"/>
              </div>
              <div class="form-group mb-6">
                <label for="confirm-password" class="block text-lg">Confirmar Contraseña</label>
                <input v-model="confirmPassword" type="password" id="confirm-password" class="input" placeholder="Confirma tu contraseña"/>
              </div>
              <button type="submit" class="btn primary">Guardar Seguridad</button>
            </form>
          </div>


  
          <!-- Notificaciones -->
          <div v-if="vista === 'notificaciones'">
            <div class="form-group flex justify-between mb-6">
              <label class="text-lg">Recibir Notificaciones</label>
              <input type="checkbox" v-model="notificaciones" class="checkbox"/>
            </div>
            <div class="form-group flex justify-between mb-6">
              <label class="text-lg">Notificaciones por Email</label>
              <input type="checkbox" v-model="emailNotificaciones" class="checkbox"/>
            </div>
            <button type="button" @click="guardarNotificaciones" class="btn primary">Guardar Notificaciones</button>
          </div>
  


          <!-- Idioma -->
          <div v-if="vista === 'idioma'">
            <form @submit.prevent="guardarIdioma">
              <div class="form-group mb-6">
                <label class="block text-lg">Idioma Preferido</label>
                <select v-model="idioma" class="input">
                  <option value="es">Español</option>
                  <option value="en">Inglés</option>
                  <option value="fr">Francés</option>
                </select>
              </div>
              <button type="submit" class="btn primary">Guardar Idioma</button>
            </form>
          </div>
  

          
          <!-- Privacidad -->
          <div v-if="vista === 'privacidad'">
            <div class="form-group flex items-center gap-2 mb-6">
              <input type="checkbox" v-model="privacidad" class="checkbox"/>
              <span>Hacer mi perfil privado</span>
            </div>
            <div class="form-group flex items-center gap-2 mb-6">
              <input type="checkbox" v-model="compartirDatos" class="checkbox"/>
              <span>Permitir compartir mis datos con terceros</span>
            </div>
            <button type="button" @click="guardarPrivacidad" class="btn primary">Guardar Privacidad</button>
          </div>
        </div>
      </div>

      
    </div>
  </template>
  
  <script>
  export default {
    name: 'AjustesView',
    data() {
      return {
        vista: 'perfil',
        nombre: '',
        usuario: '',
        password: '',
        confirmPassword: '',
        notificaciones: true,
        emailNotificaciones: false,
        idioma: 'es',
        privacidad: false,
        compartirDatos: false
      };
    },
    methods: {
      cambiarVista(vista) {
        this.vista = vista;
      },
      guardarPerfil() {
        alert('Perfil guardado');
      },
      subirImagen(event) {
        alert('Imagen subida: ' + event.target.files[0].name);
      },
      guardarSeguridad() {
        if (this.password !== this.confirmPassword) {
          alert('Las contraseñas no coinciden');
        } else {
          alert('Seguridad guardada');
        }
      },
      guardarNotificaciones() {
        alert('Notificaciones guardadas');
      },
      guardarIdioma() {
        alert('Idioma guardado');
      },
      guardarPrivacidad() {
        alert('Privacidad guardada');
      }
    }
  };
  </script>
  
  
  <style scoped>
  /* Sidebar Styles */
  .sidebar {
  background: linear-gradient(to right, #9e9e9e, #686868);
  color: white;
  width: 250px;
  padding: 2rem;
  height: 100vh;
  position: fixed;
  left: 0;
  top: 0;
  bottom: 0;
}
  
  .logo-text {
    font-size: 1.8rem;
    font-weight: bold;
  }
  
  .menu {
    margin-top: 2rem;
  }
  
  .menu-item {
    margin-bottom: 1.2rem;
  }
  
  .menu-link {
    color: white;
    font-size: 1.2rem;
    font-weight: 500;
    text-decoration: none;
    transition: 0.3s;
  }

  .menu-link1 {
    color: rgb(0, 0, 0);
    font-size: 1.2rem;
    font-weight: 500;
    text-decoration: none;
    transition: 0.3s;
    text-shadow: #1F2937;
  }

  .menu-link1:hover {
    color: #ff0000;
  }
  
  .menu-link:hover {
    color: #FBBF24;
  }
  
  /* Content Styles */
  .content {
  display: flex;
  justify-content: start; /* Asegura que el contenido inicie justo después del sidebar */
  align-items: center; /* Centra verticalmente */
  flex-grow: 1;
  padding-left: 280px; /* Ajusta según el ancho del sidebar */
  width: 100%;
  height: 100vh; /* Asegura que ocupe toda la pantalla */
}
  
  .page-title {
    color: #000000;
    font-size: 2.5rem;
    font-weight: 600;
    margin-bottom: 2rem;
    
  }
  
  .settings-card {
  background-color: white;
  padding: 2rem;
  border-radius: 1rem;
  box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
  width: 100%;
  max-width: 600px; /* Ajusta el tamaño según necesites */
  margin-left: auto; /* Empuja la tarjeta hacia la derecha */
  margin-right: auto; /* Mantiene centrado si hay espacio */
}
  
  .form-group {
    margin-bottom: 1.5rem;
  }
  
  .input {
    width: 100%;
    padding: 0.8rem;
    border: 1px solid #ddd;
    border-radius: 0.5rem;
    font-size: 1rem;
  }
  
  .switch {
    position: relative;
    display: inline-block;
    width: 40px;
    height: 24px;
  }
  
  .switch .slider {
    position: absolute;
    cursor: pointer;
    background: #ccc;
    border-radius: 20px;
    transition: 0.4s;
    width: 100%;
    height: 100%;
  }
  
  .switch input:checked + .slider {
    background: #28a745;
  }
  
  .checkbox {
    width: auto;
    margin-right: 0.5rem;
  }
  
  /* Button Styles */
  .btn {
    padding: 0.7rem 1.5rem;
    font-weight: bold;
    border-radius: 0.5rem;
    cursor: pointer;
    transition: 0.3s;
  }
  
  .primary {
    background-color: #007bff;
    color: white;
  }
  
  .primary:hover {
    background-color: #0056b3;
  }
  
  /* Footer */
  .footer {
    margin-top: 2rem;
    color: #ccc;
    font-size: 0.9rem;
  }
  </style>
  