<template>
  <div class="container" ref="container">
    <div class="form-container sign-up-container">
      <form >
        <!-- @submit.prevent="submitForm" -->
        <h1>Create Account</h1>
        <div class="social-container">
          <a href="#" class="social"><i class="fab fa-google-plus-g"></i></a>
          <a href="#" class="social"><i class="fab fa-google-plus-g"></i></a>
          <a href="#" class="social"><i class="fab fa-linkedin-in"></i></a>
        </div>
        <input type="text" v-model="registerForm.name" placeholder="Name" required />
        <input type="email" v-model="registerForm.email" placeholder="Email" required />
        <input type="password" v-model="registerForm.password" placeholder="Password" required />
        <input type="password" v-model="registerForm.password_confirmation" placeholder="Password" required />
        <button type="button" :disabled="isSubmitting" @click="submitForm()">Sign Up</button>
      </form>
    </div>

    <div class="form-container sign-in-container">
      <form @submit.prevent="submitLoginForm">
        <h1>Sign in</h1>
        <div class="social-container">
          <a href="#" class="social"><i class="fab fa-google-plus-g"></i></a>
          <a href="#" class="social"><i class="fab fa-google-plus-g"></i></a>
          <a href="#" class="social"><i class="fab fa-linkedin-in"></i></a>
        </div>
        <input type="email" v-model="loginForm.email" placeholder="Email" required />
        <input type="password" v-model="loginForm.password" placeholder="Password" required />
        <button type="submit" :disabled="isSubmitting">Sign In</button>
      </form>
    </div>
    <div class="overlay-container">
      <div class="overlay">
        <div class="overlay-panel overlay-left">
          <a href="/" class="mb-6">
             <img src="/imagenes/Logo.png" alt="SportFamilyRD Logo" class="logo-main" />
          </a>
          <h1>Welcome Back!</h1>
          <p>To keep connected with us, please login with your personal info</p>
          <button class="ghost" @click="toggleForm('signIn')">Sign In</button>
        </div>
        <div class="overlay-panel overlay-right">
          <a href="/" class="mb-6">
             <img src="/imagenes/Logo.png" alt="SportFamilyRD Logo" class="logo-main" />
          </a>
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
      // this.isSubmitting = true;
    
      try {
        console.log('Registering:', this.registerForm);
        
        axios.post('/user', this.registerForm)
        .then((response) => {
          console.log(response);
       
          alert('Registrado con éxito');
        })
        .catch((error) => {
          console.log(error);
         
          alert('Algo salió mal, por favor intenta de nuevo');
        });
      } catch (error) {
        console.error(error);
      }
      // this.isSubmitting = false;
    },

    async submitLoginForm() {
      this.isSubmitting = true;
      try {
        console.log('Logging in:', this.loginForm);
        
        axios.post('/login', {
          email: this.email,
          password: this.password,
        })
        .then((response) => {
          console.log(response);
          // Manejar inicio de sesión exitoso
          alert('Bienvenido de nuevo!');
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
  },
};
</script>


<style scoped >
* {
  box-sizing: border-box;
}

body {
  background: #f6f5f7;
  display: flex;
  justify-content: center;
  align-items: center;
  flex-direction: column;
  font-family: 'Montserrat', sans-serif;
  height: 100vh;
  margin: 0;

}
h1 {
  font-weight: bold;
  margin: 0;
}


h2 {
  text-align: center;
}

p {
  font-size: 14px;
  font-weight: 100;
  line-height: 20px;
  letter-spacing: 0.5px;
  margin: 20px 0 30px;
}

span {
  font-size: 12px;
}

a {
  color: #333;
  font-size: 14px;
  text-decoration: none;
  margin: 15px 0;
}

button {
  border-radius: 20px;
  border: 1px solid #000000;
  background-color: #000000;
  color: #ffffff;
  font-size: 12px;
  font-weight: bold;
  padding: 12px 45px;
  letter-spacing: 1px;
  text-transform: uppercase;
  transition: transform 80ms ease-in;
}

button:active {
  transform: scale(0.95);
}

button:focus {
  outline: none;
}

button.ghost {
  background-color: transparent;
  border-color: #ffffff;
}

form {
  background-color: #c3c2c2;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-direction: column;
  padding: 0 50px;
  height: 100%;
  text-align: center;
}

input {
  background-color: #eee;
  border: none;
  padding: 12px 15px;
  margin: 8px 0;
  width: 100%;
}

.logo-main {
  width: 250px; /* Ajusta el tamaño del logo */
  height: auto;
  transition: transform 0.3s ease-in-out;
  margin: 20px;
  position: relative; /* Agregado */
  z-index: 1000; /* Asegura que el logo esté siempre encima */
}

.container {
  background-color: #fff;
  border-radius: 10px;
  box-shadow: 0 14px 28px rgba(0, 0, 0, 0.25), 0 10px 10px rgba(0, 0, 0, 0.22);
  position: center;
  overflow: hidden;
  width: 100%;
  max-width: 768px;
  min-height: 480px;
}

.form-container {
  position: absolute;
  top: 0;
  height: 100%;
  transition: all 0.6s ease-in-out;
}

.sign-in-container {
  left: 0;
  width: 50%;
  z-index: 2;
}

.container.right-panel-active .sign-in-container {
  transform: translateX(100%);
}

.sign-up-container {
  left: 0;
  width: 50%;
  opacity: 0;
  z-index: 1;
}

.container.right-panel-active .sign-up-container {
  transform: translateX(100%);
  opacity: 1;
  z-index: 5;
  animation: show 0.6s;
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

.container.right-panel-active .overlay-container {
  transform: translateX(-100%);
}

.overlay {
  background: linear-gradient(to right, #000000, #000000);
  color: #ffffff;
  position: relative;
  left: -100%;
  height: 100%;
  width: 200%;
  transform: translateX(0);
  transition: transform 0.6s ease-in-out;
}

.container.right-panel-active .overlay {
  transform: translateX(50%);
}

.overlay-panel {
  position: absolute;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-direction: column;
  padding: 0 40px;
  text-align: center;
  top: 0;
  height: 100%;
  width: 50%;
  transform: translateX(0);
  transition: transform 0.6s ease-in-out;
}

.overlay-left {
  transform: translateX(-20%);
}

.container.right-panel-active .overlay-left {
  transform: translateX(0);
}

.overlay-right {
  right: 0;
  transform: translateX(0);
}

.container.right-panel-active .overlay-right {
  transform: translateX(20%);
}

.social-container {
  margin: 20px 0;
}

.social-container a {
  border: 1px solid #ffffff;
  border-radius: 50%;
  display: inline-flex;
  justify-content: center;
  align-items: center;
  margin: 0 5px;
  height: 40px;
  width: 40px;
}

@media (max-width: 768px) {
  .container {
    width: 100%;
    max-width: none;
  }

  .form-container {
    padding: 20px;
  }

  .overlay-panel {
    padding: 20px;
  }

  .overlay {
    width: 100%;
  }
}
</style>
