<template>

  <div class="container" ref="container">

    <div v-if="showLogoutMessage" class="logout-message">
      ✅ Sesión cerrada exitosamente
    </div>

    <div class="form-container sign-up-container">
      <form>
        <!-- @submit.prevent="submitForm" -->
        <h1>Create Account</h1>
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
        <input type="password" v-model="registerForm.password" placeholder="Contraseña" required />
        <input type="password" v-model="registerForm.password_confirmation" placeholder="Confirmar contraseña"
          required />
        <button type="button" :disabled="isSubmitting" @click="submitForm()">Sign Up</button>
      </form>
    </div>

    <div class="form-container sign-in-container">
      <form @submit.prevent="submitLoginForm">
        <h1>Sign in</h1>
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
        <input type="password" v-model="loginForm.password" placeholder="Contraseña" required />
        <button type="submit" :disabled="isSubmitting">Sign In</button>
      </form>
    </div>


    <div class="overlay-container">
      <div class="overlay">
        <div class="overlay-panel overlay-left">
          <router-link to="/" class="mb-6">
            <img src="/imagenes/Logo2.png" alt="SportFamilyRD Logo" class="logo-main" />
          </router-link>
          <h1>Welcome Back!</h1>
          <p>To keep connected with us, please login with your personal info</p>
          <button class="ghost" @click="toggleForm('signIn')">Sign In</button>
        </div>
        <div class="overlay-panel overlay-right">
          <router-link to="/" class="mb-6">
            <img src="/imagenes/Logo2.png" alt="SportFamilyRD Logo" class="logo-main" />
          </router-link>
          <h1>Hello, Friend!</h1>
          <p>Enter your details and start your journey with us</p>
          <button class="ghost" @click="toggleForm('signUp')">Sign Up</button>
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
        console.log('Logging in:', this.loginForm);

        axios.post('/login', {
          email: this.loginForm.email,
          password: this.loginForm.password,
        })
          .then((response) => {
            console.log(response);
            // Manejar inicio de sesión exitoso
            alert('Bienvenido de nuevo!');
            sessionStorage.setItem('user', JSON.stringify(response.data.user));
            this.$router.push('/');
          })
          .catch((error) => {
            console.log(error);
            // Mostrar mensaje de error
            alert('Credenciales incorrectas');
          });

      } catch (error) {
        console.error(error);
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
  },
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
  from { top: -50px; }
  to { top: 20px; }
}
</style>
