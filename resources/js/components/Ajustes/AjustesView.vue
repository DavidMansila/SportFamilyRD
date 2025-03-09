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
  /* Sidebar Styles */
  .sidebar {
    background: linear-gradient(to right, #979797, #bcbcbc);
    color: white;
    width: 250px;
    padding: 2rem;
    height: 100vh;
    position: fixed;
    left: 0;
    top: 0;
    bottom: 0;
    border-radius: 0 10px 10px 0;
  }
  
  .logo-text {
    font-size: 2rem;
    font-weight: 700;
    color: #fff;
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
  }
  
  .menu {
    margin-top: 2rem;
  }
  
  .menu-item {
    margin-bottom: 1.5rem;
  }
  
  .menu-link, .menu-link1 {
    color: white;
    font-size: 1.2rem;
    font-weight: 500;
    text-decoration: none;
    transition: 0.3s ease;
  }
  
  .menu-link1 {
    color: #000000;
    font-size: 1.1rem;
    text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.1);
  }
  
  .menu-link1:hover {
    color: #FBBF24;
  }
  
  .menu-link:hover {
    color: #FBBF24;
    transform: scale(1.05);
  }
  
  /* Content Styles */
  .content {
    display: flex;
    justify-content: start;
    align-items: center;
    flex-grow: 1;
    padding-left: 280px;
    width: 100%;
    height: 100vh;
  }
  
  .page-title {
    color: #333;
    font-size: 2.5rem;
    font-weight: 600;
    margin-bottom: 2rem;
  }
  
  
  /* Configuración - Contenido Mejorado */
  .settings-card {
    background-color: #ffffff;
    padding: 2rem;
    border-radius: 1.2rem;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    width: 100%;
    max-width: 800px;
    margin-left: auto;
    margin-right: auto;
    transition: 0.3s ease;
  }
  
  .settings-card:hover {
    box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
  }
  
  .form-group {
    margin-bottom: 2rem;
  }
  
  .form-group label {
    color: #333;
    font-weight: 600;
    font-size: 1.1rem;
    margin-bottom: 0.8rem;
    display: block;
  }
  
  .input {
    width: 100%;
    padding: 1rem;
    border: 1px solid #ddd;
    border-radius: 1rem;
    font-size: 1rem;
    transition: 0.3s ease;
    background-color: #f9f9f9;
  }
  
  .input:focus {
    background: linear-gradient(to right, #979797, #bcbcbc);
    outline: none;
    box-shadow: 0 0 10px rgba(74, 144, 226, 0.4);
    background-color: #fff;
  }
  
  .input:disabled {
    background-color: #f1f1f1;
    border-color: #ccc;
    cursor: not-allowed;
  }
  
  .checkbox {
    width: auto;
    margin-right: 0.8rem;
    cursor: pointer;
  }
  
  .checkbox-label {
    font-size: 1rem;
    color: #666;
    margin-top: 0.4rem;
  }
  
  .checkbox:hover {
    transform: scale(1.05);
    transition: 0.3s ease;
  }
  
  .checkbox:checked {
    background-color: #4a90e2;
    border-color: #4a90e2;
  }
  
  /* Botón Mejorado */
  .btn {
    padding: 0.9rem 1.8rem;
    font-weight: bold;
    border-radius: 1.2rem;
    cursor: pointer;
    transition: 0.3s ease;
    display: inline-block;
    font-size: 1.1rem;
    border: none;
    margin-top: 1rem;
  }
  
  .primary {
    background: linear-gradient(to right, #979797, #bcbcbc);
    color: white;
    box-shadow: 0 4px 15px rgba(74, 144, 226, 0.3);
    width: 100%;
  }
  
  .primary:hover {
    background: linear-gradient(to right, #979797, #bcbcbc);
    transform: translateY(-3px);
  }
  
  .primary:active {
    background: linear-gradient(to right, #979797, #bcbcbc);
    box-shadow: 0 3px 8px rgba(74, 144, 226, 0.4);
    transform: translateY(2px);
  }
  
  /* Transiciones y efectos para el formulario */
  .input, .checkbox, .btn {
    transition: all 0.3s ease;
  }
  
  .input:focus, .checkbox:hover, .btn:hover {
    transform: scale(1.05);
  }
  
  .input:focus {
    background: linear-gradient(to right, #979797, #bcbcbc);
    background-color: #fff;
    box-shadow: 0 0 8px rgba(74, 144, 226, 0.4);
  }
  
  .form-group .input::placeholder {
    color: #aaa;
    font-style: italic;
  }
  
  /* Fondo del formulario */
  .settings-card {
    background-color: #f9f9f9;
    border-radius: 1.5rem;
  }
  
  /* Contenido y título */
  .page-title {
    font-size: 2.5rem;
    font-weight: 700;
    color: #333;
    margin-bottom: 2rem;
    text-align: center;
    text-transform: uppercase;
    letter-spacing: 1px;
  }
  
  .footer {
    text-align: center;
    padding: 1rem;
    font-size: 0.9rem;
    color: #000000;
    border-top: 1px solid #ddd;
  }
  
  /* Mejoras generales */
  * {
    box-sizing: border-box;
  }
  
  body {
    font-family: 'Arial', sans-serif;
    background-color: #f3f4f8;
    margin: 0;
    padding: 0;
  }
  
  .settings-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
  }
  
  .settings-card:hover {
    box-shadow: 0 12px 40px rgba(0, 0, 0, 0.2);
  }
  
  /* Animaciones suaves */
  @keyframes fadeIn {
    0% {
      opacity: 0;
    }
    100% {
      opacity: 1;
    }
  }
  
  .fade-in {
    animation: fadeIn 0.5s ease-out;
  }
  
  /* Estilo para el encabezado del formulario */
  .page-title {
    font-size: 2.5rem;
    font-weight: bold;
    color: #333;
    text-align: center;
    margin-bottom: 3rem;
    text-transform: uppercase;
    letter-spacing: 2px;
  }
  
  .settings-card {
    background-color: #fff;
    padding: 2rem;
    border-radius: 1.5rem;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    margin: 0 auto;
    max-width: 800px;
    width: 100%;
  }
  
  .settings-card:hover {
    box-shadow: 0 12px 40px rgba(0, 0, 0, 0.2);
  }
  
  /* Media Queries for Responsiveness */
  @media (max-width: 1024px) {
    .sidebar {
      width: 200px;
      padding: 1.5rem;
    }
  
    .content {
      padding-left: 220px;
    }
  
    .page-title {
      font-size: 2rem;
    }
  
    .settings-card {
      padding: 1.5rem;
    }
  
    .input, .btn {
      font-size: 1rem;
    }
  
    .menu-link, .menu-link1 {
      font-size: 1.1rem;
    }
  }
  
  @media (max-width: 768px) {
    .sidebar {
      width: 180px;
      padding: 1rem;
    }
  
    .content {
      padding-left: 200px;
    }
  
    .page-title {
      font-size: 1.8rem;
    }
  
    .settings-card {
      padding: 1rem;
      margin: 1rem;
    }
  
    .menu {
      display: none;
    }
  
    .sidebar {
      position: fixed;
      width: 100%;
      height: auto;
      top: 0;
      left: 0;
      border-radius: 0;
    }
  
    .content {
      padding-left: 0;
      padding-top: 20px;
    }
  
    .logo-text {
      font-size: 1.8rem;
    }
  
    .menu-link, .menu-link1 {
      font-size: 1rem;
    }
  
    .page-title {
      font-size: 1.6rem;
    }
  }
  
  @media (max-width: 480px) {
    .sidebar {
      width: 100%;
      height: auto;
    }
  
    .content {
      padding-left: 0;
    }
  
    .page-title {
      font-size: 1.5rem;
    }
  
    .settings-card {
      padding: 1rem;
      margin: 1rem;
    }
  
    .menu-link, .menu-link1 {
      font-size: 0.9rem;
    }
  
    .logo-text {
      font-size: 1.6rem;
    }
  
    .menu {
      display: block;
    }
  }
  </style>
  