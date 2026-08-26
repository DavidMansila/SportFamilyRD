<template>

  <!-- auth-shell centra el bloque tabs+tarjeta verticalmente en cualquier
       resolucion (ver <style>). -->
  <div class="auth-shell">
    <!-- Tabs para móvil -->
    <div class="mobile-tabs">
      <button :class="['tab-button', { 'active': activeTab === 'signIn' }]" @click="showSignIn">Iniciar Sesión</button>
      <button :class="['tab-button', { 'active': activeTab === 'signUp' }]" @click="showSignUp">Registrarse</button>
    </div>

    <div class="container" ref="container">
    
    <!-- Mensaje de cierre de sesión -->
    <div v-if="showLogoutMessage" class="logout-message">
      ✅ Sesión cerrada exitosamente
    </div>

    <!-- Formulario de Registro -->
    <div class="form-container sign-up-container">
      <form>
        <h1>Registrarse</h1>

        <!-- <div class="social-container">
          <a href="https://www.instagram.com" target="_blank" class="social">
            <img src="imagenes/SocialMedia-Instagram.png" alt="Instagram" width="40">
          </a>
          <a href="https://www.facebook.com" target="_blank" class="social">
            <img src="imagenes/SocialMedia-Facebook.png" alt="Facebook" width="40">
          </a>
          <a href="https://x.com" target="_blank" class="social">
            <img src="imagenes/SocialMedia-X.png" alt="Twitter" width="40">
          </a>
        </div> -->
        <input type="text" v-model="registerForm.name" placeholder="Nombre" required />
        <input type="email" v-model="registerForm.email" placeholder="Correo" required />

        <!-- Campo de contraseña con ojo -->
        <div class="password-wrapper">
          <input :type="showRegisterPassword ? 'text' : 'password'" v-model="registerForm.password"
            placeholder="Contraseña" required />
          <span class="toggle-password" @click="showRegisterPassword = !showRegisterPassword">
            <svg v-if="showRegisterPassword" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
              viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
              stroke-linejoin="round">
              <path
                d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24" />
              <line x1="1" y1="1" x2="23" y2="23" />
            </svg>
            <svg v-else xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
              stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
              <circle cx="12" cy="12" r="3" />
            </svg>
          </span>
        </div>

        <!-- Campo de confirmación con ojo -->
        <div class="password-wrapper">
          <input :type="showRegisterConfirm ? 'text' : 'password'" v-model="registerForm.password_confirmation"
            placeholder="Confirmar contraseña" required />
          <span class="toggle-password" @click="showRegisterConfirm = !showRegisterConfirm">
            <svg v-if="showRegisterConfirm" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
              viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
              stroke-linejoin="round">
              <path
                d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24" />
              <line x1="1" y1="1" x2="23" y2="23" />
            </svg>
            <svg v-else xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
              stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
              <circle cx="12" cy="12" r="3" />
            </svg>
          </span>
        </div>

        <button type="button" :disabled="isSubmitting" @click="submitForm()">Registrarse</button>
      </form>
    </div>

    <!-- Formulario de Login -->
    <div class="form-container sign-in-container">
      <form @submit.prevent="submitLoginForm">
        <h1>Iniciar Sesión</h1>
        <!-- <div class="social-container">
          <a href="https://www.instagram.com" target="_blank" class="social">
            <img src="imagenes/SocialMedia-Instagram.png" alt="Instagram" width="40">
          </a>
          <a href="https://www.facebook.com" target="_blank" class="social">
            <img src="imagenes/SocialMedia-Facebook.png" alt="Facebook" width="40">
          </a>
          <a href="https://x.com" target="_blank" class="social">
            <img src="imagenes/SocialMedia-X.png" alt="Twitter" width="40">
          </a>
        </div> -->
        <input type="email" v-model="loginForm.email" placeholder="Correo" required />

        <!-- Campo de contraseña con ojo -->
        <div class="password-wrapper">
          <input :type="showLoginPassword ? 'text' : 'password'" v-model="loginForm.password" placeholder="Contraseña"
            required />
          <span class="toggle-password" @click="showLoginPassword = !showLoginPassword">
            <svg v-if="showLoginPassword" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
              fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path
                d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24" />
              <line x1="1" y1="1" x2="23" y2="23" />
            </svg>
            <svg v-else xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
              stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
              <circle cx="12" cy="12" r="3" />
            </svg>
          </span>
        </div>

        <button type="submit" :disabled="isSubmitting">Iniciar Sesión</button>
      </form>
    </div>

    <!-- Overlay con botones para cambiar entre formularios -->
    <div class="overlay-container">
      <div class="overlay">
        <div class="overlay-panel overlay-left">
          <h1>¡Bienvenido de nuevo!</h1>
          <p>Para mantenerte conectado con nosotros, inicia sesión con tu información personal</p>
          <router-link to="/" class="margin-pc">
            <img src="/imagenes/Logo2.png" alt="SportFamilyRD Logo" class="logo-main" />
          </router-link>
          <button class="ghost" @click="toggleForm('signIn')">Iniciar Sesión</button>
        </div>
        <div class="overlay-panel overlay-right">
          <h1>¡Hola, Amigo!</h1>
          <p>Ingresa tus detalles y comienza tu viaje con nosotros</p>
          <router-link to="/" class="margin-pc">
            <img src="/imagenes/Logo2.png" alt="SportFamilyRD Logo" class="logo-main" />
          </router-link>
          <button class="ghost" @click="toggleForm('signUp')">Registrarse</button>
        </div>
      </div>
    </div>
  </div>
  </div>

  <Alert
    v-if="openModal" 
    :key="alertKey"
    :type="alertType" 
    :message="alertMessage" 
    @close="openModal = false" 
  />  
</template>

<script>
import axios from 'axios';
import Alert from '../Alert.vue';

export default {
  components: {
    Alert
  },

  data() {
    return {
      alertType: 'success', // 'success', 'error', 'alert'
      alertMessage: '',
      openModal: false,
      alertKey: 0, // Para forzar la re-renderización del componente Alert
      showLogoutMessage: false,
      showRegisterPassword: false,
      showRegisterConfirm: false,
      showLoginPassword: false,
      activeTab: 'signIn',
      registerForm: {
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
      },
      loginForm: {
        email: '',
        password: '',
      },
      isSubmitting: false,
    };
  },
  methods: {
    toggleForm(form) {
      this.$router.replace({ query: null });

      if (form === 'signIn') {
        this.$refs.container.classList.remove('right-panel-active');
        this.activeTab = 'signIn';
      } else {
        this.$refs.container.classList.add('right-panel-active');
        this.activeTab = 'signUp';
      }
    },

    showSignIn() {
      this.toggleForm('signIn');
    },

    showSignUp() {
      this.toggleForm('signUp');
    },

    async submitForm() {
      try {
        this.isSubmitting = true;
        const response = await axios.post('/user', this.registerForm);
        sessionStorage.setItem('token', response.data.token);
        sessionStorage.setItem('user', JSON.stringify(response.data.user));
        this.$router.push('/');
      } catch (error) {
        console.error(error);
        
        this.alertType = 'alert';
        this.alertMessage = 'Contraseña debe tener al menos 4 caracteres y ser igual a la confirmación.';
        this.alertKey++;
        this.openModal = true;

      } finally {
        this.isSubmitting = false;
      }
    },

    async submitLoginForm() {
      try {
        this.isSubmitting = true;
        const response = await axios.post('/login', {
          email: this.loginForm.email,
          password: this.loginForm.password
        });
        sessionStorage.setItem('user', JSON.stringify(response.data.user));
        sessionStorage.setItem('token', response.data.token);
        this.$router.push('/');
      } catch (error) {
        console.error('Login error:', error);

        this.alertType = 'error';
        if (!error.response) {
          // La petición no llegó al servidor (red caída, servidor no responde, etc.)
          this.alertMessage = 'No se pudo conectar con el servidor. Verifica tu conexión e inténtalo de nuevo.';
        } else if (error.response.status === 429) {
          this.alertMessage = 'Demasiados intentos. Espera un minuto e inténtalo de nuevo.';
        } else if (error.response.status === 422) {
          this.alertMessage = error.response.data?.errors?.email?.[0]
            || error.response.data?.message
            || 'Credenciales inválidas. Por favor, inténtalo de nuevo.';
        } else {
          this.alertMessage = error.response.data?.message || 'Ocurrió un error al iniciar sesión. Inténtalo de nuevo.';
        }
        this.alertKey++;
        this.openModal = true;

      } finally {
        this.isSubmitting = false;
      }
    },

    checkLogoutMessage() {
      if (this.$route.query.logoutSuccess || sessionStorage.getItem('logoutMessage')) {
        this.showLogoutMessage = true;
        sessionStorage.removeItem('logoutMessage');
        setTimeout(() => {
          this.showLogoutMessage = false;
        }, 5000);
      }
    }
  },
  mounted() {
    this.checkLogoutMessage();
    if (this.$route.query.panel === 'signup') {
      this.$refs.container.classList.add('right-panel-active');
      this.activeTab = 'signUp';
    } else {
      this.activeTab = 'signIn';
    }
  },
  watch: {
    $route(to) {
      if (to.query.panel === 'signup') {
        this.$refs.container.classList.add('right-panel-active');
        this.activeTab = 'signUp';
      } else {
        this.$refs.container.classList.remove('right-panel-active');
        this.activeTab = 'signIn';
      }
    }
  }
};
</script>

<style scoped lang="scss">
@import '../../../scss/Login/signup.scss';

// Variables
$primary-color: #000000;
$secondary-color: #ff3149;
$light-color: #ffffff;
$dark-color: #333333;
$gray-light: #f5f5f5;
$gray-medium: #e0e0e0;
$gray-dark: #c3c2c2;
$font-main: "Montserrat", sans-serif;
$transition-speed: 0.3s;
$breakpoint-tablet: 768px;
$breakpoint-mobile: 480px;

// Mixins
@mixin flex-center {
  display: flex;
  justify-content: center;
  align-items: center;
}

@mixin box-shadow {
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
}

@mixin gradient-bg {
  background: linear-gradient(135deg, $primary-color, $secondary-color);
}

// Estilos base
body {
  @include gradient-bg;
  font-family: $font-main;
  min-height: 100vh;
  @include flex-center;
  padding: 1rem;
}

.container {
  background-color: $light-color;
  border-radius: 1rem;
  @include box-shadow;
  position: relative;
  overflow: hidden;
  width: 100%;
  max-width: 60rem;
  min-height: 35rem;
  display: flex;
  margin: auto;
}

// Centra el bloque tabs+tarjeta verticalmente en toda resolucion (antes solo
// pasaba en movil). El "body { ... flex-center ... }" de arriba nunca
// funciono: con <style scoped>, Vue le agrega un atributo data-v-xxx a ese
// selector que el <body> real jamas tiene, asi que esa regla no aplicaba a
// nada. .auth-shell es un div que si renderiza el propio componente, asi que
// aqui el centrado si funciona.
.auth-shell {
  min-height: 100vh;
  min-height: 100dvh;
  display: flex;
  flex-direction: column;
  justify-content: center;
}

// Mensaje de logout
.logout-message {
  position: fixed;
  top: 20px;
  left: 50%;
  transform: translateX(-50%);
  background: #4CAF50;
  color: white;
  padding: 15px 25px;
  border-radius: 8px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  z-index: 1000;
  animation: slideIn 0.5s ease-out;
}

@keyframes slideIn {
  from {
    top: -50px;
  }

  to {
    top: 20px;
  }
}

// Tabs para móvil
.mobile-tabs {
  display: none;
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  background: rgba(0, 0, 0, 0.8);
  z-index: 200;
  padding: 10px;
  justify-content: space-around;

  .tab-button {
    flex: 1;
    background: rgba(142, 40, 40, 0.8);
    border: none;
    color: rgba(46, 4, 4, 0.8);
    padding: 10px;
    margin: 10px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
    border-bottom: 3px solid $secondary-color;

    &.active {
      border-bottom: 3px solid $secondary-color;
      color: white;
      background-color:rgba(94, 29, 29, 0.1);
    }
  }
}

// Formularios
.form-container {
  position: absolute;
  top: 0;
  height: 100%;
  transition: all 0.6s ease-in-out;
  width: 50%;
  padding: 2rem;
  @include flex-center;
}

.sign-in-container {
  left: 0;
  z-index: 2;
}

.sign-up-container {
  left: 0;
  opacity: 0;
  z-index: 1;
}

form {
  background-color: $light-color;
  @include flex-center;
  flex-direction: column;
  padding: 1.5rem;
  height: 100%;
  text-align: center;
  border-radius: 1rem;
  @include box-shadow;
  width: 100%;
}

h1 {
  font-weight: 700;
  margin: 0 0 1.5rem;
  font-size: 1.75rem;
}

input {
  background-color: $gray-light;
  border: none;
  margin: 0.5rem 0;
  padding: 0.875rem 1.25rem;
  width: 100%;
  border-radius: 0.5rem;
  font-size: 0.875rem;
  transition: all $transition-speed;
  border: 2px solid transparent;

  &:focus {
    outline: none;
    border-color: $primary-color;
    background-color: $light-color;
    @include box-shadow;
  }
}

button {
  border-radius: 2rem;
  border: 2px solid $primary-color;
  background-color: $primary-color;
  color: $light-color;
  font-size: 0.75rem;
  font-weight: 600;
  padding: 0.75rem 2rem;
  letter-spacing: 1px;
  text-transform: uppercase;
  transition: all $transition-speed;
  cursor: pointer;
  margin: 0.5rem 0;
  min-width: 120px;

  &:hover {
    background-color: $secondary-color;
    border-color: $secondary-color;
    transform: translateY(-2px);
  }

  &:disabled {
    opacity: 0.7;
    cursor: not-allowed;
  }

  &.ghost {
    background-color: transparent;
    border-color: $light-color;
    color: $light-color;

    &:hover {
      background-color: rgba($light-color, 0.1);
    }
  }
}

// Social buttons
.social-container {
  margin: 1.5rem 0;
  display: flex;
  gap: 0.75rem;
  justify-content: center;

  a {
    border: 1px solid $light-color;
    border-radius: 50%;
    @include flex-center;
    height: 2.5rem;
    width: 2.5rem;
    color: $light-color;
    transition: all $transition-speed;

    &:hover {
      background-color: rgba($light-color, 0.2);
      transform: translateY(-2px);
    }

    img {
      width: 24px;
      height: 24px;
    }
  }
}

// Overlay
.overlay-container {
  position: absolute;
  top: 0;
  left: 50%;
  width: 50%;
  height: 100%;
  overflow: hidden;
  transition: transform 0.6s ease-in-out;
  z-index: 100;
}

.overlay {
  @include gradient-bg;
  color: $light-color;
  position: relative;
  left: -100%;
  height: 100%;
  width: 200%;
  transform: translateX(0);
  transition: transform 0.6s ease-in-out;
}

.overlay-panel {
  position: absolute;
  @include flex-center;
  flex-direction: column;
  padding: 2rem;
  text-align: center;
  top: 0;
  height: 100%;
  width: 50%;
  transform: translateX(0);
  transition: transform 0.6s ease-in-out;

  h1 {
    color: $light-color;
    margin-bottom: 1rem;
  }

  p {
    color: rgba($light-color, 0.8);
    max-width: 80%;
    font-size: 0.875rem;
    line-height: 1.6;
  }
}

.overlay-left {
  transform: translateX(-20%);
}

.overlay-right {
  right: 0;
  transform: translateX(0);
}

.logo-main {
  margin: 18px;
  padding: 8px;
  background-color: #070707;
  border-radius: 40px;
  width: 12rem;
  height: auto;
  transition: transform $transition-speed;
  filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.2));

  &:hover {
    transform: scale(1.05);
  }
}

// Password toggle
.password-wrapper {
  position: relative;
  width: 100%;

  input {
    padding-right: 40px;
  }

  .toggle-password {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    cursor: pointer;
    opacity: 0.6;
    transition: opacity 0.3s;

    &:hover {
      opacity: 1;
    }

    svg {
      width: 20px;
      height: 20px;
      display: block;
    }
  }
}

// Animaciones
.container.right-panel-active {
  .sign-in-container {
    transform: translateX(100%);
  }

  .sign-up-container {
    transform: translateX(100%);
    opacity: 1;
    z-index: 5;
    animation: show 0.6s;
  }

  .overlay-container {
    transform: translateX(-100%);
  }

  .overlay {
    transform: translateX(50%);
  }

  .overlay-left {
    transform: translateX(0);
  }

  .overlay-right {
    transform: translateX(20%);
  }
}

@keyframes show {

  0%,
  49.99% {
    opacity: 0;
    z-index: 1;
  }

  50%,
  100% {
    opacity: 1;
    z-index: 5;
  }
}

// Estilos responsivos
@media (max-width: $breakpoint-tablet) {
  .container {
    flex-direction: column;
    min-height: auto;
    max-height: unset;
    margin-top: 0;
    margin-bottom: 0;
    border-radius: 0;
    box-shadow: none;
  }

  // El mismo degradado que .overlay (ver @include gradient-bg mas abajo),
  // en vez de negro solido: antes la barra de tabs y el hero de abajo se
  // veian como dos piezas de diseño distintas pegadas con una costura dura.
  .mobile-tabs {
    position: relative;
    background: linear-gradient(135deg, $primary-color, $secondary-color);
    padding: 15px 10px;
    margin-top: 0;
    z-index: 10;
    display: flex;
  }

  // min-height:50vh forzaba el formulario a ocupar la mitad de la pantalla
  // sin importar cuantos campos tuviera, dejando un hueco en blanco debajo
  // de la tarjeta (el body no tiene mas contenido que lo llene). Ahora se
  // ajusta al contenido real.
  // min-height fijo (calculado para el formulario mas alto, el de
  // Registrarse con 4 campos): sin esto, al cambiar de pestaña el
  // formulario de Iniciar Sesion (2 campos) era mas bajo y todo el bloque
  // -incluido el panel rojo de arriba- cambiaba de tamaño/posicion de
  // golpe al alternar entre pestañas.
  .form-container {
    position: relative;
    width: 100%;
    height: auto;
    min-height: 29rem;
    padding: 1.5rem;
    display: none;

    &.sign-in-container {
      display: flex;
    }
  }

  // !important: signup.scss (importado arriba) tiene un bloque @media
  // (max-width: 480px) que fija height:180px !important en esta misma
  // clase. Sin igualar la importancia, ese valor gana siempre por debajo
  // de 480px sin importar el orden, y el texto de abajo del logo terminaba
  // desbordando el recuadro (invisible: texto blanco sobre fondo blanco).
  .overlay-container {
    position: relative;
    left: 0;
    width: 100%;
    height: 215px !important;
    order: -1;
  }

  .overlay {
    left: 0;
    width: 100%;
    height: 200%;
  }

  // flex-start en vez de center: centrado dejaba el titulo pegado arriba y
  // un hueco raro entre el logo y el texto de abajo (la porcion sobrante de
  // alto se reparte arriba/abajo del bloque entero, no entre cada elemento).
  // Alineando arriba, el espaciado real entre titulo/logo/texto lo deciden
  // los margenes de cada uno, que es lo que se ajusta abajo.
  .overlay-panel {
    width: 100%;
    padding: 2rem 1.5rem 1.5rem;
    height: 50%;
    justify-content: flex-start;

    h1 {
      margin-top: 0 !important;
      margin-bottom: 0.75rem !important;
    }

    p {
      max-width: 100%;
      margin-top: 0 !important;
      margin-bottom: 0 !important;
    }

    .ghost {
      display: none;
    }
  }

  .overlay-left {
    top: 0;
    transform: translateY(0) !important;
  }

  .overlay-right {
    top: 50%;
    transform: translateY(0) !important;
  }

  .container.right-panel-active {
    .sign-in-container {
      display: none;
    }

    .sign-up-container {
      display: flex;
    }

    .overlay {
      transform: translateY(-50%) !important;
    }
  }

  .logo-main {
    width: 150px !important;
    height: auto;
    margin-bottom: 0.6rem !important;
    display: block;
  }

  .social-container a {
    width: 35px;
    height: 35px;
  }

  form {
    padding: 1.2rem;
  }

  .password-wrapper .toggle-password {
    right: 10px;

    svg {
      width: 18px;
      height: 18px;
    }
  }
}

@media (max-width: $breakpoint-mobile) {
  .overlay-panel {
    h1 {
      margin-top: 10px;
      font-size: 1.5rem;
      margin-bottom: 0.5rem;
    }

    p {
      font-size: 0.9rem;
      margin-bottom: 1.5rem;
    }
  }

  form {
    h1 {
      font-size: 1.6rem;
    }

    input {
      padding: 12px 15px;
      font-size: 0.9rem;
    }

    button {
      padding: 10px 20px;
      font-size: 0.85rem;
    }
  }
}
</style>
