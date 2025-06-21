<template>
  <div class="container" ref="container">

    <div v-if="showLogoutMessage" class="logout-message">
      ✅ Sesión cerrada exitosamente
    </div>

    <div class="form-container sign-up-container">
      <form>
        <h1>Crear Cuenta</h1>
        <div class="social-container">
          <a href="https://www.instagram.com" target="_blank" class="social">
            <img src="imagenes/SocialMedia-Instagram.png" alt="Instagram" width="40">
          </a>
          <a href="https://www.facebook.com" target="_blank" class="social">
            <img src="imagenes/SocialMedia-Facebook.png" alt="Facebook" width="40">
          </a>
          <a href="https://x.com" target="_blank" class="social">
            <img src="imagenes/SocialMedia-X.png" alt="Twitter" width="40">
          </a>
        </div>
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

    <div class="form-container sign-in-container">
      <form @submit.prevent="submitLoginForm">
        <h1>Iniciar Sesión</h1>
        <div class="social-container">
          <a href="https://www.instagram.com" target="_blank" class="social">
            <img src="imagenes/SocialMedia-Instagram.png" alt="Instagram" width="40">
          </a>
          <a href="https://www.facebook.com" target="_blank" class="social">
            <img src="imagenes/SocialMedia-Facebook.png" alt="Facebook" width="40">
          </a>
          <a href="https://x.com" target="_blank" class="social">
            <img src="imagenes/SocialMedia-X.png" alt="Twitter" width="40">
          </a>
        </div>
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

    <div class="overlay-container">
      <div class="overlay">
        <div class="overlay-panel overlay-left">
          <router-link to="/" class="mb-6">
            <img src="/imagenes/Logo2.png" alt="SportFamilyRD Logo" class="logo-main" />
          </router-link>
          <h1>¡Bienvenido de nuevo!</h1>
          <p>Para mantenerte conectado con nosotros, inicia sesión con tu información personal</p>
          <button class="ghost" @click="toggleForm('signIn')">Iniciar Sesión</button>
        </div>
        <div class="overlay-panel overlay-right">
          <router-link to="/" class="mb-6">
            <img src="/imagenes/Logo2.png" alt="SportFamilyRD Logo" class="logo-main" />
          </router-link>
          <h1>¡Hola, Amigo!</h1>
          <p>Ingresa tus detalles y comienza tu viaje con nosotros</p>
          <button class="ghost" @click="toggleForm('signUp')">Registrarse</button>
        </div>
      </div>
    </div>
  </div>
</template>


<script>
import axios from 'axios';

export default {
  data() {
    return {
      showLogoutMessage: false,
      showRegisterPassword: false,
      showRegisterConfirm: false,
      showLoginPassword: false,
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
      } else {
        this.$refs.container.classList.add('right-panel-active');
      }
    },

    async submitForm() {
      try {
        console.log('Registering:', this.registerForm);

        axios.post('/user', this.registerForm)
          .then(response => {
            sessionStorage.setItem('user', JSON.stringify(response.data.user));
            this.$router.push('/');
          })
          .catch((error) => {
            console.log(error);
            alert('Algo salió mal, por favor intenta de nuevo');
          });
      } catch (error) {
        console.error(error);
      }
    },

    async submitLoginForm() {
      this.isSubmitting = true;
      try {
        await axios.get('/sanctum/csrf-cookie');

        const response = await axios.post('/login', {
          email: this.loginForm.email,
          password: this.loginForm.password
        });

        // Manejo de usuario
        const user = response.data.user;
        user.image = user.image
          ? `${axios.defaults.baseURL}/storage/users/${user.id}/${user.image}`
          : `${axios.defaults.baseURL}/storage/users/Perfil-Icon.png`;

        sessionStorage.setItem('user', JSON.stringify(user));
        this.$router.push('/');
      } catch (error) {
        console.error('Error en login:', error);

        // Manejo específico de error 419
        if (error.response?.status === 419) {
          try {
            // Reintento con nuevo token
            await axios.get('/sanctum/csrf-cookie');
            const retryResponse = await axios.post('/login', {
              email: this.loginForm.email,
              password: this.loginForm.password
            }, {
              headers: {
                'X-XSRF-TOKEN': this.getCsrfFromCookies(),
                'Accept': 'application/json'
              }
            });

            const user = retryResponse.data.user;
            user.image = user.image
              ? `${axios.defaults.baseURL}/storage/users/${user.id}/${user.image}`
              : `${axios.defaults.baseURL}/storage/users/Perfil-Icon.png`;

            sessionStorage.setItem('user', JSON.stringify(user));
            this.$router.push('/');
          } catch (retryError) {
            console.error('Error en reintento:', retryError);
            alert('Error persistente. Por favor recarga la página.');
          }
        } else {
          alert('Error de conexión: ' + error.message);
        }
      }
      this.isSubmitting = false;
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
    }
  },
  watch: {
    $route(to) {
      if (to.query.panel === 'signup') {
        this.$refs.container.classList.add('right-panel-active');
      } else {
        this.$refs.container.classList.remove('right-panel-active');
      }
    }
  }
};
</script>


<style scoped>
@import '../../../scss/Login/signup.scss';

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


.password-wrapper {
  position: relative;
  width: 100%;
}

.password-wrapper input {
  padding-right: 40px;
  /* Espacio para el ojo */
  width: 100%;
}

.toggle-password {
  position: absolute;
  right: 12px;
  top: 50%;
  transform: translateY(-50%);
  cursor: pointer;
  opacity: 0.6;
  transition: opacity 0.3s;
}

.toggle-password:hover {
  opacity: 1;
}

.toggle-password svg {
  width: 20px;
  height: 20px;
  display: block;
}

/* Estilos responsivos */
@media (max-width: 768px) {
  .password-wrapper {
    width: 100%;
  }
}
</style>
