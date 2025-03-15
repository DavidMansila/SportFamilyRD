<template>
    <div class="min-h-screen bg-gray-50 flex">
      <!-- Sidebar -->
      <div class="sidebar bg-gradient-to-r from-blue-700 to-blue-500 text-white w-64 p-6 flex flex-col justify-between">
        <div>
          <div class="logo-section flex items-center gap-3 mb-8">
            <h1 class="logo-text text-2xl font-extrabold">Ajustes</h1>
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
        <div class="settings-card bg-white p-8 rounded-2xl shadow-lg w-full max-w-4xl">
          <h2 class="page-title text-3xl font-semibold text-gray-800 mb-8">Configuración</h2>
  
          <!-- Perfil -->
          <div v-if="vista === 'perfil'">
            <form @submit.prevent="guardarPerfil">
              <div class="form-group mb-6">
                <label for="email" class="block text-lg font-medium">Email</label>
                <input v-model="email" type="email" id="email" class="input" placeholder="Email"/>
              </div>
              <div class="form-group mb-6">
                <label for="usuario" class="block text-lg font-medium">Nombre de Usuario</label>
                <input v-model="usuario" type="text" id="usuario" class="input" placeholder="Nombre de usuario"/>
              </div>
              <div class="form-group mb-6">
                <label class="block text-lg font-medium">Imagen de Perfil</label>
                <input type="file" @change="subirImagen" class="input"/>
              </div>
              <button type="submit" class="btn primary">Guardar Perfil</button>
            </form>
          </div>
  
          <!-- Seguridad -->
          <div v-if="vista === 'seguridad'">
            <form @submit.prevent="guardarSeguridad">
              <div class="form-group mb-6">
                <label for="password" class="block text-lg font-medium">Nueva Contraseña</label>
                <input v-model="password" type="password" id="password" class="input" placeholder="Introduce nueva contraseña"/>
              </div>
              <div class="form-group mb-6">
                <label for="confirm-password" class="block text-lg font-medium">Confirmar Contraseña</label>
                <input v-model="confirmPassword" type="password" id="confirm-password" class="input" placeholder="Confirma tu contraseña"/>
              </div>
              <button type="submit" class="btn primary">Guardar Seguridad</button>
            </form>
          </div>
  
          <!-- Notificaciones -->
          <div v-if="vista === 'notificaciones'">
            <div class="form-group flex justify-between mb-6">
              <label class="text-lg font-medium">Recibir Notificaciones</label>
              <input type="checkbox" v-model="notificaciones" class="checkbox"/>
            </div>
            <div class="form-group flex justify-between mb-6">
              <label class="text-lg font-medium">Notificaciones por Email</label>
              <input type="checkbox" v-model="emailNotificaciones" class="checkbox"/>
            </div>
            <button type="button" @click="guardarNotificaciones" class="btn primary">Guardar Notificaciones</button>
          </div>
  
          <!-- Idioma -->
          <div v-if="vista === 'idioma'">
            <form @submit.prevent="guardarIdioma">
              <div class="form-group mb-6">
                <label class="block text-lg font-medium">Idioma Preferido</label>
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
  @import '../../../scss/Ajustes/ajustes.scss';
  </style>